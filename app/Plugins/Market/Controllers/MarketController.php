<?php

namespace App\Plugins\Market\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Market\Models\MarketListing;
use App\Plugins\Market\Models\MarketBid;
use App\Plugins\Market\Models\TradeOffer;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    /**
     * List market listings
     */
    public function index(Request $request)
    {
        $query = MarketListing::active()
            ->with(['seller:id,username,level', 'item']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $listings = $query->paginate(20);

        return response()->json([
            'success' => true,
            'listings' => $listings,
        ]);
    }

    /**
     * Get single listing
     */
    public function show(int $id)
    {
        $listing = MarketListing::with(['seller:id,username,level,avatar', 'item', 'bids.bidder:id,username'])
            ->find($id);

        if (!$listing) {
            return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
        }

        return response()->json([
            'success' => true,
            'listing' => $listing,
        ]);
    }

    /**
     * Get my listings
     */
    public function myListings()
    {
        $listings = MarketListing::where('seller_id', auth()->id())
            ->with('item')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'listings' => $listings,
        ]);
    }

    /**
     * Create a listing
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|integer|min:1',
            'type' => 'required|in:fixed,auction',
            'duration' => 'nullable|integer|min:3600|max:604800',
        ]);

        $user = auth()->user();

        // Check listing limit
        $currentListings = MarketListing::where('seller_id', $user->id)
            ->where('status', 'active')
            ->count();

        if ($currentListings >= 20) {
            return response()->json(['success' => false, 'message' => 'Maximum listings reached'], 400);
        }

        // TODO: Verify user owns the item and deduct from inventory

        $listing = MarketListing::create([
            'seller_id' => $user->id,
            'item_id' => $validated['item_id'],
            'quantity' => $validated['quantity'],
            'price' => $validated['price'],
            'type' => $validated['type'],
            'status' => 'active',
            'expires_at' => isset($validated['duration']) ? now()->addSeconds($validated['duration']) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Listing created',
            'listing' => $listing,
        ]);
    }

    /**
     * Buy a listing (fixed price)
     */
    public function buy(int $id)
    {
        $listing = MarketListing::active()->find($id);

        if (!$listing) {
            return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
        }

        if ($listing->type !== 'fixed') {
            return response()->json(['success' => false, 'message' => 'This is an auction, place a bid instead'], 400);
        }

        $buyer = auth()->user();

        if ($buyer->id === $listing->seller_id) {
            return response()->json(['success' => false, 'message' => 'Cannot buy your own listing'], 400);
        }

        if ($buyer->money < $listing->price) {
            return response()->json(['success' => false, 'message' => 'Not enough money'], 400);
        }

        // Process transaction
        $buyer->decrement('money', $listing->price);

        $seller = $listing->seller;
        $fee = intval($listing->price * 0.10); // 10% fee
        $seller->increment('money', $listing->price - $fee);

        // TODO: Transfer item to buyer's inventory

        $listing->update([
            'buyer_id' => $buyer->id,
            'status' => 'sold',
            'sold_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase successful',
        ]);
    }

    /**
     * Place a bid on auction
     */
    public function bid(Request $request, int $id)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $listing = MarketListing::active()->auctions()->find($id);

        if (!$listing) {
            return response()->json(['success' => false, 'message' => 'Auction not found'], 404);
        }

        $user = auth()->user();

        if ($user->id === $listing->seller_id) {
            return response()->json(['success' => false, 'message' => 'Cannot bid on your own auction'], 400);
        }

        $minBid = $listing->current_bid ? $listing->current_bid + 1 : $listing->price;

        if ($validated['amount'] < $minBid) {
            return response()->json(['success' => false, 'message' => "Minimum bid is {$minBid}"], 400);
        }

        if ($user->money < $validated['amount']) {
            return response()->json(['success' => false, 'message' => 'Not enough money'], 400);
        }

        // Refund previous highest bidder
        $previousBid = $listing->bids()->where('status', 'active')->orderBy('amount', 'desc')->first();
        if ($previousBid) {
            $previousBid->bidder->increment('money', $previousBid->amount);
            $previousBid->update(['status' => 'outbid']);
        }

        // Hold bid amount
        $user->decrement('money', $validated['amount']);

        MarketBid::create([
            'listing_id' => $id,
            'bidder_id' => $user->id,
            'amount' => $validated['amount'],
            'status' => 'active',
        ]);

        $listing->update(['current_bid' => $validated['amount']]);

        return response()->json([
            'success' => true,
            'message' => 'Bid placed',
        ]);
    }

    /**
     * Cancel a listing
     */
    public function cancel(int $id)
    {
        $listing = MarketListing::where('seller_id', auth()->id())
            ->where('id', $id)
            ->where('status', 'active')
            ->first();

        if (!$listing) {
            return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
        }

        // Refund any active bids
        foreach ($listing->bids()->where('status', 'active')->get() as $bid) {
            $bid->bidder->increment('money', $bid->amount);
            $bid->update(['status' => 'refunded']);
        }

        // TODO: Return item to seller's inventory

        $listing->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Listing cancelled',
        ]);
    }

    /**
     * Get trade offers
     */
    public function trades()
    {
        $userId = auth()->id();

        $incoming = TradeOffer::where('recipient_id', $userId)
            ->pending()
            ->with('sender:id,username,level')
            ->get();

        $outgoing = TradeOffer::where('sender_id', $userId)
            ->pending()
            ->with('recipient:id,username,level')
            ->get();

        return response()->json([
            'success' => true,
            'incoming' => $incoming,
            'outgoing' => $outgoing,
        ]);
    }

    /**
     * Create trade offer
     */
    public function createTrade(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'sender_items' => 'nullable|array',
            'sender_money' => 'nullable|integer|min:0',
            'recipient_items' => 'nullable|array',
            'recipient_money' => 'nullable|integer|min:0',
            'message' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        if ($validated['recipient_id'] == $user->id) {
            return response()->json(['success' => false, 'message' => 'Cannot trade with yourself'], 400);
        }

        $senderMoney = $validated['sender_money'] ?? 0;
        if ($senderMoney > $user->money) {
            return response()->json(['success' => false, 'message' => 'Not enough money'], 400);
        }

        $trade = TradeOffer::create([
            'sender_id' => $user->id,
            'recipient_id' => $validated['recipient_id'],
            'sender_items' => $validated['sender_items'] ?? [],
            'sender_money' => $senderMoney,
            'recipient_items' => $validated['recipient_items'] ?? [],
            'recipient_money' => $validated['recipient_money'] ?? 0,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
            'expires_at' => now()->addDays(3),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trade offer sent',
            'trade' => $trade,
        ]);
    }

    /**
     * Accept trade offer
     */
    public function acceptTrade(int $id)
    {
        $trade = TradeOffer::where('recipient_id', auth()->id())
            ->where('id', $id)
            ->pending()
            ->first();

        if (!$trade) {
            return response()->json(['success' => false, 'message' => 'Trade not found'], 404);
        }

        $sender = $trade->sender;
        $recipient = auth()->user();

        // Verify both parties have required items/money
        if ($trade->sender_money > $sender->money) {
            $trade->update(['status' => 'failed']);
            return response()->json(['success' => false, 'message' => 'Sender no longer has enough money'], 400);
        }

        if ($trade->recipient_money > $recipient->money) {
            return response()->json(['success' => false, 'message' => 'You do not have enough money'], 400);
        }

        // Process money exchange
        if ($trade->sender_money > 0) {
            $sender->decrement('money', $trade->sender_money);
            $recipient->increment('money', $trade->sender_money);
        }

        if ($trade->recipient_money > 0) {
            $recipient->decrement('money', $trade->recipient_money);
            $sender->increment('money', $trade->recipient_money);
        }

        // TODO: Process item exchange

        $trade->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => 'Trade completed',
        ]);
    }

    /**
     * Decline trade offer
     */
    public function declineTrade(int $id)
    {
        $trade = TradeOffer::where('recipient_id', auth()->id())
            ->where('id', $id)
            ->pending()
            ->first();

        if (!$trade) {
            return response()->json(['success' => false, 'message' => 'Trade not found'], 404);
        }

        $trade->update(['status' => 'declined']);

        return response()->json([
            'success' => true,
            'message' => 'Trade declined',
        ]);
    }

    /**
     * Cancel trade offer
     */
    public function cancelTrade(int $id)
    {
        $trade = TradeOffer::where('sender_id', auth()->id())
            ->where('id', $id)
            ->pending()
            ->first();

        if (!$trade) {
            return response()->json(['success' => false, 'message' => 'Trade not found'], 404);
        }

        $trade->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Trade cancelled',
        ]);
    }
}

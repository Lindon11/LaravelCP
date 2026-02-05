<?php

namespace App\Plugins\Market\Services;

use App\Plugins\Market\Models\MarketListing;
use App\Plugins\Market\Models\MarketBid;
use App\Plugins\Market\Models\TradeOffer;
use App\Plugins\Inventory\Models\UserInventory;
use App\Core\Models\User;
use Illuminate\Support\Facades\DB;

class MarketService
{
    protected float $marketFee = 0.10; // 10% market fee

    /**
     * Get active marketplace listings
     */
    public function getListings(array $filters = [], int $perPage = 20)
    {
        $query = MarketListing::with(['seller:id,username', 'item'])
            ->where('status', 'active');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price');
                break;
            case 'price_high':
                $query->orderByDesc('price');
                break;
            default:
                $query->orderByDesc('created_at');
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new marketplace listing
     */
    public function createListing(User $seller, array $data): MarketListing
    {
        return DB::transaction(function () use ($seller, $data) {
            // If listing an inventory item, verify ownership and remove from inventory
            if (!empty($data['inventory_id'])) {
                $inventory = UserInventory::where('id', $data['inventory_id'])
                    ->where('user_id', $seller->id)
                    ->firstOrFail();

                $inventory->delete();
            }

            return MarketListing::create([
                'seller_id' => $seller->id,
                'item_id' => $data['item_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'] ?? 'fixed',
                'price' => $data['price'],
                'quantity' => $data['quantity'] ?? 1,
                'status' => 'active',
            ]);
        });
    }

    /**
     * Buy a fixed-price listing
     */
    public function buyListing(MarketListing $listing, User $buyer): array
    {
        if ($listing->status !== 'active') {
            throw new \Exception('This listing is no longer available.');
        }

        if ($listing->seller_id === $buyer->id) {
            throw new \Exception('You cannot buy your own listing.');
        }

        if ($buyer->cash < $listing->price) {
            throw new \Exception('Insufficient funds.');
        }

        return DB::transaction(function () use ($listing, $buyer) {
            $fee = (int) ($listing->price * $this->marketFee);
            $sellerReceives = $listing->price - $fee;

            // Charge buyer
            $buyer->decrement('cash', $listing->price);

            // Pay seller (minus fee)
            $seller = User::findOrFail($listing->seller_id);
            $seller->increment('cash', $sellerReceives);

            // Transfer item to buyer's inventory if applicable
            if ($listing->item_id) {
                UserInventory::create([
                    'user_id' => $buyer->id,
                    'item_id' => $listing->item_id,
                    'quantity' => $listing->quantity ?? 1,
                ]);
            }

            // Mark listing as sold
            $listing->update([
                'status' => 'sold',
                'buyer_id' => $buyer->id,
                'sold_at' => now(),
            ]);

            return [
                'listing' => $listing,
                'price_paid' => $listing->price,
                'market_fee' => $fee,
                'seller_received' => $sellerReceives,
            ];
        });
    }

    /**
     * Place a bid on an auction listing
     */
    public function placeBid(MarketListing $listing, User $bidder, int $amount): MarketBid
    {
        if ($listing->type !== 'auction') {
            throw new \Exception('This is not an auction listing.');
        }

        if ($listing->seller_id === $bidder->id) {
            throw new \Exception('You cannot bid on your own listing.');
        }

        $highestBid = $listing->bids()->max('amount') ?? $listing->price;
        if ($amount <= $highestBid) {
            throw new \Exception("Bid must be higher than \${$highestBid}.");
        }

        if ($bidder->cash < $amount) {
            throw new \Exception('Insufficient funds.');
        }

        return DB::transaction(function () use ($listing, $bidder, $amount) {
            // Refund previous highest bidder
            $previousBid = $listing->bids()->orderByDesc('amount')->first();
            if ($previousBid) {
                User::find($previousBid->user_id)?->increment('cash', $previousBid->amount);
            }

            // Hold bid amount from bidder
            $bidder->decrement('cash', $amount);

            return MarketBid::create([
                'listing_id' => $listing->id,
                'user_id' => $bidder->id,
                'amount' => $amount,
            ]);
        });
    }

    /**
     * Cancel a listing
     */
    public function cancelListing(MarketListing $listing, User $user): void
    {
        if ($listing->seller_id !== $user->id) {
            throw new \Exception('You can only cancel your own listings.');
        }

        if ($listing->status !== 'active') {
            throw new \Exception('Only active listings can be cancelled.');
        }

        DB::transaction(function () use ($listing, $user) {
            // Refund active bids
            foreach ($listing->bids as $bid) {
                User::find($bid->user_id)?->increment('cash', $bid->amount);
            }

            // Return item to seller's inventory
            if ($listing->item_id) {
                UserInventory::create([
                    'user_id' => $user->id,
                    'item_id' => $listing->item_id,
                    'quantity' => $listing->quantity ?? 1,
                ]);
            }

            $listing->update(['status' => 'cancelled']);
        });
    }

    /**
     * Create a trade offer
     */
    public function createTradeOffer(User $sender, array $data): TradeOffer
    {
        if ($sender->id == $data['recipient_id']) {
            throw new \Exception('Cannot trade with yourself.');
        }

        if (($data['offer_cash'] ?? 0) > 0 && $sender->cash < $data['offer_cash']) {
            throw new \Exception('Insufficient funds for the offered cash amount.');
        }

        return TradeOffer::create([
            'sender_id' => $sender->id,
            'recipient_id' => $data['recipient_id'],
            'offer_items' => $data['offer_items'] ?? null,
            'offer_cash' => $data['offer_cash'] ?? 0,
            'request_items' => $data['request_items'] ?? null,
            'request_cash' => $data['request_cash'] ?? 0,
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);
    }

    /**
     * Accept a trade offer
     */
    public function acceptTrade(TradeOffer $trade, User $recipient): void
    {
        if ($trade->recipient_id !== $recipient->id) {
            throw new \Exception('This trade is not for you.');
        }

        if ($trade->status !== 'pending') {
            throw new \Exception('This trade is no longer pending.');
        }

        DB::transaction(function () use ($trade, $recipient) {
            $sender = User::findOrFail($trade->sender_id);

            // Exchange cash
            if ($trade->offer_cash > 0) {
                $sender->decrement('cash', $trade->offer_cash);
                $recipient->increment('cash', $trade->offer_cash);
            }
            if ($trade->request_cash > 0) {
                $recipient->decrement('cash', $trade->request_cash);
                $sender->increment('cash', $trade->request_cash);
            }

            // Exchange items (if applicable)
            if ($trade->offer_items) {
                foreach ((array) $trade->offer_items as $itemId => $qty) {
                    $inv = UserInventory::where('user_id', $sender->id)->where('item_id', $itemId)->first();
                    if ($inv) {
                        $inv->delete();
                        UserInventory::create([
                            'user_id' => $recipient->id,
                            'item_id' => $itemId,
                            'quantity' => $qty,
                        ]);
                    }
                }
            }
            if ($trade->request_items) {
                foreach ((array) $trade->request_items as $itemId => $qty) {
                    $inv = UserInventory::where('user_id', $recipient->id)->where('item_id', $itemId)->first();
                    if ($inv) {
                        $inv->delete();
                        UserInventory::create([
                            'user_id' => $sender->id,
                            'item_id' => $itemId,
                            'quantity' => $qty,
                        ]);
                    }
                }
            }

            $trade->update(['status' => 'accepted']);
        });
    }

    /**
     * Decline a trade offer
     */
    public function declineTrade(TradeOffer $trade, User $recipient): void
    {
        if ($trade->recipient_id !== $recipient->id) {
            throw new \Exception('This trade is not for you.');
        }

        $trade->update(['status' => 'declined']);
    }
}

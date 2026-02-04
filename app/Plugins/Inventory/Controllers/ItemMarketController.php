<?php

namespace App\Plugins\Inventory\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Inventory\Models\ItemMarketListing;
use App\Plugins\Inventory\Models\ItemMarketTransaction;
use App\Plugins\Inventory\Models\ItemPriceHistory;
use App\Plugins\PointsMarket\Models\PointsMarketListing;
use App\Plugins\PointsMarket\Models\PointsTransaction;
use App\Core\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ItemMarketController extends Controller
{
    /**
     * Get item market statistics and overview.
     */
    public function index(Request $request): JsonResponse
    {
        // Get statistics
        $stats = [
            'total_listings' => ItemMarketListing::count(),
            'active_listings' => ItemMarketListing::where('status', 'active')->count(),
            'total_transactions' => ItemMarketTransaction::count(),
            'total_volume' => ItemMarketTransaction::sum('total_price'),
            'total_fees' => ItemMarketTransaction::sum('market_fee'),
            'today_transactions' => ItemMarketTransaction::whereDate('completed_at', today())->count(),
            'today_volume' => ItemMarketTransaction::whereDate('completed_at', today())->sum('total_price'),
        ];

        // Get recent transactions
        $recentTransactions = ItemMarketTransaction::with(['seller:id,username,name', 'buyer:id,username,name', 'item:id,name,image'])
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'seller' => $t->seller->username ?? $t->seller->name ?? 'Unknown',
                'buyer' => $t->buyer->username ?? $t->buyer->name ?? 'Unknown',
                'item' => $t->item->name ?? 'Unknown',
                'item_image' => $t->item->image ?? null,
                'quantity' => $t->quantity,
                'total_price' => $t->total_price,
                'market_fee' => $t->market_fee,
                'completed_at' => $t->completed_at?->toIso8601String(),
            ]);

        // Get top traded items
        $topItems = ItemMarketTransaction::selectRaw('item_id, COUNT(*) as trade_count, SUM(total_price) as total_volume')
            ->groupBy('item_id')
            ->orderByDesc('trade_count')
            ->limit(10)
            ->with('item:id,name,image')
            ->get()
            ->map(fn($t) => [
                'item_id' => $t->item_id,
                'item_name' => $t->item->name ?? 'Unknown',
                'item_image' => $t->item->image ?? null,
                'trade_count' => $t->trade_count,
                'total_volume' => $t->total_volume,
            ]);

        return response()->json([
            'stats' => $stats,
            'recent_transactions' => $recentTransactions,
            'top_items' => $topItems,
        ]);
    }

    /**
     * Get all item market listings with filters.
     */
    public function listings(Request $request): JsonResponse
    {
        $query = ItemMarketListing::with(['seller:id,username,name', 'item:id,name,image,rarity']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by item
        if ($request->has('item_id') && $request->item_id) {
            $query->where('item_id', $request->item_id);
        }

        // Filter by seller
        if ($request->has('seller_id') && $request->seller_id) {
            $query->where('seller_id', $request->seller_id);
        }

        // Search by item name
        if ($request->has('search') && $request->search) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $listings = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'listings' => $listings->map(fn($l) => [
                'id' => $l->id,
                'seller_id' => $l->seller_id,
                'seller' => $l->seller->username ?? $l->seller->name ?? 'Unknown',
                'item_id' => $l->item_id,
                'item_name' => $l->item->name ?? 'Unknown',
                'item_image' => $l->item->image ?? null,
                'item_rarity' => $l->item->rarity ?? null,
                'quantity' => $l->quantity,
                'price_per_unit' => $l->price_per_unit,
                'total_price' => $l->total_price,
                'status' => $l->status,
                'description' => $l->description,
                'listed_at' => $l->listed_at?->toIso8601String(),
                'expires_at' => $l->expires_at?->toIso8601String(),
                'sold_at' => $l->sold_at?->toIso8601String(),
                'created_at' => $l->created_at?->toIso8601String(),
            ]),
            'pagination' => [
                'total' => $listings->total(),
                'per_page' => $listings->perPage(),
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
            ],
        ]);
    }

    /**
     * Get all transactions with filters.
     */
    public function transactions(Request $request): JsonResponse
    {
        $query = ItemMarketTransaction::with([
            'seller:id,username,name',
            'buyer:id,username,name',
            'item:id,name,image',
        ]);

        // Filter by item
        if ($request->has('item_id') && $request->item_id) {
            $query->where('item_id', $request->item_id);
        }

        // Filter by user (buyer or seller)
        if ($request->has('user_id') && $request->user_id) {
            $query->where(function ($q) use ($request) {
                $q->where('seller_id', $request->user_id)
                    ->orWhere('buyer_id', $request->user_id);
            });
        }

        // Date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('completed_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('completed_at', '<=', $request->to_date);
        }

        $query->orderBy('completed_at', 'desc');

        $transactions = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'transactions' => $transactions->map(fn($t) => [
                'id' => $t->id,
                'listing_id' => $t->listing_id,
                'seller_id' => $t->seller_id,
                'seller' => $t->seller->username ?? $t->seller->name ?? 'Unknown',
                'buyer_id' => $t->buyer_id,
                'buyer' => $t->buyer->username ?? $t->buyer->name ?? 'Unknown',
                'item_id' => $t->item_id,
                'item_name' => $t->item->name ?? 'Unknown',
                'item_image' => $t->item->image ?? null,
                'quantity' => $t->quantity,
                'price_per_unit' => $t->price_per_unit,
                'total_price' => $t->total_price,
                'market_fee' => $t->market_fee,
                'seller_receives' => $t->seller_receives,
                'completed_at' => $t->completed_at?->toIso8601String(),
            ]),
            'pagination' => [
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
            ],
        ]);
    }

    /**
     * Get price history for an item.
     */
    public function priceHistory(Request $request, int $itemId): JsonResponse
    {
        $days = $request->get('days', 30);

        $history = ItemPriceHistory::where('item_id', $itemId)
            ->where('date', '>=', now()->subDays($days))
            ->orderBy('date')
            ->get()
            ->map(fn($h) => [
                'date' => $h->date->format('Y-m-d'),
                'average_price' => $h->average_price,
                'lowest_price' => $h->lowest_price,
                'highest_price' => $h->highest_price,
                'total_sold' => $h->total_sold,
            ]);

        $item = Item::find($itemId);

        return response()->json([
            'item' => [
                'id' => $item->id ?? $itemId,
                'name' => $item->name ?? 'Unknown',
                'image' => $item->image ?? null,
            ],
            'history' => $history,
        ]);
    }

    /**
     * Cancel a listing (admin action).
     */
    public function cancelListing(int $id): JsonResponse
    {
        $listing = ItemMarketListing::findOrFail($id);

        if ($listing->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Listing is not active and cannot be cancelled.',
            ], 400);
        }

        $listing->update(['status' => 'cancelled']);

        // TODO: Return items to seller inventory

        return response()->json([
            'success' => true,
            'message' => 'Listing cancelled successfully.',
        ]);
    }

    /**
     * Delete a listing (admin action).
     */
    public function deleteListing(int $id): JsonResponse
    {
        $listing = ItemMarketListing::findOrFail($id);
        $listing->delete();

        return response()->json([
            'success' => true,
            'message' => 'Listing deleted successfully.',
        ]);
    }

    // Points Market Methods

    /**
     * Get points market statistics.
     */
    public function pointsMarket(Request $request): JsonResponse
    {
        // Points Market plugin is not installed
        // Return empty data structure for compatibility
        return response()->json([
            'stats' => [
                'total_listings' => 0,
                'active_listings' => 0,
                'total_transactions' => 0,
                'total_points_traded' => 0,
                'total_cash_traded' => 0,
            ],
            'active_listings' => [],
            'recent_transactions' => [],
            'message' => 'Points Market plugin is not installed',
        ]);
    }
}

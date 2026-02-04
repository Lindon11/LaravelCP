<?php

namespace App\Plugins\Market;

use App\Plugins\Plugin;
use App\Plugins\Market\Models\MarketListing;
use App\Plugins\Market\Models\TradeOffer;

/**
 * Market Module
 *
 * Player-to-player trading and auction house
 */
class MarketModule extends Plugin
{
    protected string $name = 'Market';

    public function construct(): void
    {
        $this->config = [
            'listing_fee_percent' => 5,
            'sale_fee_percent' => 10,
            'max_listings_per_user' => 20,
            'max_auction_duration' => 7 * 24 * 3600, // 7 days
            'min_auction_duration' => 3600, // 1 hour
            'min_price' => 1,
            'max_price' => 999999999,
        ];
    }

    /**
     * Get active listings
     */
    public function getListings(array $filters = []): array
    {
        $query = MarketListing::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['seller:id,username,level', 'item']);

        if (!empty($filters['item_type'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('type', $filters['item_type']);
            });
        }

        if (!empty($filters['search'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        $sortBy = $filters['sort'] ?? 'created_at';
        $sortOrder = $filters['order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate(20)->toArray();
    }

    /**
     * Get user's listings
     */
    public function getMyListings(int $userId): array
    {
        return MarketListing::where('seller_id', $userId)
            ->with('item')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Create a listing
     */
    public function createListing(int $userId, int $itemId, int $quantity, int $price, ?string $type = 'fixed', ?int $duration = null): array
    {
        $currentListings = MarketListing::where('seller_id', $userId)
            ->where('status', 'active')
            ->count();

        if ($currentListings >= $this->config['max_listings_per_user']) {
            return ['success' => false, 'message' => 'Maximum listings reached'];
        }

        if ($price < $this->config['min_price'] || $price > $this->config['max_price']) {
            return ['success' => false, 'message' => 'Invalid price'];
        }

        $listing = MarketListing::create([
            'seller_id' => $userId,
            'item_id' => $itemId,
            'quantity' => $quantity,
            'price' => $price,
            'type' => $type,
            'status' => 'active',
            'expires_at' => $duration ? now()->addSeconds($duration) : null,
        ]);

        return ['success' => true, 'message' => 'Listing created', 'listing' => $listing];
    }

    /**
     * Get module stats
     */
    public function getStats(?\App\Core\Models\User $user = null): array
    {
        $activeListings = MarketListing::where('status', 'active')->count();

        $userListings = 0;
        $userSales = 0;

        if ($user) {
            $userListings = MarketListing::where('seller_id', $user->id)
                ->where('status', 'active')
                ->count();
            $userSales = MarketListing::where('seller_id', $user->id)
                ->where('status', 'sold')
                ->count();
        }

        return [
            'active_listings' => $activeListings,
            'your_listings' => $userListings,
            'your_sales' => $userSales,
        ];
    }
}

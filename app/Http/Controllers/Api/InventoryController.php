<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Get player inventory
     */
    public function index(Request $request)
    {
        $player = $request->user();
        $inventory = $this->inventoryService->getPlayerInventory($player);

        return response()->json([
            'player' => $player,
            'inventory' => $inventory,
        ]);
    }

    /**
     * Get shop items
     */
    public function shop(Request $request)
    {
        $player = $request->user();
        $items = Item::all();

        return response()->json([
            'player' => $player,
            'items' => $items,
        ]);
    }

    /**
     * Buy an item
     */
    public function buy(Request $request, Item $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $player = $request->user();

        try {
            $this->inventoryService->buyItem($player, $item->id, $request->quantity);
            return response()->json([
                'success' => true,
                'message' => "Purchased {$request->quantity}x {$item->name}!",
                'player' => $player->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Sell an item
     */
    public function sell(Request $request, $inventoryId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $player = $request->user();

        try {
            $this->inventoryService->sellItem($player, $inventoryId, $request->quantity);
            return response()->json([
                'success' => true,
                'message' => 'Item sold successfully!',
                'player' => $player->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Equip an item
     */
    public function equip(Request $request, $inventoryId)
    {
        $player = $request->user();

        try {
            $this->inventoryService->equipItem($player, $inventoryId);
            return response()->json([
                'success' => true,
                'message' => 'Item equipped!',
                'player' => $player->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Unequip an item
     */
    public function unequip(Request $request, $inventoryId)
    {
        $player = $request->user();

        try {
            $this->inventoryService->unequipItem($player, $inventoryId);
            return response()->json([
                'success' => true,
                'message' => 'Item unequipped!',
                'player' => $player->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Use an item
     */
    public function use(Request $request, $inventoryId)
    {
        $player = $request->user();

        try {
            $result = $this->inventoryService->useItem($player, $inventoryId);
            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Item used!',
                'player' => $player->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

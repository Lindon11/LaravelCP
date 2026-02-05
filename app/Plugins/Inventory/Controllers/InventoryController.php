<?php

namespace App\Plugins\Inventory\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\Item;
use App\Plugins\Inventory\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $player = $request->user();
        $inventory = $this->inventoryService->getPlayerInventory($player);

        return response()->json([
            'player' => $player,
            'inventory' => $inventory,
        ]);
    }

    public function shop(Request $request)
    {
        $player = $request->user();
        $items = Item::all();

        return response()->json([
            'player' => $player,
            'items' => $items,
        ]);
    }

    public function buy(Request $request, Item $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $player = $request->user();

        try {
            $this->inventoryService->buyItem($player, $item->id, $request->quantity);
            return redirect()->back()->with('success', "Purchased {$request->quantity}x {$item->name}!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function sell(Request $request, $inventoryId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $player = $request->user();

        try {
            $this->inventoryService->sellItem($player, $inventoryId, $request->quantity);
            return redirect()->back()->with('success', 'Item sold successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function equip(Request $request, $inventoryId)
    {
        $player = $request->user();

        try {
            $this->inventoryService->equipItem($player, $inventoryId);
            return redirect()->back()->with('success', 'Item equipped!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function unequip(Request $request, $inventoryId)
    {
        $player = $request->user();

        try {
            $this->inventoryService->unequipItem($player, $inventoryId);
            return redirect()->back()->with('success', 'Item unequipped!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function use(Request $request, $inventoryId)
    {
        $player = $request->user();

        try {
            $this->inventoryService->useItem($player, $inventoryId);
            return redirect()->back()->with('success', 'Item used!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

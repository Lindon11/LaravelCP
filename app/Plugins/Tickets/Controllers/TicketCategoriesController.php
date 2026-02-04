<?php

namespace App\Plugins\Tickets\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Tickets\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketCategoriesController extends Controller
{
    /**
     * Display a listing of ticket categories.
     */
    public function index()
    {
        $categories = TicketCategory::orderBy('name')->get();

        return response()->json([
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created ticket category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_categories,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        $category = TicketCategory::create($validated);

        return response()->json([
            'message' => 'Ticket category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Update the specified ticket category.
     */
    public function update(Request $request, $id)
    {
        $category = TicketCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:ticket_categories,name,' . $id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Ticket category updated successfully',
            'category' => $category
        ]);
    }

    /**
     * Remove the specified ticket category.
     */
    public function destroy($id)
    {
        $category = TicketCategory::findOrFail($id);

        // Check if category has tickets
        if ($category->tickets()->exists()) {
            return response()->json([
                'error' => 'Cannot delete category with existing tickets'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => 'Ticket category deleted successfully'
        ]);
    }
}

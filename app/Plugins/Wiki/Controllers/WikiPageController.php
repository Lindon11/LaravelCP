<?php

namespace App\Plugins\Wiki\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Wiki\Models\WikiPage;
use Illuminate\Http\Request;

class WikiPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WikiPage::with('category');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        $pages = $query->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:wiki_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:wiki_pages,slug',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'is_published' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $page = WikiPage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully',
            'data' => $page->load('category'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $page = WikiPage::with('category')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $page,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $page = WikiPage::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'nullable|exists:wiki_categories,id',
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255|unique:wiki_pages,slug,' . $id,
            'excerpt' => 'nullable|string',
            'content' => 'sometimes|required|string',
            'is_published' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $page->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully',
            'data' => $page->load('category'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $page = WikiPage::findOrFail($id);
        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully',
        ]);
    }
}

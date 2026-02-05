<?php

namespace App\Plugins\Wiki\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Wiki\Models\FaqCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = FaqCategory::withCount('faqs')
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $category = FaqCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ category created successfully.',
            'data' => $category,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $category = FaqCategory::with(['faqs' => function ($q) {
            $q->orderBy('order');
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = FaqCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ category updated successfully.',
            'data' => $category,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $category = FaqCategory::findOrFail($id);

        if ($category->faqs()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing FAQs. Move or delete them first.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ category deleted successfully.',
        ]);
    }
}

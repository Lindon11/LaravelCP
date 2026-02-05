<?php

namespace App\Plugins\Wiki\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Wiki\Services\WikiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WikiController extends Controller
{
    protected WikiService $wikiService;

    public function __construct(WikiService $wikiService)
    {
        $this->wikiService = $wikiService;
    }

    /**
     * Get all wiki categories
     */
    public function categories(): JsonResponse
    {
        $categories = $this->wikiService->getCategories();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get a single category with its pages
     */
    public function category(int $id): JsonResponse
    {
        $data = $this->wikiService->getCategory($id);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get published wiki pages
     */
    public function pages(Request $request): JsonResponse
    {
        $pages = $this->wikiService->getPages(
            $request->only(['category_id', 'search']),
            $request->integer('per_page', 20)
        );

        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }

    /**
     * Get a single wiki page by slug or ID
     */
    public function page(string $identifier): JsonResponse
    {
        try {
            $page = $this->wikiService->getPage($identifier);

            return response()->json([
                'success' => true,
                'data' => $page,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }
    }

    /**
     * Search wiki
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);

        $results = $this->wikiService->search(
            $request->input('q'),
            $request->integer('per_page', 20)
        );

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Get all FAQs
     */
    public function faqs(): JsonResponse
    {
        $faqs = $this->wikiService->getFaqs();

        return response()->json([
            'success' => true,
            'data' => $faqs,
        ]);
    }

    /**
     * Get FAQ categories with their questions
     */
    public function faqCategories(): JsonResponse
    {
        $categories = $this->wikiService->getFaqCategories();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}

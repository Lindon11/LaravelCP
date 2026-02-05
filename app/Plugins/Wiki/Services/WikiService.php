<?php

namespace App\Plugins\Wiki\Services;

use App\Plugins\Wiki\Models\WikiPage;
use App\Plugins\Wiki\Models\WikiCategory;
use App\Plugins\Wiki\Models\Faq;
use App\Plugins\Wiki\Models\FaqCategory;
use Illuminate\Support\Facades\Cache;

class WikiService
{
    protected int $cacheTtl = 600; // 10 minutes

    /**
     * Get all wiki categories with published page counts
     */
    public function getCategories()
    {
        return Cache::remember('wiki:categories', $this->cacheTtl, function () {
            return WikiCategory::withCount('publishedPages')
                ->orderBy('order')
                ->get();
        });
    }

    /**
     * Get a single category with its published pages
     */
    public function getCategory(int $categoryId): array
    {
        $category = WikiCategory::findOrFail($categoryId);

        return [
            'category' => $category,
            'pages' => $category->publishedPages()
                ->select('id', 'title', 'slug', 'excerpt', 'order', 'views')
                ->get(),
        ];
    }

    /**
     * Get all published pages, optionally filtered
     */
    public function getPages(array $filters = [], int $perPage = 20)
    {
        $query = WikiPage::with('category:id,name,slug')
            ->where('is_published', true);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('content', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('order')->paginate($perPage);
    }

    /**
     * Get a single page by slug or ID and track views
     */
    public function getPage(string $identifier): WikiPage
    {
        $page = WikiPage::where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->where('is_published', true)
            ->with('category:id,name,slug')
            ->firstOrFail();

        $page->incrementViews();

        return $page;
    }

    /**
     * Search wiki pages
     */
    public function search(string $query, int $perPage = 20)
    {
        return WikiPage::where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->with('category:id,name,slug')
            ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", ["%{$query}%"])
            ->paginate($perPage);
    }

    /**
     * Get all active FAQs
     */
    public function getFaqs()
    {
        return Cache::remember('wiki:faqs', $this->cacheTtl, function () {
            return Faq::where('is_active', true)
                ->with('category')
                ->orderBy('order')
                ->get();
        });
    }

    /**
     * Get FAQ categories with their FAQs
     */
    public function getFaqCategories()
    {
        return FaqCategory::with(['faqs' => function ($q) {
            $q->where('is_active', true)->orderBy('order');
        }])
        ->orderBy('order')
        ->get();
    }

    /**
     * Clear wiki cache
     */
    public function clearCache(): void
    {
        Cache::forget('wiki:categories');
        Cache::forget('wiki:faqs');
    }
}

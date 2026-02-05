<?php

namespace App\Plugins\Wiki;

use App\Plugins\Plugin;

class WikiModule extends Plugin
{
    protected string $name = 'Wiki';

    public function construct(): void
    {
        $this->config = [
            'pages_per_page' => 20,
            'faqs_per_page' => 50,
            'allow_guest_view' => true,
            'enable_search' => true,
        ];
    }

    public function getCategories(): array
    {
        $categories = \App\Plugins\Wiki\Models\WikiCategory::withCount('publishedPages')
            ->orderBy('order')
            ->get();

        return $this->applyModuleHook('alterWikiCategories', [
            'categories' => $categories,
        ]);
    }

    public function getStats(?\App\Core\Models\User $user = null): array
    {
        return [
            'total_pages' => \App\Plugins\Wiki\Models\WikiPage::where('is_published', true)->count(),
            'total_categories' => \App\Plugins\Wiki\Models\WikiCategory::count(),
            'total_faqs' => \App\Plugins\Wiki\Models\Faq::where('is_active', true)->count(),
        ];
    }
}

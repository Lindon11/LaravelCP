<?php

use App\Plugins\Wiki\WikiModule;
use Illuminate\Support\Facades\Log;

return [
    'OnPageCreated' => function ($data) {
        Log::info('Wiki page created', [
            'page_id' => $data['page']->id,
            'title' => $data['page']->title,
        ]);
        return $data;
    },

    'OnPageUpdated' => function ($data) {
        Log::info('Wiki page updated', [
            'page_id' => $data['page']->id,
            'title' => $data['page']->title,
        ]);
        return $data;
    },

    'alterWikiCategories' => function ($data) {
        return $data;
    },
];

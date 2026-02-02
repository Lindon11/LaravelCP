<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmojiController extends Controller
{
    /**
     * Get all available emojis organized by category
     */
    public function index()
    {
        $categories = config('emojis.categories', []);
        $quickReactions = config('emojis.quick_reactions', []);

        return response()->json([
            'categories' => $categories,
            'quick_reactions' => $quickReactions,
        ]);
    }

    /**
     * Get quick reaction emojis only
     */
    public function quickReactions()
    {
        return response()->json(config('emojis.quick_reactions', []));
    }

    /**
     * Search emojis (you can enhance this with a proper emoji library)
     */
    public function search(Request $request)
    {
        $query = strtolower($request->get('q', ''));
        
        if (empty($query)) {
            return response()->json([]);
        }

        $categories = config('emojis.categories', []);
        $results = [];

        // Simple search by category name
        foreach ($categories as $category => $emojis) {
            if (str_contains($category, $query)) {
                $results = array_merge($results, $emojis);
            }
        }

        return response()->json(array_unique($results));
    }
}

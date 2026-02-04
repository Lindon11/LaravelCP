<?php

namespace App\Core\Http\Controllers;

use App\Core\Facades\TextFormatter;
use App\Core\Services\TextFormatterService;
use Illuminate\Http\Request;

class TextFormatterController extends Controller
{
    protected TextFormatterService $formatter;

    public function __construct(TextFormatterService $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Preview formatted text (for live preview in editors)
     */
    public function preview(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:50000',
            'bbcode' => 'sometimes|boolean',
            'emoji' => 'sometimes|boolean',
        ]);

        $text = $request->input('text');
        $options = [
            'bbcode' => $request->boolean('bbcode', true),
            'emoji' => $request->boolean('emoji', true),
            'shortcodes' => true,
            'ascii' => true,
            'sanitize' => true,
        ];

        $formatted = $this->formatter->format($text, $options);

        return response()->json([
            'original' => $text,
            'formatted' => $formatted,
        ]);
    }

    /**
     * Get available BBCode tags
     */
    public function bbcodes()
    {
        return response()->json([
            'bbcodes' => $this->formatter->getAvailableBBCodes(),
        ]);
    }

    /**
     * Get popular emoji shortcodes
     */
    public function emojis()
    {
        return response()->json([
            'emojis' => $this->formatter->getPopularEmojis(),
        ]);
    }

    /**
     * Convert text to plain (for notifications, etc.)
     */
    public function plain(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:50000',
        ]);

        return response()->json([
            'plain' => $this->formatter->formatPlain($request->input('text')),
        ]);
    }

    /**
     * Search emoji shortcodes
     */
    public function searchEmoji(Request $request)
    {
        $query = strtolower($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $allEmojis = $this->formatter->getPopularEmojis();
        $results = [];

        foreach ($allEmojis as $category => $emojis) {
            foreach ($emojis as $shortcode) {
                if (str_contains($shortcode, $query)) {
                    $results[] = [
                        'shortcode' => $shortcode,
                        'category' => $category,
                        'unicode' => $this->formatter->shortcodeToUnicode($shortcode),
                    ];
                }
            }
        }

        return response()->json([
            'results' => array_slice($results, 0, 20),
        ]);
    }
}

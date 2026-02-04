<?php

namespace App\Core\Services;

use Golonka\BBCode\BBCodeParser;
use JoyPixels\Client as JoyPixelsClient;
use JoyPixels\Ruleset;

class TextFormatterService
{
    protected BBCodeParser $bbcode;
    protected JoyPixelsClient $joypixels;

    public function __construct()
    {
        $this->bbcode = new BBCodeParser();
        $this->joypixels = new JoyPixelsClient(new Ruleset());

        // Configure JoyPixels
        $this->joypixels->ascii = true; // Convert ASCII emoticons like :) to emoji
        $this->joypixels->sprites = false; // Use individual images instead of sprites
        $this->joypixels->emojiSize = 32; // Emoji size

        // Add custom BBCode tags
        $this->registerCustomBBCodes();
    }

    /**
     * Format text with both BBCode and emoji conversion
     */
    public function format(string $text, array $options = []): string
    {
        $options = array_merge([
            'bbcode' => true,
            'emoji' => true,
            'shortcodes' => true,
            'ascii' => true,
            'sanitize' => true,
        ], $options);

        // Sanitize HTML first if enabled
        if ($options['sanitize']) {
            $text = $this->sanitizeHtml($text);
        }

        // Parse BBCode
        if ($options['bbcode']) {
            $text = $this->parseBBCode($text);
        }

        // Convert emoji shortcodes (:smile:) to images
        if ($options['shortcodes']) {
            $text = $this->joypixels->shortnameToImage($text);
        }

        // Convert unicode emoji to images
        if ($options['emoji']) {
            $text = $this->joypixels->toImage($text);
        }

        return $text;
    }

    /**
     * Format text for plain output (strips HTML)
     */
    public function formatPlain(string $text): string
    {
        // Convert shortcodes to unicode
        $text = $this->joypixels->shortnameToUnicode($text);

        // Strip any HTML/BBCode
        $text = strip_tags($text);

        return $text;
    }

    /**
     * Convert emoji shortcodes only (no BBCode)
     */
    public function parseEmoji(string $text, bool $toImage = true): string
    {
        if ($toImage) {
            $text = $this->joypixels->shortnameToImage($text);
            $text = $this->joypixels->toImage($text);
        } else {
            $text = $this->joypixels->shortnameToUnicode($text);
        }

        return $text;
    }

    /**
     * Parse BBCode only (no emoji)
     */
    public function parseBBCode(string $text): string
    {
        return $this->bbcode->parse($text);
    }

    /**
     * Get all available BBCode tags
     */
    public function getAvailableBBCodes(): array
    {
        return [
            'text' => [
                ['tag' => 'b', 'description' => 'Bold text', 'example' => '[b]bold[/b]'],
                ['tag' => 'i', 'description' => 'Italic text', 'example' => '[i]italic[/i]'],
                ['tag' => 'u', 'description' => 'Underlined text', 'example' => '[u]underline[/u]'],
                ['tag' => 's', 'description' => 'Strikethrough text', 'example' => '[s]strikethrough[/s]'],
                ['tag' => 'size', 'description' => 'Font size', 'example' => '[size=20]large text[/size]'],
                ['tag' => 'color', 'description' => 'Text color', 'example' => '[color=red]red text[/color]'],
                ['tag' => 'highlight', 'description' => 'Highlight text', 'example' => '[highlight=yellow]highlighted[/highlight]'],
            ],
            'links' => [
                ['tag' => 'url', 'description' => 'Create a link', 'example' => '[url=https://example.com]click here[/url]'],
                ['tag' => 'email', 'description' => 'Email link', 'example' => '[email]user@example.com[/email]'],
            ],
            'media' => [
                ['tag' => 'img', 'description' => 'Display an image', 'example' => '[img]https://example.com/image.png[/img]'],
                ['tag' => 'youtube', 'description' => 'Embed YouTube video', 'example' => '[youtube]VIDEO_ID[/youtube]'],
            ],
            'formatting' => [
                ['tag' => 'quote', 'description' => 'Quote block', 'example' => '[quote]quoted text[/quote]'],
                ['tag' => 'code', 'description' => 'Code block', 'example' => '[code]code here[/code]'],
                ['tag' => 'spoiler', 'description' => 'Hidden spoiler', 'example' => '[spoiler]hidden content[/spoiler]'],
                ['tag' => 'center', 'description' => 'Center align', 'example' => '[center]centered[/center]'],
                ['tag' => 'left', 'description' => 'Left align', 'example' => '[left]left aligned[/left]'],
                ['tag' => 'right', 'description' => 'Right align', 'example' => '[right]right aligned[/right]'],
            ],
            'lists' => [
                ['tag' => 'list', 'description' => 'Bullet list', 'example' => '[list][*]item 1[*]item 2[/list]'],
                ['tag' => 'olist', 'description' => 'Numbered list', 'example' => '[olist][*]item 1[*]item 2[/olist]'],
            ],
        ];
    }

    /**
     * Get popular emoji shortcodes
     */
    public function getPopularEmojis(): array
    {
        return [
            'smileys' => [
                ':smile:', ':laughing:', ':blush:', ':smiley:', ':relaxed:',
                ':smirk:', ':heart_eyes:', ':kissing_heart:', ':wink:', ':stuck_out_tongue:',
                ':joy:', ':sob:', ':rage:', ':scream:', ':thinking:',
            ],
            'gestures' => [
                ':+1:', ':-1:', ':clap:', ':wave:', ':ok_hand:',
                ':muscle:', ':pray:', ':raised_hands:', ':fist:', ':v:',
            ],
            'hearts' => [
                ':heart:', ':blue_heart:', ':green_heart:', ':yellow_heart:', ':purple_heart:',
                ':broken_heart:', ':sparkling_heart:', ':heartpulse:', ':two_hearts:', ':revolving_hearts:',
            ],
            'objects' => [
                ':fire:', ':star:', ':zap:', ':boom:', ':sparkles:',
                ':trophy:', ':medal:', ':crown:', ':gem:', ':moneybag:',
            ],
            'symbols' => [
                ':white_check_mark:', ':x:', ':warning:', ':question:', ':exclamation:',
                ':100:', ':chart_increasing:', ':clock:', ':bell:', ':lock:',
            ],
        ];
    }

    /**
     * Register custom BBCode tags
     */
    protected function registerCustomBBCodes(): void
    {
        // Spoiler tag
        $this->bbcode->setParser(
            'spoiler',
            '/\[spoiler\](.*?)\[\/spoiler\]/s',
            '<details class="spoiler"><summary>Spoiler (click to reveal)</summary><div class="spoiler-content">$1</div></details>',
            '$1'
        );

        // YouTube embed
        $this->bbcode->setParser(
            'youtube',
            '/\[youtube\](.*?)\[\/youtube\]/s',
            '<div class="video-embed"><iframe width="560" height="315" src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe></div>',
            '$1'
        );

        // Highlight tag
        $this->bbcode->setParser(
            'highlight',
            '/\[highlight(?:=([^\]]+))?\](.*?)\[\/highlight\]/s',
            '<mark style="background-color: $1;">$2</mark>',
            '$2'
        );

        // Player mention (game-specific)
        $this->bbcode->setParser(
            'player',
            '/\[player\](.*?)\[\/player\]/s',
            '<a href="/player/$1" class="player-mention">@$1</a>',
            '@$1'
        );

        // Gang mention (game-specific)
        $this->bbcode->setParser(
            'gang',
            '/\[gang\](.*?)\[\/gang\]/s',
            '<a href="/gang/$1" class="gang-mention">[G] $1</a>',
            '[G] $1'
        );
    }

    /**
     * Basic HTML sanitization (allows safe tags)
     */
    protected function sanitizeHtml(string $text): string
    {
        // Escape HTML entities first
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        return $text;
    }

    /**
     * Convert emoji to shortcodes for storage
     */
    public function emojiToShortcode(string $text): string
    {
        return $this->joypixels->toShort($text);
    }

    /**
     * Convert shortcodes to unicode for display (no images)
     */
    public function shortcodeToUnicode(string $text): string
    {
        return $this->joypixels->shortnameToUnicode($text);
    }
}

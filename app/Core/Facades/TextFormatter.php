<?php

namespace App\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string format(string $text, array $options = [])
 * @method static string formatPlain(string $text)
 * @method static string parseEmoji(string $text, bool $toImage = true)
 * @method static string parseBBCode(string $text)
 * @method static array getAvailableBBCodes()
 * @method static array getPopularEmojis()
 * @method static string emojiToShortcode(string $text)
 * @method static string shortcodeToUnicode(string $text)
 *
 * @see \App\Core\Services\TextFormatterService
 */
class TextFormatter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'text-formatter';
    }
}

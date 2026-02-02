<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Hook Facade
 * 
 * @method static void register(string $hookName, \Closure $callback, int $priority = 10)
 * @method static mixed run(string $hookName, mixed $data = null, bool $returnSingle = false)
 * @method static bool has(string $hookName)
 * @method static int count(string $hookName)
 * @method static void clear(string $hookName)
 * @method static void clearAll()
 * @method static array getHookNames()
 * @method static array getDebugInfo()
 * @method static mixed filter(string $hookName, mixed $value)
 * @method static array action(string $hookName, mixed $data = null)
 * 
 * @see \App\Services\HookService
 */
class Hook extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'hook';
    }
}

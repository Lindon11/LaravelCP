<?php

namespace App\Events\Module;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Base class for all module hook events
 */
abstract class ModuleHookEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Get the event name for hook registration
     */
    abstract public static function getName(): string;

    /**
     * Get the event data
     */
    abstract public function getData(): array;
}

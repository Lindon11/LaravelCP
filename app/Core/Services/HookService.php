<?php

namespace App\Core\Services;

use Closure;
use Illuminate\Support\Collection;

/**
 * Hook Service - Mimics Gangster Legends V2 Hook System
 * Allows modules to register and execute hooks for extensibility
 */
class HookService
{
    /**
     * Registered hooks
     * @var array<string, array<Closure>>
     */
    protected array $hooks = [];

    /**
     * Hook execution counts (for debugging)
     * @var array<string, int>
     */
    protected array $executionCounts = [];

    /**
     * Register a hook callback
     *
     * @param string $hookName
     * @param Closure $callback
     * @param int $priority Higher numbers run first
     * @return void
     */
    public function register(string $hookName, Closure $callback, int $priority = 10): void
    {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
        }

        $this->hooks[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Sort by priority (highest first)
        usort($this->hooks[$hookName], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    /**
     * Run a hook and collect results
     *
     * @param string $hookName
     * @param mixed $data Initial data
     * @param bool $returnSingle Return single modified value instead of array
     * @return mixed
     */
    public function run(string $hookName, mixed $data = null, bool $returnSingle = false): mixed
    {
        if (!isset($this->hooks[$hookName])) {
            return $returnSingle ? $data : [];
        }

        $this->executionCounts[$hookName] = ($this->executionCounts[$hookName] ?? 0) + 1;

        $results = [];

        foreach ($this->hooks[$hookName] as $hook) {
            $callback = $hook['callback'];
            
            if ($returnSingle) {
                // Pass data through each callback (WordPress filter style)
                $data = $callback($data);
            } else {
                // Collect results from each callback (WordPress action style)
                $result = $callback($data);
                if ($result !== null) {
                    $results[] = $result;
                }
            }
        }

        return $returnSingle ? $data : $results;
    }

    /**
     * Check if a hook has any callbacks registered
     *
     * @param string $hookName
     * @return bool
     */
    public function has(string $hookName): bool
    {
        return isset($this->hooks[$hookName]) && count($this->hooks[$hookName]) > 0;
    }

    /**
     * Get count of callbacks for a hook
     *
     * @param string $hookName
     * @return int
     */
    public function count(string $hookName): int
    {
        return isset($this->hooks[$hookName]) ? count($this->hooks[$hookName]) : 0;
    }

    /**
     * Clear all callbacks for a hook
     *
     * @param string $hookName
     * @return void
     */
    public function clear(string $hookName): void
    {
        unset($this->hooks[$hookName]);
    }

    /**
     * Clear all hooks
     *
     * @return void
     */
    public function clearAll(): void
    {
        $this->hooks = [];
        $this->executionCounts = [];
    }

    /**
     * Get all registered hook names
     *
     * @return array<string>
     */
    public function getHookNames(): array
    {
        return array_keys($this->hooks);
    }

    /**
     * Get debug information about hooks
     *
     * @return array
     */
    public function getDebugInfo(): array
    {
        $info = [];
        
        foreach ($this->hooks as $hookName => $callbacks) {
            $info[$hookName] = [
                'callback_count' => count($callbacks),
                'execution_count' => $this->executionCounts[$hookName] ?? 0,
                'priorities' => array_column($callbacks, 'priority'),
            ];
        }

        return $info;
    }

    /**
     * Apply a filter hook (alias for run with returnSingle = true)
     *
     * @param string $hookName
     * @param mixed $value
     * @return mixed
     */
    public function filter(string $hookName, mixed $value): mixed
    {
        return $this->run($hookName, $value, true);
    }

    /**
     * Execute an action hook (alias for run with returnSingle = false)
     *
     * @param string $hookName
     * @param mixed $data
     * @return array
     */
    public function action(string $hookName, mixed $data = null): array
    {
        return $this->run($hookName, $data, false);
    }
}

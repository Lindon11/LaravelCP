<?php

namespace App\Plugins;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Facades\Hook;

/**
 * Base Plugin Class
 * All game plugins extend this class
 */
abstract class Plugin
{
    /**
     * Module name
     */
    protected string $name;

    /**
     * Module configuration
     */
    protected array $config;

    /**
     * Allowed HTTP methods for actions
     * Example: ['actionName' => ['method' => 'POST', 'rules' => [...]]]
     */
    protected array $allowedMethods = [];

    /**
     * Page title
     */
    protected string $pageName = '';

    /**
     * Module HTML output
     */
    protected string $html = '';

    /**
     * Alert messages
     */
    protected array $alerts = [];

    /**
     * Constructor
     */
    public function __construct(string $name = '', array $config = [])
    {
        $this->name = $name ?: $this->name ?? '';
        $this->config = array_merge($this->config ?? [], $config);

        // Call the module's construct method to initialize
        $this->construct();
    }

    /**
     * Get module name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get page title
     */
    public function getPageName(): string
    {
        return $this->pageName ?: $this->name;
    }

    /**
     * Get module HTML output
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * Get alerts
     */
    public function getAlerts(): array
    {
        return $this->alerts;
    }

    /**
     * Add HTML to output
     */
    protected function addHtml(string $html): void
    {
        $this->html .= $html;
    }

    /**
     * Build element from view
     */
    protected function buildElement(string $view, array $data = []): string
    {
        // Try module view first, fall back to shared views
        $viewName = "{$this->name}::{$view}";

        if (!View::exists($viewName)) {
            $viewName = "modules.{$view}";
        }

        if (!View::exists($viewName)) {
            return "<!-- View {$view} not found -->";
        }

        return View::make($viewName, $data)->render();
    }

    /**
     * Add success alert
     */
    protected function success(string $message): void
    {
        $this->alerts[] = [
            'type' => 'success',
            'message' => $message,
        ];
    }

    /**
     * Add error alert
     */
    protected function error(string $message): void
    {
        $this->alerts[] = [
            'type' => 'error',
            'message' => $message,
        ];
    }

    /**
     * Add info alert
     */
    protected function info(string $message): void
    {
        $this->alerts[] = [
            'type' => 'info',
            'message' => $message,
        ];
    }

    /**
     * Add warning alert
     */
    protected function warning(string $message): void
    {
        $this->alerts[] = [
            'type' => 'warning',
            'message' => $message,
        ];
    }

    /**
     * Format money
     */
    protected function money(float $amount): string
    {
        return Hook::filter('currencyFormat', $amount);
    }

    /**
     * Format date
     */
    protected function date($timestamp, string $format = 'Y-m-d H:i:s'): string
    {
        if (is_numeric($timestamp)) {
            return date($format, $timestamp);
        }

        if ($timestamp instanceof \DateTime) {
            return $timestamp->format($format);
        }

        return $timestamp;
    }

    /**
     * Check if user can access this module
     */
    public function canAccess($user): bool
    {
        // Override in module if needed
        return true;
    }

    /**
     * Validate method data
     */
    protected function validateMethod(string $action, array $data): array
    {
        if (!isset($this->allowedMethods[$action])) {
            return ['error' => 'Invalid action'];
        }

        $methodConfig = $this->allowedMethods[$action];
        $rules = $methodConfig['rules'] ?? [];

        if (empty($rules)) {
            return [];
        }

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            return ['errors' => $validator->errors()->toArray()];
        }

        return $validator->validated();
    }

    /**
     * Apply module data hooks
     */
    protected function applyModuleHook(string $hookName, array $data): array
    {
        return Hook::filter($hookName, [
            'module' => $this->name,
            'user' => Auth::user(),
            'data' => $data,
        ])['data'] ?? $data;
    }

    /**
     * Track user action
     */
    protected function trackAction(string $actionType, array $data): void
    {
        Hook::action('afterUserAction', array_merge([
            'user' => Auth::id(),
            'module' => $this->name,
            'action' => $actionType,
            'timestamp' => now(),
        ], $data));
    }

    /**
     * Main construction method - override in child classes
     */
    abstract public function construct(): void;

    /**
     * Handle module action - override in child classes
     */
    public function handleAction(string $action, array $data): mixed
    {
        $methodName = 'action' . ucfirst($action);

        if (method_exists($this, $methodName)) {
            return $this->$methodName($data);
        }

        return ['error' => 'Action not found'];
    }
}

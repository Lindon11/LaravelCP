<?php

namespace App\Core\Services;

use App\Core\Models\AdminNotification;
use App\Core\Models\User;

class AdminNotificationService
{
    /**
     * Send notification to a specific admin.
     */
    public function notify(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $icon = null,
        ?string $link = null,
        string $priority = AdminNotification::PRIORITY_NORMAL
    ): AdminNotification {
        return AdminNotification::notifyAdmin(
            $userId,
            $type,
            $title,
            $message,
            $data,
            $icon ?? AdminNotification::getDefaultIcon($type),
            $link,
            $priority
        );
    }

    /**
     * Send notification to all admins.
     */
    public function notifyAll(
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $icon = null,
        ?string $link = null,
        string $priority = AdminNotification::PRIORITY_NORMAL
    ): void {
        AdminNotification::notifyAllAdmins(
            $type,
            $title,
            $message,
            $data,
            $icon ?? AdminNotification::getDefaultIcon($type),
            $link,
            $priority
        );
    }

    // ========================================
    // Convenience methods for common notifications
    // ========================================

    /**
     * Notify about a new user registration.
     */
    public function newUserRegistered(User $user): void
    {
        $this->notifyAll(
            AdminNotification::TYPE_USER,
            'New User Registered',
            "A new user '{$user->username}' has registered.",
            ['user_id' => $user->id],
            '👤',
            '/users',
            AdminNotification::PRIORITY_LOW
        );
    }

    /**
     * Notify about a new support ticket.
     */
    public function newTicket(int $ticketId, string $subject, string $username): void
    {
        $this->notifyAll(
            AdminNotification::TYPE_TICKET,
            'New Support Ticket',
            "New ticket from {$username}: {$subject}",
            ['ticket_id' => $ticketId],
            '🎫',
            '/tickets',
            AdminNotification::PRIORITY_NORMAL
        );
    }

    /**
     * Notify about a completed backup.
     */
    public function backupCompleted(string $filename, string $size): void
    {
        $this->notifyAll(
            AdminNotification::TYPE_SUCCESS,
            'Backup Completed',
            "Database backup completed successfully. File: {$filename} ({$size})",
            ['filename' => $filename, 'size' => $size],
            '💾',
            null,
            AdminNotification::PRIORITY_LOW
        );
    }

    /**
     * Notify about a system error.
     */
    public function systemError(string $errorMessage, ?string $context = null): void
    {
        $this->notifyAll(
            AdminNotification::TYPE_ERROR,
            'System Error',
            $errorMessage,
            ['context' => $context],
            '❌',
            '/error-logs',
            AdminNotification::PRIORITY_HIGH
        );
    }

    /**
     * Notify about cache cleared.
     */
    public function cacheCleared(int $adminId): void
    {
        $this->notify(
            $adminId,
            AdminNotification::TYPE_SUCCESS,
            'Cache Cleared',
            'Application cache has been cleared successfully.',
            [],
            '🧹',
            null,
            AdminNotification::PRIORITY_LOW
        );
    }

    /**
     * Notify about module installed.
     */
    public function moduleInstalled(string $moduleName): void
    {
        $this->notifyAll(
            AdminNotification::TYPE_SUCCESS,
            'Module Installed',
            "The module '{$moduleName}' has been installed successfully.",
            ['module' => $moduleName],
            '📦',
            '/module-settings',
            AdminNotification::PRIORITY_NORMAL
        );
    }

    /**
     * Notify about suspicious activity.
     */
    public function suspiciousActivity(string $description, array $data = []): void
    {
        $this->notifyAll(
            AdminNotification::TYPE_WARNING,
            'Suspicious Activity Detected',
            $description,
            $data,
            '⚠️',
            '/users',
            AdminNotification::PRIORITY_HIGH
        );
    }

    /**
     * Notify about scheduled task completion.
     */
    public function taskCompleted(string $taskName, ?string $result = null): void
    {
        $this->notifyAll(
            AdminNotification::TYPE_TASK,
            'Scheduled Task Completed',
            "Task '{$taskName}' completed successfully." . ($result ? " Result: {$result}" : ''),
            ['task' => $taskName, 'result' => $result],
            '✅',
            null,
            AdminNotification::PRIORITY_LOW
        );
    }

    /**
     * Notify about user report.
     */
    public function userReport(string $reporterName, string $reportedName, string $reason): void
    {
        $this->notifyAll(
            AdminNotification::TYPE_REPORT,
            'New User Report',
            "{$reporterName} reported {$reportedName}: {$reason}",
            ['reporter' => $reporterName, 'reported' => $reportedName],
            '🚩',
            '/users',
            AdminNotification::PRIORITY_NORMAL
        );
    }

    /**
     * Notify about economy alert (e.g., inflation).
     */
    public function economyAlert(string $message, array $stats = []): void
    {
        $this->notifyAll(
            AdminNotification::TYPE_WARNING,
            'Economy Alert',
            $message,
            $stats,
            '💰',
            '/dashboard',
            AdminNotification::PRIORITY_HIGH
        );
    }

    /**
     * Notify about lottery draw.
     */
    public function lotteryDrawn(string $lotteryName, ?string $winnerName, int $prize): void
    {
        $this->notifyAll(
            AdminNotification::TYPE_INFO,
            'Lottery Drawn',
            $winnerName
                ? "'{$lotteryName}' drawn. Winner: {$winnerName} ($" . number_format($prize) . ")"
                : "'{$lotteryName}' drawn. No winner this round.",
            ['lottery' => $lotteryName, 'winner' => $winnerName, 'prize' => $prize],
            '🎰',
            '/lotteries',
            AdminNotification::PRIORITY_LOW
        );
    }
}

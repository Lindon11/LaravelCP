<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'subject',
        'body_html',
        'body_text',
        'available_variables',
        'description',
        'is_active',
    ];

    protected $casts = [
        'available_variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get template by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->where('is_active', true)->first();
    }

    /**
     * Render the template with given variables
     */
    public function render(array $variables = []): array
    {
        $subject = $this->subject;
        $bodyHtml = $this->body_html;
        $bodyText = $this->body_text;

        // Replace variables in subject and body
        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $bodyHtml = str_replace($placeholder, $value, $bodyHtml);
            if ($bodyText) {
                $bodyText = str_replace($placeholder, $value, $bodyText);
            }
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
        ];
    }

    /**
     * Get default templates for seeding
     */
    public static function getDefaults(): array
    {
        return [
            [
                'slug' => 'welcome',
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{app_name}}, {{username}}! 🎲',
                'description' => 'Sent when a new user registers',
                'available_variables' => ['app_name', 'username', 'email', 'login_url'],
                'body_html' => self::getDefaultWelcomeTemplate(),
                'body_text' => "Welcome to {{app_name}}, {{username}}!\n\nYour account has been created successfully.\n\nStart playing now: {{login_url}}",
            ],
            [
                'slug' => 'password-reset',
                'name' => 'Password Reset',
                'subject' => 'Reset Your Password - {{app_name}}',
                'description' => 'Sent when a user requests a password reset',
                'available_variables' => ['app_name', 'username', 'email', 'reset_url', 'expiry_minutes'],
                'body_html' => self::getDefaultPasswordResetTemplate(),
                'body_text' => "Hi {{username}},\n\nYou requested to reset your password.\n\nReset your password: {{reset_url}}\n\nThis link expires in {{expiry_minutes}} minutes.\n\nIf you didn't request this, ignore this email.",
            ],
            [
                'slug' => 'password-changed',
                'name' => 'Password Changed',
                'subject' => 'Your Password Has Been Changed - {{app_name}}',
                'description' => 'Sent when a user changes their password',
                'available_variables' => ['app_name', 'username', 'email', 'changed_at', 'ip_address'],
                'body_html' => self::getPasswordChangedTemplate(),
                'body_text' => "Hi {{username}},\n\nYour password was changed on {{changed_at}}.\n\nIf you did not make this change, please contact support immediately.",
            ],
            [
                'slug' => 'account-banned',
                'name' => 'Account Banned',
                'subject' => 'Your Account Has Been Suspended - {{app_name}}',
                'description' => 'Sent when a user account is banned',
                'available_variables' => ['app_name', 'username', 'email', 'reason', 'ban_expires', 'support_url'],
                'body_html' => self::getAccountBannedTemplate(),
                'body_text' => "Hi {{username}},\n\nYour account has been suspended.\n\nReason: {{reason}}\n\nIf you believe this is an error, please contact support.",
            ],
            [
                'slug' => 'account-unbanned',
                'name' => 'Account Unbanned',
                'subject' => 'Your Account Has Been Reinstated - {{app_name}}',
                'description' => 'Sent when a user account ban is lifted',
                'available_variables' => ['app_name', 'username', 'email', 'login_url'],
                'body_html' => self::getAccountUnbannedTemplate(),
                'body_text' => "Hi {{username}},\n\nGreat news! Your account has been reinstated.\n\nYou can now log in and continue playing: {{login_url}}",
            ],
            [
                'slug' => 'ticket-reply',
                'name' => 'Support Ticket Reply',
                'subject' => 'Reply to Your Support Ticket #{{ticket_id}} - {{app_name}}',
                'description' => 'Sent when staff replies to a support ticket',
                'available_variables' => ['app_name', 'username', 'ticket_id', 'ticket_subject', 'reply_preview', 'ticket_url'],
                'body_html' => self::getTicketReplyTemplate(),
                'body_text' => "Hi {{username}},\n\nYou have a new reply to your support ticket #{{ticket_id}}.\n\nSubject: {{ticket_subject}}\n\nView the full response: {{ticket_url}}",
            ],
        ];
    }

    private static function getBaseTemplate(string $headerEmoji, string $headerTitle, string $content): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #1a1a2e; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #1a1a2e; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #16213e; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #000; font-size: 28px;">{$headerEmoji} {$headerTitle}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            {$content}
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #0f172a; padding: 20px 30px; text-align: center;">
                            <p style="color: #666; font-size: 12px; margin: 0;">© {{app_name}}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private static function getDefaultWelcomeTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #1a1a2e; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #1a1a2e; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #16213e; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #000; font-size: 28px;">🎲 Welcome to {{app_name}}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #f59e0b; margin: 0 0 20px 0; font-size: 24px;">Hey {{username}}! 👋</h2>
                            <p style="color: #e0e0e0; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Your account has been created successfully. You're now ready to build your criminal empire!
                            </p>
                            <p style="color: #e0e0e0; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                <strong style="color: #f59e0b;">Getting Started:</strong>
                            </p>
                            <ul style="color: #b0b0b0; font-size: 14px; line-height: 2; margin: 0 0 30px 0; padding-left: 20px;">
                                <li>💰 Commit crimes to earn cash and experience</li>
                                <li>💪 Train at the gym to boost your stats</li>
                                <li>🏦 Use the bank to keep your money safe</li>
                                <li>👥 Join or create a gang with friends</li>
                                <li>🏆 Climb the ranks from Thug to Godfather</li>
                            </ul>
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                <tr>
                                    <td style="background-color: #f59e0b; border-radius: 6px;">
                                        <a href="{{login_url}}" style="display: inline-block; padding: 14px 30px; color: #000; text-decoration: none; font-weight: bold; font-size: 16px;">🎮 Start Playing Now</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="color: #888; font-size: 13px; line-height: 1.6; margin: 30px 0 0 0; text-align: center;">
                                Good luck out there, and remember: trust no one! 🔫
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #0f172a; padding: 20px 30px; text-align: center;">
                            <p style="color: #666; font-size: 12px; margin: 0;">© {{app_name}}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private static function getDefaultPasswordResetTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #1a1a2e; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #1a1a2e; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #16213e; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #000; font-size: 28px;">🔐 Password Reset</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #f59e0b; margin: 0 0 20px 0; font-size: 24px;">Hi {{username}},</h2>
                            <p style="color: #e0e0e0; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                You recently requested to reset your password. Click the button below to proceed:
                            </p>
                            <table cellpadding="0" cellspacing="0" style="margin: 30px auto;">
                                <tr>
                                    <td style="background-color: #f59e0b; border-radius: 6px;">
                                        <a href="{{reset_url}}" style="display: inline-block; padding: 14px 30px; color: #000; text-decoration: none; font-weight: bold; font-size: 16px;">Reset Password</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="color: #b0b0b0; font-size: 14px; line-height: 1.6; margin: 20px 0;">
                                ⏰ This link will expire in <strong style="color: #f59e0b;">{{expiry_minutes}} minutes</strong>.
                            </p>
                            <p style="color: #888; font-size: 13px; line-height: 1.6; margin: 20px 0 0 0; padding-top: 20px; border-top: 1px solid #2d3748;">
                                If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #0f172a; padding: 20px 30px; text-align: center;">
                            <p style="color: #666; font-size: 12px; margin: 0;">© {{app_name}}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private static function getPasswordChangedTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #1a1a2e; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #1a1a2e; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #16213e; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #fff; font-size: 28px;">✅ Password Changed</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #10b981; margin: 0 0 20px 0; font-size: 24px;">Hi {{username}},</h2>
                            <p style="color: #e0e0e0; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Your password was successfully changed on <strong>{{changed_at}}</strong>.
                            </p>
                            <div style="background-color: #1e293b; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="color: #fbbf24; font-size: 14px; margin: 0;">
                                    ⚠️ <strong>Didn't make this change?</strong><br>
                                    <span style="color: #b0b0b0;">If you did not change your password, your account may be compromised. Please contact support immediately.</span>
                                </p>
                            </div>
                            <p style="color: #888; font-size: 13px; line-height: 1.6; margin: 20px 0 0 0;">
                                IP Address: {{ip_address}}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #0f172a; padding: 20px 30px; text-align: center;">
                            <p style="color: #666; font-size: 12px; margin: 0;">© {{app_name}}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private static function getAccountBannedTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #1a1a2e; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #1a1a2e; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #16213e; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #fff; font-size: 28px;">🚫 Account Suspended</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #ef4444; margin: 0 0 20px 0; font-size: 24px;">Hi {{username}},</h2>
                            <p style="color: #e0e0e0; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Your account has been suspended due to a violation of our terms of service.
                            </p>
                            <div style="background-color: #1e293b; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="color: #ef4444; font-size: 14px; margin: 0 0 10px 0;"><strong>Reason:</strong></p>
                                <p style="color: #b0b0b0; font-size: 14px; margin: 0;">{{reason}}</p>
                            </div>
                            <p style="color: #b0b0b0; font-size: 14px; line-height: 1.6; margin: 20px 0;">
                                <strong>Ban expires:</strong> {{ban_expires}}
                            </p>
                            <p style="color: #888; font-size: 13px; line-height: 1.6; margin: 20px 0 0 0; padding-top: 20px; border-top: 1px solid #2d3748;">
                                If you believe this was done in error, please contact support at {{support_url}}.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #0f172a; padding: 20px 30px; text-align: center;">
                            <p style="color: #666; font-size: 12px; margin: 0;">© {{app_name}}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private static function getAccountUnbannedTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #1a1a2e; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #1a1a2e; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #16213e; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #fff; font-size: 28px;">🎉 Account Reinstated</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #10b981; margin: 0 0 20px 0; font-size: 24px;">Welcome back, {{username}}!</h2>
                            <p style="color: #e0e0e0; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Great news! Your account suspension has been lifted and you can now access your account again.
                            </p>
                            <p style="color: #b0b0b0; font-size: 14px; line-height: 1.6; margin: 0 0 30px 0;">
                                Please make sure to review our terms of service to avoid future suspensions.
                            </p>
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                <tr>
                                    <td style="background-color: #10b981; border-radius: 6px;">
                                        <a href="{{login_url}}" style="display: inline-block; padding: 14px 30px; color: #fff; text-decoration: none; font-weight: bold; font-size: 16px;">🎮 Return to Game</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #0f172a; padding: 20px 30px; text-align: center;">
                            <p style="color: #666; font-size: 12px; margin: 0;">© {{app_name}}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private static function getTicketReplyTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #1a1a2e; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #1a1a2e; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #16213e; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #fff; font-size: 28px;">💬 Support Ticket Reply</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #3b82f6; margin: 0 0 20px 0; font-size: 24px;">Hi {{username}},</h2>
                            <p style="color: #e0e0e0; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                You have a new reply to your support ticket.
                            </p>
                            <div style="background-color: #1e293b; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="color: #3b82f6; font-size: 12px; margin: 0 0 5px 0; text-transform: uppercase;">Ticket #{{ticket_id}}</p>
                                <p style="color: #e0e0e0; font-size: 16px; margin: 0; font-weight: bold;">{{ticket_subject}}</p>
                            </div>
                            <p style="color: #b0b0b0; font-size: 14px; line-height: 1.6; margin: 20px 0; font-style: italic;">
                                "{{reply_preview}}..."
                            </p>
                            <table cellpadding="0" cellspacing="0" style="margin: 30px auto 0;">
                                <tr>
                                    <td style="background-color: #3b82f6; border-radius: 6px;">
                                        <a href="{{ticket_url}}" style="display: inline-block; padding: 14px 30px; color: #fff; text-decoration: none; font-weight: bold; font-size: 16px;">View Full Response</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #0f172a; padding: 20px 30px; text-align: center;">
                            <p style="color: #666; font-size: 12px; margin: 0;">© {{app_name}}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}

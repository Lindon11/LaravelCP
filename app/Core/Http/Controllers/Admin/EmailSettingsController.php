<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\EmailSetting;
use App\Core\Models\EmailTemplate;
use App\Mail\DynamicEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailSettingsController extends Controller
{
    /**
     * Get current email settings
     */
    public function getSettings(): JsonResponse
    {
        $settings = EmailSetting::first();

        if (!$settings) {
            $settings = new EmailSetting([
                'mailer' => 'mailgun',
                'port' => 587,
                'encryption' => 'tls',
                'mailgun_endpoint' => 'api.mailgun.net',
                'is_active' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $settings->id,
                'mailer' => $settings->mailer,
                'host' => $settings->host,
                'port' => $settings->port,
                'username' => $settings->username,
                'has_password' => !empty($settings->getAttributes()['password']),
                'encryption' => $settings->encryption,
                'from_address' => $settings->from_address,
                'from_name' => $settings->from_name,
                'mailgun_domain' => $settings->mailgun_domain,
                'has_mailgun_secret' => !empty($settings->getAttributes()['mailgun_secret']),
                'mailgun_endpoint' => $settings->mailgun_endpoint,
                'is_active' => $settings->is_active,
                'last_tested_at' => $settings->last_tested_at,
                'test_successful' => $settings->test_successful,
            ],
        ]);
    }

    /**
     * Update email settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mailer' => 'required|in:smtp,mailgun',
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'nullable|in:tls,ssl,null',
            'from_address' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'mailgun_domain' => 'nullable|string|max:255',
            'mailgun_secret' => 'nullable|string|max:255',
            'mailgun_endpoint' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $settings = EmailSetting::first();

        if (!$settings) {
            $settings = new EmailSetting();
        }

        // Only update password fields if provided (not empty)
        $updateData = collect($validated)->except(['password', 'mailgun_secret'])->toArray();

        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        if (!empty($validated['mailgun_secret'])) {
            $updateData['mailgun_secret'] = $validated['mailgun_secret'];
        }

        $settings->fill($updateData);
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Email settings updated successfully',
        ]);
    }

    /**
     * Test email settings
     */
    public function testSettings(Request $request): JsonResponse
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $settings = EmailSetting::first();

        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Please save email settings first',
            ], 400);
        }

        try {
            // Apply settings
            $settings->applyToConfig();

            // Send test email
            Mail::raw('This is a test email from ' . config('app.name') . '. If you received this, your email settings are working correctly!', function ($message) use ($request, $settings) {
                $message->to($request->test_email)
                    ->subject('Test Email - ' . config('app.name'))
                    ->from($settings->from_address, $settings->from_name);
            });

            // Update test status
            $settings->update([
                'last_tested_at' => now(),
                'test_successful' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $request->test_email,
            ]);
        } catch (\Exception $e) {
            // Update test status
            $settings->update([
                'last_tested_at' => now(),
                'test_successful' => false,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all email templates
     */
    public function getTemplates(): JsonResponse
    {
        $templates = EmailTemplate::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Get a single email template
     */
    public function getTemplate(int $id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $template,
        ]);
    }

    /**
     * Create a new email template
     */
    public function createTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:email_templates,slug',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'available_variables' => 'nullable|array',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $template = EmailTemplate::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Email template created successfully',
            'data' => $template,
        ], 201);
    }

    /**
     * Update an email template
     */
    public function updateTemplate(Request $request, int $id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:email_templates,slug,' . $id,
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'available_variables' => 'nullable|array',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Email template updated successfully',
            'data' => $template,
        ]);
    }

    /**
     * Delete an email template
     */
    public function deleteTemplate(int $id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email template deleted successfully',
        ]);
    }

    /**
     * Preview an email template
     */
    public function previewTemplate(Request $request, int $id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);

        // Generate sample data for preview
        $sampleData = [
            'app_name' => config('app.name'),
            'username' => 'SampleUser',
            'email' => 'sample@example.com',
            'login_url' => config('app.frontend_url', config('app.url')) . '/login',
            'reset_url' => config('app.frontend_url', config('app.url')) . '/reset-password?token=sample-token',
            'expiry_minutes' => '60',
        ];

        $rendered = $template->render($sampleData);

        return response()->json([
            'success' => true,
            'data' => $rendered,
        ]);
    }

    /**
     * Send a test email using a template
     */
    public function sendTestTemplate(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $template = EmailTemplate::findOrFail($id);
        $settings = EmailSetting::getActive();

        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Please configure and activate email settings first',
            ], 400);
        }

        try {
            $settings->applyToConfig();

            // Sample variables for testing
            $variables = [
                'app_name' => config('app.name'),
                'username' => 'TestUser',
                'email' => $request->test_email,
                'login_url' => config('app.frontend_url', config('app.url')) . '/login',
                'reset_url' => config('app.frontend_url', config('app.url')) . '/reset-password?token=test-token',
                'expiry_minutes' => '60',
            ];

            Mail::to($request->test_email)->send(new DynamicEmail($template->slug, $variables));

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Seed default templates
     */
    public function seedDefaultTemplates(): JsonResponse
    {
        $defaults = EmailTemplate::getDefaults();
        $created = 0;

        foreach ($defaults as $templateData) {
            if (!EmailTemplate::where('slug', $templateData['slug'])->exists()) {
                EmailTemplate::create($templateData);
                $created++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => $created > 0
                ? "Created {$created} default template(s)"
                : 'All default templates already exist',
        ]);
    }

    /**
     * Send a custom/manual email
     */
    public function sendManualEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_html' => 'boolean',
        ]);

        $settings = EmailSetting::getActive();

        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Please configure and activate email settings first',
            ], 400);
        }

        try {
            $settings->applyToConfig();

            $isHtml = $validated['is_html'] ?? true;

            Mail::send([], [], function ($message) use ($validated, $settings, $isHtml) {
                $message->to($validated['to'])
                    ->from($settings->from_address, $settings->from_name)
                    ->subject($validated['subject']);

                if ($isHtml) {
                    $message->html($validated['body']);
                } else {
                    $message->text($validated['body']);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully to ' . $validated['to'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }
}

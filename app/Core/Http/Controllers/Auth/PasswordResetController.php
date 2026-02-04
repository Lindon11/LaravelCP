<?php

namespace App\Core\Http\Controllers\Auth;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\EmailSetting;
use App\Core\Models\User;
use App\Mail\DynamicEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Send a password reset link to the given user.
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Don't reveal if user exists or not for security
            return response()->json([
                'success' => true,
                'message' => 'If an account with that email exists, you will receive a password reset link shortly.',
            ]);
        }

        // Check if email is configured
        $emailSettings = EmailSetting::getActive();
        if (!$emailSettings) {
            return response()->json([
                'success' => false,
                'message' => 'Email system is not configured. Please contact support.',
            ], 500);
        }

        // Delete any existing tokens for this user
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Create new token
        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Build reset URL - frontend will handle this
        $resetUrl = config('app.frontend_url', config('app.url')) . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);

        // Send email using dynamic template
        try {
            $emailSettings->applyToConfig();

            Mail::to($user)->send(new DynamicEmail('password-reset', [
                'app_name' => config('app.name'),
                'username' => $user->username,
                'email' => $user->email,
                'reset_url' => $resetUrl,
                'expiry_minutes' => '60',
            ]));
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset email. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'If an account with that email exists, you will receive a password reset link shortly.',
        ]);
    }

    /**
     * Validate the password reset token.
     */
    public function validateToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired password reset token.',
            ], 400);
        }

        // Check if token matches
        if (!Hash::check($request->token, $record->token)) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired password reset token.',
            ], 400);
        }

        // Check if token is expired (60 minutes)
        $tokenCreatedAt = \Carbon\Carbon::parse($record->created_at);
        if ($tokenCreatedAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'valid' => false,
                'message' => 'Password reset token has expired. Please request a new one.',
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Token is valid.',
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired password reset token.',
            ], 400);
        }

        // Check if token matches
        if (!Hash::check($request->token, $record->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired password reset token.',
            ], 400);
        }

        // Check if token is expired (60 minutes)
        $tokenCreatedAt = \Carbon\Carbon::parse($record->created_at);
        if ($tokenCreatedAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Password reset token has expired. Please request a new one.',
            ], 400);
        }

        // Find and update user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully. You can now log in with your new password.',
        ]);
    }
}

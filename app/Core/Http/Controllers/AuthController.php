<?php

namespace App\Core\Http\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\EmailSetting;
use App\Core\Models\User;
use App\Mail\DynamicEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['username'], // Use username as display name
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rank_id' => 1, // Default rank: Thug
            'location_id' => 1, // Default location: Detroit
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 1000,
            'bank' => 0,
            'bullets' => 50,
            'experience' => 0,
            'level' => 1,
        ]);

        // Assign default player role
        if (\Spatie\Permission\Models\Role::where('name', 'user')->exists()) {
            $user->assignRole('user');
        }

        // Send welcome email using dynamic template
        try {
            $emailSettings = EmailSetting::getActive();
            if ($emailSettings) {
                $emailSettings->applyToConfig();

                Mail::to($user)->send(new DynamicEmail('welcome', [
                    'app_name' => config('app.name'),
                    'username' => $user->username,
                    'email' => $user->email,
                    'login_url' => config('app.frontend_url', config('app.url')) . '/login',
                ]));
            }
        } catch (\Exception $e) {
            // Log but don't fail registration if email fails
            \Log::warning('Failed to send welcome email: ' . $e->getMessage());
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('currentRank', 'location'),
            'token' => $token,
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // Can be email or username
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->login)
            ->orWhere('username', $request->login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Update last login timestamp and IP
        $user->update([
            'last_active' => now(),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('currentRank', 'location', 'roles'),
            'token' => $token,
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user()->load('currentRank', 'location', 'roles', 'permissions');

        return response()->json($user); // Return user directly for frontend compatibility
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices',
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required_without:force_change|string',
            'new_password' => 'required|string|min:8|confirmed',
            'force_change' => 'boolean'
        ]);

        $user = $request->user();

        // If not a forced change, verify current password
        if (!($validated['force_change'] ?? false) || !$user->force_password_change) {
            if (!isset($validated['current_password']) || !Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['The current password is incorrect.'],
                ]);
            }
        }

        // Update password and remove force flag
        $user->update([
            'password' => Hash::make($validated['new_password']),
            'force_password_change' => false,
        ]);

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }
}


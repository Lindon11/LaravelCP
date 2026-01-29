// Temporary debug route - paste this into routes/web.php

Route::get('/debug-roles', function () {
    $user = auth()->user();
    return response()->json([
        'user_email' => $user->email,
        'roles' => $user->getRoleNames()->toArray(),
        'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        'has_admin_role' => $user->hasRole('admin'),
    ]);
})->middleware('auth');

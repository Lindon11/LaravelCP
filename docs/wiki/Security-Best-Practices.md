# Security Best Practices

Security guidelines for LaravelCP development and deployment.

---

## Overview

Security is critical for any web application, especially games handling user accounts and virtual currencies. This guide covers essential security practices.

---

## Authentication Security

### Password Requirements

```php
// In validation
'password' => [
    'required',
    'min:8',
    'confirmed',
    Password::min(8)
        ->letters()
        ->mixedCase()
        ->numbers()
        ->uncompromised(),
]
```

### Rate Limiting

LaravelCP includes rate limiting on sensitive endpoints:

```php
// In RouteServiceProvider or routes
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
});
```

### API Token Security

```php
// Tokens expire after set period
$token = $user->createToken('auth-token', ['*'], now()->addDays(7));

// Revoke tokens on logout
$user->tokens()->delete();

// Revoke specific token
$user->currentAccessToken()->delete();
```

---

## Input Validation

### Always Validate Input

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'amount' => 'required|integer|min:1|max:1000000',
        'recipient_id' => 'required|exists:users,id',
        'message' => 'nullable|string|max:500',
    ]);
    
    // Use $validated, not $request->all()
    return $this->service->transfer($validated);
}
```

### Custom Validation Rules

```php
// Prevent self-transfer
'recipient_id' => [
    'required',
    'exists:users,id',
    Rule::notIn([auth()->id()]),
]

// Ensure user has enough funds
'amount' => [
    'required',
    'integer',
    'min:1',
    function ($attribute, $value, $fail) {
        if ($value > auth()->user()->bank) {
            $fail('Insufficient funds.');
        }
    },
]
```

---

## SQL Injection Prevention

### Use Eloquent/Query Builder

```php
// ✅ Safe - parameterized
User::where('email', $email)->first();
User::where('id', $id)->update(['name' => $name]);

// ✅ Safe - query builder
DB::table('users')->where('email', '=', $email)->first();

// ❌ UNSAFE - raw queries with user input
DB::select("SELECT * FROM users WHERE email = '$email'");
```

### Raw Queries When Necessary

```php
// ✅ Use bindings
DB::select('SELECT * FROM users WHERE email = ?', [$email]);

// ✅ Named bindings
DB::select('SELECT * FROM users WHERE email = :email', ['email' => $email]);
```

---

## XSS Prevention

### Blade Escaping

```blade
{{-- ✅ Escaped output (default) --}}
{{ $user->bio }}

{{-- ❌ Unescaped - use ONLY for trusted HTML --}}
{!! $trustedHtml !!}
```

### API Response Sanitization

```php
// Sanitize user-generated content
use HTMLPurifier;

public function getFormattedBio(): string
{
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($this->bio);
}
```

---

## CSRF Protection

### API Tokens

Sanctum API tokens provide CSRF protection by design. For stateful SPA:

```php
// Ensure CSRF cookie is set
Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
});
```

### Web Routes

```blade
{{-- Include CSRF token in forms --}}
<form method="POST">
    @csrf
    <!-- form fields -->
</form>
```

---

## Authorization

### Policy-Based Authorization

```php
// Define policy
class UserItemPolicy
{
    public function use(User $user, UserItem $item): bool
    {
        return $user->id === $item->user_id;
    }
    
    public function delete(User $user, UserItem $item): bool
    {
        return $user->id === $item->user_id;
    }
}

// Use in controller
public function use(UserItem $item)
{
    $this->authorize('use', $item);
    
    // Proceed with action
}
```

### Role-Based Access Control

```php
// Check permission
if ($user->hasPermissionTo('manage users')) {
    // Admin action
}

// Middleware
Route::middleware(['permission:manage users'])->group(function () {
    Route::get('/admin/users', [AdminUserController::class, 'index']);
});

// In controller
public function __construct()
{
    $this->middleware('permission:manage users');
}
```

---

## Secure Configuration

### Environment Security

```env
# Production settings
APP_ENV=production
APP_DEBUG=false

# Strong session security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

### Hide Sensitive Information

```php
// config/app.php
'debug_blacklist' => [
    '_ENV' => [
        'APP_KEY',
        'DB_PASSWORD',
        'MAIL_PASSWORD',
        'REDIS_PASSWORD',
    ],
    '_SERVER' => [
        'APP_KEY',
        'DB_PASSWORD',
    ],
],
```

---

## Data Protection

### Encrypt Sensitive Data

```php
use Illuminate\Support\Facades\Crypt;

// Encrypt
$encrypted = Crypt::encryptString($sensitiveData);

// Decrypt
$decrypted = Crypt::decryptString($encrypted);

// Model attribute encryption
protected $casts = [
    'api_secret' => 'encrypted',
];
```

### Hash Passwords

```php
use Illuminate\Support\Facades\Hash;

// Never store plain text passwords
$user->password = Hash::make($password);

// Verify password
if (Hash::check($input, $user->password)) {
    // Password matches
}
```

---

## Security Headers

### Nginx Configuration

```nginx
# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';" always;
```

### Middleware

```php
// app/Http/Middleware/SecurityHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    
    return $response;
}
```

---

## Logging & Monitoring

### Log Security Events

```php
use Illuminate\Support\Facades\Log;

// Log failed login attempts
Log::warning('Failed login attempt', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);

// Log suspicious activity
Log::alert('Suspicious activity detected', [
    'user_id' => $user->id,
    'action' => 'multiple_failed_transfers',
    'ip' => $request->ip(),
]);
```

### Error Logging

```php
// Use the ErrorLogService
app(ErrorLogService::class)->log(
    exception: $e,
    context: ['user_id' => auth()->id()],
    severity: 'error'
);
```

---

## File Upload Security

### Validate Uploads

```php
'avatar' => [
    'required',
    'image',
    'mimes:jpeg,png,gif',
    'max:2048', // 2MB
    'dimensions:max_width=1000,max_height=1000',
],
```

### Store Safely

```php
// Store with random name
$path = $request->file('avatar')->store('avatars', 'public');

// Never use original filename
// $request->file('avatar')->storeAs('avatars', $request->file('avatar')->getClientOriginalName());
```

---

## API Security

### API Key Validation

```php
public function handle(Request $request, Closure $next)
{
    $apiKey = $request->header('X-API-Key');
    
    if (!$apiKey || !$this->isValidApiKey($apiKey)) {
        return response()->json(['error' => 'Invalid API key'], 401);
    }
    
    return $next($request);
}
```

### IP Whitelisting

```php
// For sensitive admin endpoints
protected $whitelist = [
    '192.168.1.1',
    '10.0.0.1',
];

public function handle(Request $request, Closure $next)
{
    if (!in_array($request->ip(), $this->whitelist)) {
        abort(403, 'IP not authorized');
    }
    
    return $next($request);
}
```

---

## Database Security

### Principle of Least Privilege

```sql
-- Create application user with limited permissions
CREATE USER 'laravelcp'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON laravelcp.* TO 'laravelcp'@'localhost';
FLUSH PRIVILEGES;

-- Separate user for migrations (don't use in production app)
CREATE USER 'laravelcp_admin'@'localhost' IDENTIFIED BY 'admin_password';
GRANT ALL PRIVILEGES ON laravelcp.* TO 'laravelcp_admin'@'localhost';
```

### Backup Encryption

```bash
# Encrypt database backups
mysqldump -u user -p database | gpg -c > backup.sql.gpg

# Decrypt
gpg -d backup.sql.gpg | mysql -u user -p database
```

---

## Dependency Security

### Keep Dependencies Updated

```bash
# Check for vulnerabilities
composer audit

# Update dependencies
composer update

# Check npm packages
npm audit
npm audit fix
```

### Lock Dependencies

Always commit `composer.lock` and `package-lock.json` to ensure consistent deployments.

---

## Security Checklist

### Before Deployment

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials are secure
- [ ] HTTPS enabled
- [ ] Security headers configured
- [ ] Rate limiting enabled
- [ ] File permissions set correctly
- [ ] Dependencies audited

### Regular Maintenance

- [ ] Review error logs
- [ ] Monitor failed login attempts
- [ ] Update dependencies monthly
- [ ] Rotate API keys periodically
- [ ] Review user permissions
- [ ] Test backup restoration

---

## Next Steps

- [Production Deployment](Production-Deployment) - Secure deployment
- [Environment Variables](Environment-Variables) - Configuration
- [Troubleshooting](Troubleshooting) - Common issues

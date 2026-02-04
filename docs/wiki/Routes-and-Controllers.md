# Routes and Controllers

Understanding the routing system and controller patterns in LaravelCP.

---

## Route Organization

Routes are organized across multiple files:

```
routes/
├── api.php       # Main API routes
├── web.php       # Web/blade routes
└── console.php   # Artisan console routes
```

Plugins can also define their own routes:

```
app/Plugins/YourPlugin/routes/
├── api.php       # Plugin API routes
├── web.php       # Plugin web routes
└── admin.php     # Plugin admin routes
```

---

## Main API Routes (`routes/api.php`)

### Structure Overview

```php
<?php

use Illuminate\Support\Facades\Route;

// ============================================
// PUBLIC ROUTES (Rate Limited)
// ============================================
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
});

// ============================================
// FRONTEND ERROR LOGGING (Rate Limited)
// ============================================
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/log-frontend-error', [FrontendErrorController::class, 'log']);
});

// ============================================
// AUTHENTICATED ROUTES
// ============================================
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Feature routes...
    
    // ========================================
    // ADMIN ROUTES
    // ========================================
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin|moderator')
        ->group(function () {
            // Admin endpoints...
        });
});
```

### Route Patterns

#### RESTful API Resources

```php
// Full CRUD resource
Route::apiResource('crimes', CrimeController::class);

// Generates:
// GET    /api/crimes           index()
// POST   /api/crimes           store()
// GET    /api/crimes/{id}      show()
// PUT    /api/crimes/{id}      update()
// DELETE /api/crimes/{id}      destroy()
```

#### Grouped Routes

```php
Route::prefix('users')
    ->controller(UserController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::patch('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::post('/{id}/ban', 'ban');
        Route::post('/{id}/unban', 'unban');
    });
```

#### Named Routes

```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');
    
// Use in code:
$url = route('dashboard');
```

---

## Admin Routes Reference

### Dashboard & Statistics

```php
GET  /api/admin/stats              // Dashboard statistics
```

### User Management

```php
GET    /api/admin/users                 // List users (paginated)
GET    /api/admin/users/statistics      // User statistics
POST   /api/admin/users                 // Create user
GET    /api/admin/users/{id}            // Get user details
PATCH  /api/admin/users/{id}            // Update user
DELETE /api/admin/users/{id}            // Delete user
POST   /api/admin/users/{id}/ban        // Ban user
POST   /api/admin/users/{id}/unban      // Unban user
```

### Roles & Permissions

```php
GET    /api/admin/roles                 // List roles
POST   /api/admin/roles                 // Create role
PATCH  /api/admin/roles/{id}            // Update role
DELETE /api/admin/roles/{id}            // Delete role
GET    /api/admin/permissions           // List permissions
POST   /api/admin/users/{id}/roles      // Assign role
DELETE /api/admin/users/{id}/roles      // Remove role
```

### Plugin Management

```php
GET    /api/admin/plugins               // List all plugins
POST   /api/admin/plugins/upload        // Upload plugin ZIP
POST   /api/admin/plugins/create        // Create new plugin
POST   /api/admin/plugins/{slug}/install    // Install plugin
PUT    /api/admin/plugins/{slug}/enable     // Enable plugin
PUT    /api/admin/plugins/{slug}/disable    // Disable plugin
DELETE /api/admin/plugins/{slug}            // Uninstall plugin
```

### Settings

```php
GET   /api/admin/settings               // Get all settings
POST  /api/admin/settings               // Create setting
PATCH /api/admin/settings               // Update settings
GET   /api/admin/settings/{key}         // Get specific setting
DELETE /api/admin/settings/{key}        // Delete setting
```

### Error Logging

```php
GET    /api/admin/error-logs                    // List error logs
GET    /api/admin/error-logs/statistics         // Error statistics
GET    /api/admin/error-logs/{id}               // Get error details
PATCH  /api/admin/error-logs/{id}/resolve       // Mark resolved
PATCH  /api/admin/error-logs/{id}/unresolve     // Mark unresolved
DELETE /api/admin/error-logs/{id}               // Delete error
DELETE /api/admin/error-logs/clear              // Clear all errors
POST   /api/admin/error-logs/bulk-resolve       // Bulk resolve
POST   /api/admin/error-logs/bulk-delete        // Bulk delete
```

### Webhooks

```php
GET    /api/admin/webhooks                          // List webhooks
GET    /api/admin/webhooks/events                   // Available events
POST   /api/admin/webhooks                          // Create webhook
GET    /api/admin/webhooks/{id}                     // Get webhook
PATCH  /api/admin/webhooks/{id}                     // Update webhook
DELETE /api/admin/webhooks/{id}                     // Delete webhook
POST   /api/admin/webhooks/{id}/toggle              // Toggle enabled
POST   /api/admin/webhooks/{id}/test                // Send test
GET    /api/admin/webhooks/{id}/deliveries          // Delivery history
POST   /api/admin/webhooks/{id}/regenerate-secret   // New secret
```

---

## Creating Controllers

### Basic Controller

```php
<?php

namespace App\Plugins\YourPlugin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class YourController extends Controller
{
    /**
     * List resources
     */
    public function index(Request $request): JsonResponse
    {
        $items = YourModel::query()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy($request->sort ?? 'created_at', $request->order ?? 'desc')
            ->paginate($request->per_page ?? 15);
        
        return response()->json($items);
    }
    
    /**
     * Store new resource
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        
        $item = YourModel::create($validated);
        
        return response()->json([
            'message' => 'Created successfully',
            'data' => $item,
        ], 201);
    }
    
    /**
     * Show resource
     */
    public function show(int $id): JsonResponse
    {
        $item = YourModel::findOrFail($id);
        
        return response()->json(['data' => $item]);
    }
    
    /**
     * Update resource
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $item = YourModel::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        
        $item->update($validated);
        
        return response()->json([
            'message' => 'Updated successfully',
            'data' => $item,
        ]);
    }
    
    /**
     * Delete resource
     */
    public function destroy(int $id): JsonResponse
    {
        $item = YourModel::findOrFail($id);
        $item->delete();
        
        return response()->json([
            'message' => 'Deleted successfully',
        ]);
    }
}
```

### Controller with Dependency Injection

```php
<?php

namespace App\Plugins\YourPlugin\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\YourPlugin\Services\YourService;
use Illuminate\Http\Request;

class YourController extends Controller
{
    public function __construct(
        protected YourService $service
    ) {}
    
    public function performAction(Request $request)
    {
        $user = $request->user();
        
        $result = $this->service->doSomething($user, $request->all());
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 400);
        }
        
        return response()->json($result);
    }
}
```

### Form Request Validation

Create a Form Request for complex validation:

```php
<?php

namespace App\Plugins\YourPlugin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreYourModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:your_table',
            'difficulty' => 'required|integer|min:1|max:10',
            'min_reward' => 'required|integer|min:0',
            'max_reward' => 'required|integer|gte:min_reward',
            'active' => 'boolean',
        ];
    }
    
    public function messages(): array
    {
        return [
            'max_reward.gte' => 'Max reward must be greater than or equal to min reward.',
        ];
    }
}
```

Use in controller:

```php
public function store(StoreYourModelRequest $request): JsonResponse
{
    $item = YourModel::create($request->validated());
    
    return response()->json(['data' => $item], 201);
}
```

---

## Plugin Routes

### Defining Plugin Routes

**`app/Plugins/YourPlugin/routes/api.php`**

```php
<?php

use App\Plugins\YourPlugin\Controllers\YourController;
use Illuminate\Support\Facades\Route;

// All routes here are automatically prefixed and protected
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('your-plugin')->group(function () {
        Route::get('/', [YourController::class, 'index']);
        Route::get('/{id}', [YourController::class, 'show']);
        Route::post('/{id}/action', [YourController::class, 'action']);
    });
});
```

**`app/Plugins/YourPlugin/routes/admin.php`**

```php
<?php

use App\Plugins\YourPlugin\Controllers\YourManagementController;
use Illuminate\Support\Facades\Route;

// Admin routes - automatically includes admin middleware
Route::apiResource('your-items', YourManagementController::class);
Route::get('your-items/statistics', [YourManagementController::class, 'statistics']);
```

### Route Configuration in plugin.json

```json
{
    "routes": {
        "web": false,
        "api": true,
        "admin": true
    }
}
```

---

## Response Formats

### Success Response

```php
return response()->json([
    'message' => 'Operation successful',
    'data' => $data,
]);
```

### Error Response

```php
return response()->json([
    'error' => 'Something went wrong',
    'details' => $errorDetails,
], 400);
```

### Paginated Response

```php
$items = YourModel::paginate(15);

return response()->json($items);

// Returns:
{
    "data": [...],
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150,
    "links": {...}
}
```

### Custom API Resource

```php
<?php

namespace App\Plugins\YourPlugin\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class YourResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'formatted_reward' => '$' . number_format($this->reward),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

// Usage
return YourResource::collection($items);
return new YourResource($item);
```

---

## Middleware

### Available Middleware

| Middleware | Description |
|------------|-------------|
| `auth:sanctum` | Require authentication |
| `role:admin` | Require admin role |
| `role:admin\|moderator` | Require admin OR moderator |
| `permission:manage-users` | Require specific permission |
| `throttle:10,1` | Rate limit 10 req/min |
| `verified` | Require email verification |

### Custom Middleware

```php
<?php

namespace App\Plugins\YourPlugin\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPluginAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user || $user->level < 5) {
            return response()->json([
                'error' => 'Level 5 required to access this feature',
            ], 403);
        }
        
        if ($user->isJailed()) {
            return response()->json([
                'error' => 'Cannot access while in jail',
            ], 403);
        }
        
        return $next($request);
    }
}
```

Register in plugin's service provider:

```php
public function boot()
{
    $this->app['router']->aliasMiddleware(
        'your-plugin.access', 
        CheckPluginAccess::class
    );
}
```

Use in routes:

```php
Route::middleware(['auth:sanctum', 'your-plugin.access'])->group(function () {
    // Protected routes
});
```

---

## Testing Routes

### List All Routes

```bash
php artisan route:list
php artisan route:list --path=api/admin
php artisan route:list | grep your-plugin
```

### Test with cURL

```bash
# Login
curl -X POST http://localhost:8001/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}'

# Use token
curl -X GET http://localhost:8001/api/user \
  -H "Authorization: Bearer YOUR_TOKEN"

# Admin endpoint
curl -X GET http://localhost:8001/api/admin/stats \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Next Steps

- [Models & Database](Models-and-Database) - Data layer
- [Services](Services) - Business logic
- [Authentication API](Authentication-API) - Auth endpoints

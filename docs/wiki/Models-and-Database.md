# Models and Database

Guide to database design and Eloquent models in LaravelCP.

---

## Database Structure

LaravelCP uses MySQL with a modular database design. Each plugin can have its own tables.

### Core Tables

| Table | Description |
| ------- |-------------|
| `users` | Player accounts |
| `settings` | Game settings |
| `locations` | Game locations |
| `ranks` | Player ranks |
| `memberships` | VIP memberships |
| `items` | Base items |
| `notifications` | User notifications |
| `error_logs` | System errors |
| `webhooks` | Webhook configurations |
| `api_keys` | API key management |
| `installed_plugins` | Plugin registry |

### Users Table

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('username')->unique();
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    
    // Stats
    $table->integer('level')->default(1);
    $table->bigInteger('experience')->default(0);
    $table->integer('strength')->default(10);
    $table->integer('defense')->default(10);
    $table->integer('speed')->default(10);
    $table->integer('health')->default(100);
    $table->integer('max_health')->default(100);
    $table->integer('energy')->default(100);
    $table->integer('max_energy')->default(100);
    
    // Economy
    $table->bigInteger('cash')->default(1000);
    $table->bigInteger('bank')->default(0);
    $table->integer('respect')->default(0);
    $table->integer('bullets')->default(0);
    
    // Position
    $table->foreignId('location_id')->nullable()->constrained();
    $table->foreignId('rank_id')->nullable()->constrained();
    
    // Status
    $table->timestamp('jail_until')->nullable();
    $table->timestamp('hospital_until')->nullable();
    $table->timestamp('last_active')->nullable();
    $table->timestamp('last_login_at')->nullable();
    $table->string('last_login_ip')->nullable();
    
    $table->rememberToken();
    $table->timestamps();
    
    // Indexes
    $table->index(['level', 'experience']);
    $table->index('last_active');
});
```

---

## Creating Models

### Basic Model

```php
<?php

namespace App\Plugins\YourPlugin\Models;

use Illuminate\Database\Eloquent\Model;

class YourModel extends Model
{
    /**
     * Table name (optional if following convention)
     */
    protected $table = 'your_models';
    
    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'description',
        'value',
        'active',
    ];
    
    /**
     * Attribute casting
     */
    protected $casts = [
        'active' => 'boolean',
        'value' => 'integer',
        'metadata' => 'array',
        'expires_at' => 'datetime',
    ];
    
    /**
     * Hidden from serialization
     */
    protected $hidden = [
        'internal_field',
    ];
    
    /**
     * Appended attributes
     */
    protected $appends = [
        'formatted_value',
    ];
    
    /**
     * Accessor for formatted value
     */
    public function getFormattedValueAttribute(): string
    {
        return '$' . number_format($this->value);
    }
}
```

### Model with Relationships

```php
<?php

namespace App\Plugins\YourPlugin\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YourModel extends Model
{
    protected $fillable = ['user_id', 'name'];
    
    /**
     * Belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Has many items
     */
    public function items(): HasMany
    {
        return $this->hasMany(YourItem::class);
    }
    
    /**
     * Belongs to many tags (pivot table)
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class)
            ->withPivot('order')
            ->withTimestamps();
    }
}
```

---

## Creating Migrations

### Basic Migration

```bash
# In your plugin directory
php artisan make:migration create_your_models_table --path=app/Plugins/YourPlugin/database/migrations
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('your_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('value')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('your_models');
    }
};
```

### Migration with Foreign Keys

```php
Schema::create('user_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();
    $table->foreignId('item_id')
        ->constrained()
        ->cascadeOnDelete();
    $table->integer('quantity')->default(1);
    $table->timestamps();
    
    // Composite unique key
    $table->unique(['user_id', 'item_id']);
});
```

### Adding Columns to Existing Table

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('new_stat')->default(0)->after('experience');
        });
    }
    
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('new_stat');
        });
    }
};
```

---

## Query Scopes

### Local Scopes

```php
class YourModel extends Model
{
    /**
     * Scope to active items
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    
    /**
     * Scope to items above a value
     */
    public function scopeMinValue($query, int $value)
    {
        return $query->where('value', '>=', $value);
    }
    
    /**
     * Scope to user's items
     */
    public function scopeForUser($query, $user)
    {
        return $query->where('user_id', $user->id);
    }
}

// Usage
$items = YourModel::active()->minValue(100)->get();
$userItems = YourModel::forUser($user)->get();
```

### Global Scopes

```php
// In model boot method
protected static function booted(): void
{
    static::addGlobalScope('active', function ($query) {
        $query->where('active', true);
    });
}

// Disable scope when needed
YourModel::withoutGlobalScope('active')->get();
```

---

## Seeders

### Creating a Seeder

```php
<?php

namespace App\Plugins\YourPlugin\Database\Seeders;

use App\Plugins\YourPlugin\Models\YourModel;
use Illuminate\Database\Seeder;

class YourModelSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Item One',
                'description' => 'First item',
                'value' => 100,
                'active' => true,
            ],
            [
                'name' => 'Item Two',
                'description' => 'Second item',
                'value' => 200,
                'active' => true,
            ],
        ];
        
        foreach ($items as $item) {
            YourModel::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
```

### Running Seeders

```bash
# Run specific seeder
php artisan db:seed --class="App\Plugins\YourPlugin\Database\Seeders\YourModelSeeder"

# Run all seeders
php artisan db:seed
```

---

## Factories (Testing)

```php
<?php

namespace App\Plugins\YourPlugin\Database\Factories;

use App\Plugins\YourPlugin\Models\YourModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class YourModelFactory extends Factory
{
    protected $model = YourModel::class;
    
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'value' => fake()->numberBetween(10, 1000),
            'active' => true,
        ];
    }
    
    /**
     * Inactive state
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
```

Usage in tests:

```php
// Create one
$item = YourModel::factory()->create();

// Create many
$items = YourModel::factory()->count(10)->create();

// With state
$inactive = YourModel::factory()->inactive()->create();
```

---

## Extending the User Model

### Adding Relationships

```php
// In User model or via trait
public function yourPluginItems()
{
    return $this->hasMany(\App\Plugins\YourPlugin\Models\UserItem::class);
}

public function hasItem(int $itemId): bool
{
    return $this->yourPluginItems()
        ->where('item_id', $itemId)
        ->exists();
}
```

### Adding to User via Migration

```php
Schema::table('users', function (Blueprint $table) {
    $table->integer('your_plugin_stat')->default(0);
});
```

---

## Best Practices

### 1. Use Transactions

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($user, $item) {
    $user->decrement('cash', $item->price);
    $user->items()->attach($item->id);
});
```

### 2. Eager Load Relationships

```php
// Bad - N+1 queries
$users = User::all();
foreach ($users as $user) {
    echo $user->gang->name; // Query per user
}

// Good - 2 queries total
$users = User::with('gang')->get();
foreach ($users as $user) {
    echo $user->gang->name;
}
```

### 3. Use Chunk for Large Datasets

```php
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process user
    }
});
```

### 4. Index Frequently Queried Columns

```php
$table->index('user_id');
$table->index(['status', 'created_at']);
```

### 5. Soft Deletes for Important Data

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class YourModel extends Model
{
    use SoftDeletes;
}

// Migration
$table->softDeletes();
```

---

## Next Steps

- [Services](Services) - Business logic layer
- [Creating Plugins](Creating-Plugins) - Full plugin example
- [Routes & Controllers](Routes-and-Controllers) - API development

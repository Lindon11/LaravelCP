# Testing Guide

Comprehensive guide to testing in LaravelCP.

---

## Overview

LaravelCP uses PHPUnit for testing with Laravel's built-in testing utilities.

### Test Types

| Type | Location | Purpose |
|------|----------|---------|
| Unit | `tests/Unit/` | Test isolated classes/methods |
| Feature | `tests/Feature/` | Test API endpoints and integrations |
| Plugin | `tests/Feature/Plugins/` | Test plugin functionality |

---

## Running Tests

### Basic Commands

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run specific test method
php artisan test --filter=test_user_can_login

# Run tests in parallel
php artisan test --parallel

# Run with coverage
php artisan test --coverage

# Verbose output
php artisan test -v
```

### PHPUnit Directly

```bash
# Run via PHPUnit
./vendor/bin/phpunit

# With configuration
./vendor/bin/phpunit --configuration phpunit.xml
```

---

## PHPUnit Configuration

### phpunit.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
    </php>
</phpunit>
```

---

## Test Structure

### Base Test Case

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
```

### Feature Test Template

```php
<?php

namespace Tests\Feature;

use App\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create authenticated user
        $this->user = User::factory()->create();
    }

    public function test_example_endpoint_returns_success(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/example');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'created_at']
                ]
            ]);
    }
}
```

### Unit Test Template

```php
<?php

namespace Tests\Unit;

use App\Plugins\YourPlugin\Services\YourService;
use PHPUnit\Framework\TestCase;

class YourServiceTest extends TestCase
{
    public function test_calculates_reward_correctly(): void
    {
        $service = new YourService();
        
        $result = $service->calculateReward(100, 1.5);
        
        $this->assertEquals(150, $result);
    }
}
```

---

## Testing Authentication

### Login Test

```php
public function test_user_can_login(): void
{
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'email', 'username']
        ]);
}
```

### Protected Route Test

```php
public function test_protected_route_requires_auth(): void
{
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
}

public function test_authenticated_user_can_access(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/api/user');

    $response->assertOk();
}
```

---

## Testing Plugins

### Crime Plugin Test

```php
<?php

namespace Tests\Feature\Plugins;

use App\Core\Models\User;
use App\Plugins\Crimes\Models\Crime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrimesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_attempt_crime(): void
    {
        $user = User::factory()->create([
            'level' => 5,
            'energy' => 100,
        ]);
        
        $crime = Crime::factory()->create([
            'level_required' => 1,
            'energy_cost' => 10,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/crimes/{$crime->id}/attempt");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'rewards',
            ]);
    }

    public function test_user_cannot_attempt_crime_without_energy(): void
    {
        $user = User::factory()->create([
            'energy' => 0,
        ]);
        
        $crime = Crime::factory()->create([
            'energy_cost' => 10,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/crimes/{$crime->id}/attempt");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Not enough energy',
            ]);
    }

    public function test_crime_rewards_are_within_range(): void
    {
        $user = User::factory()->create();
        
        $crime = Crime::factory()->create([
            'min_reward' => 50,
            'max_reward' => 100,
            'success_rate' => 100, // Guaranteed success
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/crimes/{$crime->id}/attempt");

        $data = $response->json();
        
        $this->assertGreaterThanOrEqual(50, $data['rewards']['cash']);
        $this->assertLessThanOrEqual(100, $data['rewards']['cash']);
    }
}
```

### Banking Plugin Test

```php
<?php

namespace Tests\Feature\Plugins;

use App\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_deposit_money(): void
    {
        $user = User::factory()->create([
            'cash' => 1000,
            'bank' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/bank/deposit', [
                'amount' => 500,
            ]);

        $response->assertOk();
        
        $user->refresh();
        $this->assertEquals(500, $user->cash);
        $this->assertEquals(500, $user->bank);
    }

    public function test_user_cannot_deposit_more_than_they_have(): void
    {
        $user = User::factory()->create([
            'cash' => 100,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/bank/deposit', [
                'amount' => 500,
            ]);

        $response->assertStatus(422);
    }

    public function test_user_can_transfer_money(): void
    {
        $sender = User::factory()->create(['bank' => 1000]);
        $recipient = User::factory()->create(['bank' => 0]);

        $response = $this->actingAs($sender)
            ->postJson('/api/bank/transfer', [
                'recipient_id' => $recipient->id,
                'amount' => 250,
            ]);

        $response->assertOk();
        
        $sender->refresh();
        $recipient->refresh();
        
        $this->assertEquals(750, $sender->bank);
        $this->assertEquals(250, $recipient->bank);
    }
}
```

---

## Testing Services

### Service Test with Mocking

```php
<?php

namespace Tests\Unit\Services;

use App\Core\Models\User;
use App\Core\Services\HookService;
use App\Core\Services\NotificationService;
use App\Plugins\YourPlugin\Services\YourService;
use Mockery;
use Tests\TestCase;

class YourServiceTest extends TestCase
{
    public function test_performs_action_and_sends_notification(): void
    {
        // Create mocks
        $hookService = Mockery::mock(HookService::class);
        $hookService->shouldReceive('doAction')
            ->once()
            ->with('your_plugin.action_complete', Mockery::any(), Mockery::any());
        
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('send')
            ->once();

        // Create service with mocks
        $service = new YourService($hookService, $notificationService);
        
        $user = User::factory()->create();
        
        $result = $service->performAction($user, ['value' => 100]);
        
        $this->assertTrue($result['success']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
```

---

## Database Testing

### Using Factories

```php
// Create single model
$user = User::factory()->create();

// Create multiple
$users = User::factory()->count(10)->create();

// With specific attributes
$user = User::factory()->create([
    'level' => 50,
    'cash' => 100000,
]);

// With state
$user = User::factory()->vip()->create();

// With relationship
$user = User::factory()
    ->has(UserItem::factory()->count(5), 'items')
    ->create();
```

### Database Assertions

```php
// Assert record exists
$this->assertDatabaseHas('users', [
    'email' => 'test@example.com',
]);

// Assert record doesn't exist
$this->assertDatabaseMissing('users', [
    'email' => 'deleted@example.com',
]);

// Assert count
$this->assertDatabaseCount('users', 5);

// Assert soft deleted
$this->assertSoftDeleted('posts', [
    'id' => 1,
]);
```

---

## HTTP Testing

### Request Assertions

```php
$response->assertStatus(200);
$response->assertOk();                    // 200
$response->assertCreated();               // 201
$response->assertNoContent();             // 204
$response->assertNotFound();              // 404
$response->assertUnauthorized();          // 401
$response->assertForbidden();             // 403
$response->assertUnprocessable();         // 422
```

### JSON Assertions

```php
// Assert exact JSON
$response->assertJson([
    'success' => true,
    'data' => ['id' => 1],
]);

// Assert structure
$response->assertJsonStructure([
    'data' => [
        '*' => ['id', 'name', 'email']
    ],
    'meta' => ['total', 'page']
]);

// Assert contains
$response->assertJsonFragment([
    'name' => 'Test User',
]);

// Assert path value
$response->assertJsonPath('data.0.id', 1);

// Assert count
$response->assertJsonCount(10, 'data');
```

---

## Testing Hooks

```php
<?php

namespace Tests\Unit;

use App\Core\Services\HookService;
use Tests\TestCase;

class HookServiceTest extends TestCase
{
    public function test_action_is_executed(): void
    {
        $hookService = app(HookService::class);
        $executed = false;
        
        $hookService->addAction('test.action', function () use (&$executed) {
            $executed = true;
        });
        
        $hookService->doAction('test.action');
        
        $this->assertTrue($executed);
    }

    public function test_filter_modifies_value(): void
    {
        $hookService = app(HookService::class);
        
        $hookService->addFilter('test.filter', function ($value) {
            return $value * 2;
        });
        
        $result = $hookService->applyFilters('test.filter', 100);
        
        $this->assertEquals(200, $result);
    }

    public function test_hooks_respect_priority(): void
    {
        $hookService = app(HookService::class);
        $order = [];
        
        $hookService->addAction('test.priority', function () use (&$order) {
            $order[] = 'second';
        }, 20);
        
        $hookService->addAction('test.priority', function () use (&$order) {
            $order[] = 'first';
        }, 10);
        
        $hookService->doAction('test.priority');
        
        $this->assertEquals(['first', 'second'], $order);
    }
}
```

---

## Testing Best Practices

### 1. One Assertion Per Concept

```php
// Good - focused test
public function test_user_level_increases(): void
{
    $user = User::factory()->create(['level' => 1, 'experience' => 0]);
    
    $user->addExperience(100);
    
    $this->assertEquals(2, $user->level);
}

// Bad - testing too many things
public function test_user_stats(): void
{
    $user = User::factory()->create();
    $user->addExperience(100);
    $this->assertEquals(2, $user->level);
    $this->assertEquals(100, $user->experience);
    $this->assertNotNull($user->updated_at);
}
```

### 2. Use Descriptive Names

```php
// Good
public function test_user_cannot_attack_while_in_jail(): void

// Bad
public function test_attack(): void
```

### 3. Arrange-Act-Assert Pattern

```php
public function test_deposit_increases_bank_balance(): void
{
    // Arrange
    $user = User::factory()->create(['cash' => 1000, 'bank' => 0]);
    
    // Act
    $this->actingAs($user)
        ->postJson('/api/bank/deposit', ['amount' => 500]);
    
    // Assert
    $user->refresh();
    $this->assertEquals(500, $user->bank);
}
```

### 4. Test Edge Cases

```php
public function test_cannot_deposit_zero_amount(): void { }
public function test_cannot_deposit_negative_amount(): void { }
public function test_cannot_deposit_more_than_cash_on_hand(): void { }
public function test_deposit_with_exact_amount_available(): void { }
```

---

## Continuous Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo, sqlite3
          
      - name: Install Dependencies
        run: composer install --no-interaction
        
      - name: Run Tests
        run: php artisan test
```

---

## Next Steps

- [Services](Services) - Service layer architecture
- [Creating Plugins](Creating-Plugins) - Build testable plugins
- [Troubleshooting](Troubleshooting) - Common issues

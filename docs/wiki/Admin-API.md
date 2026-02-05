# Admin API

Complete reference for LaravelCP's administrative API endpoints.

---

## Overview

All admin endpoints require:

- Authentication (`auth:sanctum`)
- Admin or Moderator role (`role:admin|moderator`)

**Base URL:** `/api/admin`

**Headers:**

```text
Authorization: Bearer YOUR_TOKEN
Accept: application/json
Content-Type: application/json
```

---

## Dashboard

### Get Dashboard Statistics

**GET** `/api/admin/stats`

```json
// Response (200 OK)
{
    "users": {
        "total": 1500,
        "active_today": 234,
        "active_week": 890,
        "new_today": 45,
        "new_week": 312,
        "online": 89
    },
    "economy": {
        "total_cash": 15000000,
        "total_bank": 45000000,
        "average_cash": 10000,
        "average_bank": 30000
    },
    "activity": {
        "crimes_today": 5670,
        "crimes_week": 34500,
        "combat_today": 890,
        "transactions_today": 2340
    },
    "support": {
        "open_tickets": 12,
        "unresolved_errors": 3
    }
}
```

---

## User Management

### List Users

**GET** `/api/admin/users`

**Query Parameters:**

| Parameter | Type | Description |
| ----------- | ------ | ------------- |
| `page` | int | Page number |
| `per_page` | int | Items per page (default: 15) |
| `search` | string | Search by name, username, email |
| `role` | string | Filter by role |
| `status` | string | Filter: active, banned, online |
| `sort` | string | Sort field |
| `order` | string | asc or desc |

```json
// Response (200 OK)
{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "username": "johndoe",
            "email": "john@example.com",
            "level": 15,
            "cash": 50000,
            "bank": 150000,
            "roles": ["player"],
            "is_banned": false,
            "last_active": "2024-01-15T10:30:00.000000Z",
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "current_page": 1,
    "last_page": 100,
    "per_page": 15,
    "total": 1500
}
```

### Get User Statistics

**GET** `/api/admin/users/statistics`

```json
// Response (200 OK)
{
    "total": 1500,
    "active_today": 234,
    "active_week": 890,
    "banned": 25,
    "by_role": {
        "admin": 2,
        "moderator": 5,
        "player": 1493
    },
    "by_level": {
        "1-10": 500,
        "11-25": 450,
        "26-50": 350,
        "51-75": 150,
        "76-100": 50
    }
}
```

### Create User

**POST** `/api/admin/users`

```json
// Request
{
    "name": "New User",
    "username": "newuser",
    "email": "new@example.com",
    "password": "password123",
    "role": "player"
}

// Response (201 Created)
{
    "message": "User created successfully",
    "data": {
        "id": 1501,
        "name": "New User",
        ...
    }
}
```

### Get User Details

**GET** `/api/admin/users/{id}`

```json
// Response (200 OK)
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "level": 15,
        "experience": 12500,
        "strength": 85,
        "defense": 70,
        "speed": 65,
        "health": 100,
        "max_health": 100,
        "energy": 80,
        "max_energy": 100,
        "cash": 50000,
        "bank": 150000,
        "respect": 250,
        "bullets": 500,
        "location_id": 1,
        "rank_id": 3,
        "roles": ["player"],
        "permissions": [],
        "is_banned": false,
        "ban_reason": null,
        "ban_until": null,
        "last_active": "2024-01-15T10:30:00.000000Z",
        "last_login_ip": "192.168.1.1",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "gang": {
            "id": 5,
            "name": "The Crew"
        },
        "timers": {
            "crime": null,
            "gym": 300
        }
    }
}
```

### Update User

**PATCH** `/api/admin/users/{id}`

```json
// Request
{
    "name": "John Updated",
    "level": 20,
    "cash": 100000,
    "bank": 500000
}

// Response (200 OK)
{
    "message": "User updated successfully",
    "data": { ... }
}
```

### Delete User

**DELETE** `/api/admin/users/{id}`

```json
// Response (200 OK)
{
    "message": "User deleted successfully"
}
```

### Ban User

**POST** `/api/admin/users/{id}/ban`

```json
// Request
{
    "reason": "Cheating - exploiting bug",
    "duration": 7  // days, null for permanent
}

// Response (200 OK)
{
    "message": "User banned successfully",
    "ban_until": "2024-01-22T10:30:00.000000Z"
}
```

### Unban User

**POST** `/api/admin/users/{id}/unban`

```json
// Response (200 OK)
{
    "message": "User unbanned successfully"
}
```

---

## Roles & Permissions

### List Roles

**GET** `/api/admin/roles`

```json
// Response (200 OK)
{
    "data": [
        {
            "id": 1,
            "name": "admin",
            "permissions": ["*"],
            "users_count": 2
        },
        {
            "id": 2,
            "name": "moderator",
            "permissions": ["manage-users", "view-logs"],
            "users_count": 5
        },
        {
            "id": 3,
            "name": "player",
            "permissions": [],
            "users_count": 1493
        }
    ]
}
```

### Create Role

**POST** `/api/admin/roles`

```json
// Request
{
    "name": "vip",
    "permissions": ["vip-features", "priority-support"]
}

// Response (201 Created)
{
    "message": "Role created successfully",
    "data": {
        "id": 4,
        "name": "vip",
        "permissions": ["vip-features", "priority-support"]
    }
}
```

### Update Role

**PATCH** `/api/admin/roles/{id}`

```json
// Request
{
    "permissions": ["vip-features", "priority-support", "bonus-rewards"]
}

// Response (200 OK)
{
    "message": "Role updated successfully",
    "data": { ... }
}
```

### Delete Role

**DELETE** `/api/admin/roles/{id}`

```json
// Response (200 OK)
{
    "message": "Role deleted successfully"
}
```

### List Permissions

**GET** `/api/admin/permissions`

```json
// Response (200 OK)
{
    "data": [
        "manage-users",
        "manage-settings",
        "manage-plugins",
        "view-logs",
        "manage-roles",
        ...
    ]
}
```

### Assign Role to User

**POST** `/api/admin/users/{id}/roles`

```json
// Request
{
    "role": "vip"
}

// Response (200 OK)
{
    "message": "Role assigned successfully"
}
```

### Remove Role from User

**DELETE** `/api/admin/users/{id}/roles`

```json
// Request
{
    "role": "vip"
}

// Response (200 OK)
{
    "message": "Role removed successfully"
}
```

---

## Plugin Management

### List Plugins

**GET** `/api/admin/plugins`

```json
// Response (200 OK)
{
    "installed": [
        {
            "slug": "crimes",
            "name": "Crimes",
            "version": "3.0.0",
            "description": "Crime system",
            "author": "OpenPBBG",
            "enabled": true,
            "status": "installed"
        }
    ],
    "staging": [
        {
            "slug": "new-plugin",
            "name": "New Plugin",
            "version": "1.0.0",
            "status": "staging"
        }
    ],
    "disabled": []
}
```

### Upload Plugin

**POST** `/api/admin/plugins/upload`

```text
Content-Type: multipart/form-data

file: [plugin.zip]
```

```json
// Response (200 OK)
{
    "message": "Plugin uploaded to staging",
    "plugin": {
        "slug": "my-plugin",
        "name": "My Plugin",
        "version": "1.0.0",
        "status": "staging"
    }
}
```

### Install Plugin

**POST** `/api/admin/plugins/{slug}/install`

```json
// Response (200 OK)
{
    "message": "Plugin installed successfully"
}
```

### Enable Plugin

**PUT** `/api/admin/plugins/{slug}/enable`

```json
// Response (200 OK)
{
    "message": "Plugin enabled successfully"
}
```

### Disable Plugin

**PUT** `/api/admin/plugins/{slug}/disable`

```json
// Response (200 OK)
{
    "message": "Plugin disabled successfully"
}
```

### Uninstall Plugin

**DELETE** `/api/admin/plugins/{slug}`

```json
// Request (optional)
{
    "remove_data": true  // Also drop database tables
}

// Response (200 OK)
{
    "message": "Plugin uninstalled successfully"
}
```

---

## Settings

### Get All Settings

**GET** `/api/admin/settings`

```json
// Response (200 OK)
{
    "data": {
        "game.name": "My Game",
        "game.description": "An awesome PBBG",
        "game.starting_cash": 1000,
        "registration.enabled": true,
        ...
    }
}
```

### Update Settings

**PATCH** `/api/admin/settings`

```json
// Request
{
    "game.name": "Updated Game Name",
    "game.starting_cash": 5000
}

// Response (200 OK)
{
    "message": "Settings updated successfully"
}
```

### Get Single Setting

**GET** `/api/admin/settings/{key}`

```json
// Response (200 OK)
{
    "key": "game.name",
    "value": "My Game"
}
```

---

## Error Logs

### List Error Logs

**GET** `/api/admin/error-logs`

**Query Parameters:**

| Parameter | Type | Description |
| ----------- | ------ | ------------- |
| `page` | int | Page number |
| `per_page` | int | Items per page |
| `source` | string | Filter: backend, admin, frontend, laravel_log |
| `level` | string | Filter: error, warning, info |
| `resolved` | boolean | Filter by resolved status |
| `date_from` | string | Start date (Y-m-d) |
| `date_to` | string | End date (Y-m-d) |

```json
// Response (200 OK)
{
    "data": [
        {
            "id": 1,
            "source": "backend",
            "level": "error",
            "message": "SQLSTATE[42S02]: Table not found",
            "file": "app/Core/Models/User.php",
            "line": 45,
            "resolved": false,
            "resolved_by": null,
            "created_at": "2024-01-15T10:30:00.000000Z"
        }
    ],
    "stats": {
        "total": 50,
        "unresolved": 3,
        "by_source": {
            "backend": 30,
            "admin": 15,
            "frontend": 5
        },
        "by_level": {
            "error": 20,
            "warning": 25,
            "info": 5
        }
    }
}
```

### Get Error Statistics

**GET** `/api/admin/error-logs/statistics`

```json
// Response (200 OK)
{
    "total": 150,
    "unresolved": 12,
    "resolved_today": 5,
    "by_source": { ... },
    "by_level": { ... },
    "trend": [
        { "date": "2024-01-10", "count": 8 },
        { "date": "2024-01-11", "count": 12 },
        ...
    ]
}
```

### Resolve Error

**PATCH** `/api/admin/error-logs/{id}/resolve`

```json
// Response (200 OK)
{
    "message": "Error marked as resolved"
}
```

### Bulk Operations

**POST** `/api/admin/error-logs/bulk-resolve`

```json
// Request
{
    "ids": [1, 2, 3, 4, 5]
}

// Response (200 OK)
{
    "message": "5 errors marked as resolved"
}
```

**POST** `/api/admin/error-logs/bulk-delete`

```json
// Request
{
    "ids": [1, 2, 3]
}

// Response (200 OK)
{
    "message": "3 errors deleted"
}
```

---

## Webhooks

### List Webhooks

**GET** `/api/admin/webhooks`

```json
// Response (200 OK)
{
    "data": [
        {
            "id": 1,
            "name": "Discord Notifications",
            "url": "https://discord.com/api/webhooks/...",
            "events": ["user.registered", "user.leveled_up"],
            "enabled": true,
            "last_triggered": "2024-01-15T10:30:00.000000Z",
            "success_rate": 98.5
        }
    ]
}
```

### Get Available Events

**GET** `/api/admin/webhooks/events`

```json
// Response (200 OK)
{
    "events": [
        {
            "name": "user.registered",
            "description": "When a new user registers"
        },
        {
            "name": "user.leveled_up",
            "description": "When a user gains a level"
        },
        {
            "name": "crime.committed",
            "description": "When a crime is committed"
        },
        ...
    ]
}
```

### Create Webhook

**POST** `/api/admin/webhooks`

```json
// Request
{
    "name": "Discord Notifications",
    "url": "https://discord.com/api/webhooks/...",
    "events": ["user.registered", "user.leveled_up"],
    "enabled": true
}

// Response (201 Created)
{
    "message": "Webhook created successfully",
    "data": {
        "id": 2,
        "secret": "whsec_abc123..."
    }
}
```

### Test Webhook

**POST** `/api/admin/webhooks/{id}/test`

```json
// Response (200 OK)
{
    "message": "Test webhook sent",
    "response_status": 200,
    "response_time": 245
}
```

---

## System Health

### Get System Health

**GET** `/api/admin/system/health`

```json
// Response (200 OK)
{
    "status": "healthy",
    "checks": {
        "database": {
            "status": "ok",
            "response_time": 5
        },
        "cache": {
            "status": "ok",
            "driver": "redis"
        },
        "queue": {
            "status": "ok",
            "pending_jobs": 12,
            "failed_jobs": 0
        },
        "storage": {
            "status": "ok",
            "disk_usage": "45%"
        }
    },
    "php_version": "8.3.0",
    "laravel_version": "11.0.0",
    "uptime": "15 days"
}
```

### Clear Cache

**POST** `/api/admin/system/cache/clear`

```json
// Response (200 OK)
{
    "message": "Cache cleared successfully"
}
```

### Retry Failed Jobs

**POST** `/api/admin/system/queue/retry-failed`

```json
// Response (200 OK)
{
    "message": "3 failed jobs retried"
}
```

---

## Next Steps

- [Plugin API](Plugin-API) - Game plugin endpoints
- [Error Logging](Error-Logging) - Error management
- [Webhooks](Webhooks) - Webhook configuration

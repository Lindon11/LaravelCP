# Admin Panel Guide

Guide to using and extending the LaravelCP admin panel.

---

## Overview

The admin panel is a Vue.js 3 SPA located at `/admin`. It provides management interfaces for:

- Users & player management
- Plugin configuration
- Game settings
- System monitoring
- Support tickets
- Announcements

---

## Accessing Admin Panel

### URL

```
https://yourdomain.com/admin
```

### Requirements

- Admin user account
- `admin` role or appropriate permissions

### Default Credentials

After running seeders, a default admin account is created:

```
Email: admin@example.com
Password: password
```

**⚠️ Change this immediately in production!**

---

## Admin Panel Structure

### Directory Layout

```
resources/admin/
├── index.html           # Entry point
├── src/
│   ├── App.vue          # Root component
│   ├── main.js          # Application bootstrap
│   ├── router/          # Vue Router configuration
│   │   └── index.js
│   ├── stores/          # Pinia stores
│   │   ├── auth.js
│   │   └── settings.js
│   ├── views/           # Page components
│   │   ├── Dashboard.vue
│   │   ├── Users.vue
│   │   ├── Plugins.vue
│   │   ├── Settings.vue
│   │   └── ...
│   ├── components/      # Reusable components
│   │   ├── Sidebar.vue
│   │   ├── DataTable.vue
│   │   └── ...
│   └── services/        # API services
│       └── api.js
└── vite.config.js
```

---

## Dashboard

The dashboard displays key metrics:

- **Total Users** - Registered player count
- **Active Users** - Players online in last 15 minutes
- **New Users Today** - Registrations today
- **Total Revenue** - If payment system enabled
- **Error Count** - Recent system errors
- **System Health** - Server status

### Widgets

```vue
<template>
  <div class="dashboard">
    <StatsWidget 
      title="Total Users" 
      :value="stats.totalUsers" 
      icon="users" 
    />
    <StatsWidget 
      title="Active Now" 
      :value="stats.activeUsers" 
      icon="activity" 
      color="green" 
    />
    <RecentUsersWidget :users="recentUsers" />
    <ErrorLogWidget :errors="recentErrors" />
  </div>
</template>
```

---

## User Management

### User List

View all registered users with filtering and search:

- **Search** - By username, email, or ID
- **Filter** - By status, role, level, location
- **Sort** - By any column
- **Actions** - View, edit, ban, delete

### User Details

View and edit individual user:

```
/admin/users/{id}
```

#### Editable Fields

- Username and email
- Stats (level, experience, cash, etc.)
- Roles and permissions
- Ban status
- Account verification

### User Actions

| Action | Description |
|--------|-------------|
| **Edit** | Modify user details |
| **Ban** | Temporarily or permanently ban |
| **Unban** | Remove ban |
| **Reset Password** | Send password reset |
| **Verify Email** | Manually verify |
| **Add Money** | Add cash/bank balance |
| **Delete** | Remove account |

---

## Plugin Management

### Plugin List

View all available plugins:

```
/admin/plugins
```

Displays:
- Plugin name and description
- Version
- Author
- Status (enabled/disabled)
- Actions

### Plugin Actions

| Action | Description |
|--------|-------------|
| **Enable** | Activate plugin |
| **Disable** | Deactivate plugin |
| **Configure** | Open plugin settings |
| **View Info** | Plugin details |

### Plugin Configuration

Each plugin can have its own settings page:

```
/admin/plugins/{plugin}/settings
```

Example settings for Crimes plugin:
- Enable/disable crimes
- Modify success rates
- Adjust rewards
- Set cooldowns

---

## Settings Management

### Game Settings

```
/admin/settings
```

Configure core game parameters:

| Category | Settings |
|----------|----------|
| **General** | Game name, description, timezone |
| **Registration** | Enable/disable, email verification |
| **Economy** | Starting cash, interest rates |
| **Combat** | Enable PvP, damage multipliers |
| **Energy** | Regen rate, max energy |
| **Experience** | Level formula, XP multipliers |

### Setting Storage

Settings are stored in the `settings` table and cached:

```php
// Get setting
$value = setting('game.starting_cash', 1000);

// Set setting
setting(['game.starting_cash' => 5000]);
```

---

## Announcements

### Creating Announcements

```
/admin/announcements/create
```

Fields:
- **Title** - Announcement headline
- **Content** - Full message (supports Markdown)
- **Type** - Info, warning, success, danger
- **Active** - Show/hide
- **Expires At** - Auto-hide date

### Managing Announcements

- View all announcements
- Edit existing
- Toggle visibility
- Delete old announcements

---

## Support Tickets

### Ticket List

```
/admin/tickets
```

View and manage support tickets:
- Filter by status (open, pending, closed)
- Filter by priority
- Assign to admin
- Search by user or subject

### Ticket Actions

| Action | Description |
|--------|-------------|
| **View** | Read ticket details |
| **Reply** | Respond to user |
| **Assign** | Assign to admin |
| **Close** | Mark as resolved |
| **Reopen** | Reopen closed ticket |

---

## Error Logs

### Viewing Errors

```
/admin/errors
```

Browse system errors with:
- Error message and stack trace
- Severity level
- Occurrence count
- First and last seen
- User context (if applicable)

### Resolving Errors

- Mark as resolved
- Add notes
- Delete old entries
- Export for analysis

---

## Creating Admin Pages

### 1. Create View Component

```vue
<!-- resources/admin/src/views/MyAdminPage.vue -->
<template>
  <div class="admin-page">
    <h1>My Admin Page</h1>
    
    <div class="card">
      <div class="card-body">
        <DataTable :columns="columns" :data="items" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '@/services/api';
import DataTable from '@/components/DataTable.vue';

const api = useApi();
const items = ref([]);

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'name', label: 'Name' },
  { key: 'created_at', label: 'Created', type: 'date' },
];

onMounted(async () => {
  const response = await api.get('/admin/my-endpoint');
  items.value = response.data;
});
</script>
```

### 2. Add Route

```javascript
// resources/admin/src/router/index.js
{
  path: '/admin/my-page',
  name: 'admin.my-page',
  component: () => import('@/views/MyAdminPage.vue'),
  meta: { 
    requiresAuth: true,
    permission: 'view my page'
  }
}
```

### 3. Add Navigation Item

```vue
<!-- In Sidebar.vue -->
<NavItem 
  to="/admin/my-page" 
  icon="custom-icon"
  permission="view my page"
>
  My Page
</NavItem>
```

### 4. Create Backend Endpoint

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/my-endpoint', [MyAdminController::class, 'index']);
});
```

---

## Admin Permissions

### Built-in Permissions

| Permission | Description |
|------------|-------------|
| `view admin` | Access admin panel |
| `manage users` | User management |
| `manage plugins` | Plugin configuration |
| `manage settings` | Game settings |
| `manage tickets` | Support system |
| `view errors` | Error logs |

### Checking Permissions

```vue
<template>
  <div v-if="can('manage users')">
    <!-- Content for authorized users -->
  </div>
</template>

<script setup>
import { useCan } from '@/composables/usePermissions';
const can = useCan();
</script>
```

---

## Building Admin Assets

### Development

```bash
cd resources/admin
npm install
npm run dev
```

### Production

```bash
npm run build
```

This builds to `public/admin/`.

---

## API Service

### Using the API Service

```javascript
import { useApi } from '@/services/api';

const api = useApi();

// GET request
const users = await api.get('/admin/users');

// POST request
const response = await api.post('/admin/users', { 
  name: 'John',
  email: 'john@example.com' 
});

// PUT request
await api.put(`/admin/users/${id}`, userData);

// DELETE request
await api.delete(`/admin/users/${id}`);
```

### Error Handling

```javascript
try {
  await api.post('/admin/action', data);
  toast.success('Action completed');
} catch (error) {
  if (error.response?.status === 422) {
    // Validation error
    errors.value = error.response.data.errors;
  } else {
    toast.error('An error occurred');
  }
}
```

---

## Next Steps

- [Admin API](Admin-API) - Backend endpoints reference
- [Creating Plugins](Creating-Plugins) - Add plugin admin pages
- [Authentication API](Authentication-API) - Auth system

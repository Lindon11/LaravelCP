# Authentication API

Complete reference for LaravelCP's authentication system.

---

## Overview

LaravelCP uses **Laravel Sanctum** for API authentication. It supports:

- Token-based authentication
- SPA authentication with cookies
- Two-factor authentication (2FA)
- OAuth providers (Discord, Google, GitHub)
- Password reset via email

---

## Endpoints

### Registration

**POST** `/api/register`

Register a new user account.

```json
// Request
{
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}

// Response (201 Created)
{
    "message": "Registration successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "level": 1,
        "created_at": "2024-01-15T10:30:00.000000Z"
    },
    "token": "1|abc123..."
}
```

**Validation Rules:**

- `name`: required, string, max 255
- `username`: required, string, max 50, unique, alphanumeric
- `email`: required, email, unique
- `password`: required, min 8, confirmed

---

### Login

**POST** `/api/login`

Authenticate and receive an API token.

```json
// Request
{
    "email": "john@example.com",
    "password": "password123"
}

// Response (200 OK)
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "level": 5,
        "roles": ["player"]
    },
    "token": "2|xyz789...",
    "requires_2fa": false
}
```

**With 2FA Enabled:**

```json
// Response (200 OK)
{
    "message": "2FA verification required",
    "requires_2fa": true,
    "temp_token": "temp_abc123..."
}
```

---

### Two-Factor Authentication

**POST** `/api/2fa/verify`

Verify 2FA code after login.

```json
// Request
{
    "temp_token": "temp_abc123...",
    "code": "123456"
}

// Response (200 OK)
{
    "message": "2FA verified",
    "user": { ... },
    "token": "3|def456..."
}
```

**POST** `/api/2fa/setup` (Authenticated)

Enable 2FA for your account.

```json
// Response (200 OK)
{
    "secret": "JBSWY3DPEHPK3PXP",
    "qr_code": "data:image/png;base64,..."
}
```

**POST** `/api/2fa/confirm` (Authenticated)

Confirm 2FA setup with verification code.

```json
// Request
{
    "code": "123456"
}

// Response (200 OK)
{
    "message": "2FA enabled successfully",
    "recovery_codes": [
        "abc123-def456",
        "ghi789-jkl012",
        ...
    ]
}
```

**POST** `/api/2fa/disable` (Authenticated)

Disable 2FA.

```json
// Request
{
    "code": "123456"
}

// Response (200 OK)
{
    "message": "2FA disabled successfully"
}
```

---

### Current User

**GET** `/api/user`

Get the authenticated user's details.

**Headers:**

```text
Authorization: Bearer YOUR_TOKEN
```

```json
// Response (200 OK)
{
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "level": 5,
    "experience": 2500,
    "strength": 50,
    "defense": 45,
    "speed": 40,
    "health": 100,
    "max_health": 100,
    "energy": 80,
    "max_energy": 100,
    "cash": 15000,
    "bank": 50000,
    "location_id": 1,
    "rank_id": 2,
    "roles": ["player"],
    "permissions": [],
    "timers": {
        "crime": null,
        "gym": 45
    },
    "created_at": "2024-01-01T00:00:00.000000Z"
}
```

---

### Logout

**POST** `/api/logout`

Logout and invalidate current token.

```json
// Response (200 OK)
{
    "message": "Logged out successfully"
}
```

**POST** `/api/logout-all`

Logout from all devices.

```json
// Response (200 OK)
{
    "message": "Logged out from all devices"
}
```

---

### Password Reset

**POST** `/api/forgot-password`

Request password reset email.

```json
// Request
{
    "email": "john@example.com"
}

// Response (200 OK)
{
    "message": "Password reset link sent"
}
```

**POST** `/api/validate-reset-token`

Validate a reset token.

```json
// Request
{
    "token": "abc123...",
    "email": "john@example.com"
}

// Response (200 OK)
{
    "valid": true
}
```

**POST** `/api/reset-password`

Reset password with token.

```json
// Request
{
    "token": "abc123...",
    "email": "john@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}

// Response (200 OK)
{
    "message": "Password reset successfully"
}
```

---

### Change Password

**POST** `/api/user/change-password` (Authenticated)

Change password for authenticated user.

```json
// Request
{
    "current_password": "oldpassword",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}

// Response (200 OK)
{
    "message": "Password changed successfully"
}
```

---

## OAuth Authentication

### Get Available Providers

**GET** `/api/oauth/providers`

```json
// Response (200 OK)
{
    "providers": [
        {
            "name": "discord",
            "enabled": true,
            "icon": "discord"
        },
        {
            "name": "google",
            "enabled": true,
            "icon": "google"
        }
    ]
}
```

### Redirect to Provider

**GET** `/api/oauth/{provider}/redirect`

Redirects to OAuth provider's login page.

### OAuth Callback

**GET** `/api/oauth/{provider}/callback`

Handles the OAuth callback. Returns:

```json
// New user created
{
    "message": "Account created successfully",
    "user": { ... },
    "token": "4|oauth_token..."
}

// Existing user logged in
{
    "message": "Login successful",
    "user": { ... },
    "token": "5|oauth_token..."
}
```

### Link OAuth Account (Authenticated)

**GET** `/api/oauth/{provider}/link`

Link an OAuth provider to existing account.

### Get Linked Accounts (Authenticated)

**GET** `/api/oauth/linked`

```json
// Response (200 OK)
{
    "linked": [
        {
            "provider": "discord",
            "linked_at": "2024-01-15T10:30:00.000000Z"
        }
    ]
}
```

### Unlink OAuth Account (Authenticated)

**DELETE** `/api/oauth/{provider}/unlink`

```json
// Response (200 OK)
{
    "message": "Discord account unlinked"
}
```

---

## Using Authentication in Frontend

### JavaScript Example

```javascript
// Login
const login = async (email, password) => {
    const response = await fetch('/api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password }),
    });
    
    const data = await response.json();
    
    if (response.ok) {
        // Store token
        localStorage.setItem('token', data.token);
        return data;
    }
    
    throw new Error(data.message || 'Login failed');
};

// Authenticated request
const fetchUser = async () => {
    const token = localStorage.getItem('token');
    
    const response = await fetch('/api/user', {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        },
    });
    
    return response.json();
};

// Logout
const logout = async () => {
    const token = localStorage.getItem('token');
    
    await fetch('/api/logout', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        },
    });
    
    localStorage.removeItem('token');
};
```

### Axios Example

```javascript
import axios from 'axios';

// Configure axios
const api = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
    },
});

// Add token to requests
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Handle 401 errors
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

// Use
const user = await api.get('/user');
```

---

## Error Responses

### 401 Unauthorized

```json
{
    "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
    "message": "This action is unauthorized."
}
```

### 422 Validation Error

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### 429 Too Many Requests

```json
{
    "message": "Too many login attempts. Please try again in 60 seconds."
}
```

---

## Security Best Practices

1. **Store tokens securely** - Use httpOnly cookies or secure storage
2. **Always use HTTPS** in production
3. **Enable 2FA** for admin accounts
4. **Set token expiration** for sensitive applications
5. **Implement rate limiting** on auth endpoints
6. **Log authentication events** for security auditing

---

## Next Steps

- [Admin API](Admin-API) - Administrative endpoints
- [Two-Factor Authentication](Two-Factor-Authentication) - 2FA details
- [Webhooks](Webhooks) - Auth event notifications

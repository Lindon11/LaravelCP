# API Implementation Summary

## What Was Created

### 1. API Manager Module (Admin Interface)
Located in: `/modules/installed/apiManager/`

**Purpose:** Provides administrative interface for managing API keys, monitoring usage, and viewing documentation.

**Features:**
- ✓ Dashboard with statistics (total keys, requests, failed requests)
- ✓ API key creation with secure secret generation
- ✓ Key management (list, view, delete)
- ✓ Permissions system (JSON array)
- ✓ Rate limiting configuration (per-hour limits)
- ✓ Request logs with pagination
- ✓ Endpoint documentation
- ✓ Professional UI with icons, badges, and responsive design

**Database Tables:**
- `api_keys` - Stores API credentials and configuration
- `api_request_logs` - Logs all API requests
- `api_endpoints` - Configured endpoint definitions
- `api_rate_limits` - Rate limit tracking
- `api_webhooks` - Webhook configurations

**Access:** Admin Panel → API Manager

---

### 2. API Endpoints Module (Request Handler)
Located in: `/modules/installed/api/`

**Purpose:** Handles actual API requests, authenticates users, enforces limits, and returns data.

**Authentication:**
- Bearer token (API Key) in `Authorization` header
- API Secret in `X-API-Secret` header
- Secrets are hashed with bcrypt in database
- Automatic validation against `api_keys` table

**Rate Limiting:**
- Per-key hourly limits
- Sliding window (last 3600 seconds)
- Returns 429 when exceeded
- Logged to `api_request_logs`

**Implemented Endpoints:**

#### Public (No Auth)
- `GET /?page=api` - API information

#### Users (requires `read:users` or `user.read`)
- `GET /?page=api&action=v1/users` - List users (paginated)
- `GET /?page=api&action=v1/users/{id}` - Get specific user
- `PUT /?page=api&action=v1/users/{id}` - Update user (requires `write:users`)

#### Statistics (requires `read:stats` or `stats.read`)
- `GET /?page=api&action=v1/stats` - Global game stats
- `GET /?page=api&action=v1/stats/{id}` - User stats

#### Gangs (requires `read:gangs` or `gangs.read`)
- `GET /?page=api&action=v1/gangs` - List gangs (paginated)
- `GET /?page=api&action=v1/gangs/{id}` - Get specific gang

#### Leaderboards (requires `public` or `read:stats`)
- `GET /?page=api&action=v1/leaderboards/money` - Money leaderboard
- `GET /?page=api&action=v1/leaderboards/total_stats` - Stats leaderboard

#### Mail (requires `mail.read`/`mail.write`) *Placeholder*
- `GET /?page=api&action=v1/mail/inbox` - Get inbox
- `POST /?page=api&action=v1/mail/send` - Send message

#### Crimes (requires `game.play`) *Placeholder*
- `POST /?page=api&action=v1/crimes/commit` - Commit crime

---

### 3. Test Script
Located in: `/test_api.php`

**Purpose:** Automated testing of API endpoints

**Features:**
- Tests all major endpoints
- Validates authentication
- Checks error handling
- Color-coded output
- Detailed response logging

**Usage:**
```bash
php test_api.php
```

---

## How It Works

### Creating an API Key

1. Go to **Admin Panel → API Manager**
2. Click **"Create New API Key"**
3. Fill in the form:
   - **Label:** Descriptive name (e.g., "Mobile App", "External Service")
   - **Permissions:** Select from available permissions
     - `admin` - Full access
     - `public` - Public data only
     - `read:users`, `user.read` - Read user data
     - `write:users`, `user.write` - Modify users
     - `read:stats`, `stats.read` - Read statistics
     - `read:gangs`, `gangs.read` - Read gang data
     - `mail.read`, `mail.write` - Mail operations
     - `game.play`, `write:game` - Game actions
   - **Rate Limit:** Requests per hour (default: 1000)
   - **Expiration:** Optional expiry date
4. Click **"Generate API Key"**
5. **IMPORTANT:** Copy the **API Secret** - it's shown only once!

### Making API Requests

**cURL Example:**
```bash
curl -H "Authorization: Bearer YOUR_API_KEY" \
     -H "X-API-Secret: YOUR_API_SECRET" \
     http://localhost/?page=api&action=v1/users/1
```

**JavaScript Example:**
```javascript
fetch('http://localhost/?page=api&action=v1/users/1', {
  headers: {
    'Authorization': 'Bearer YOUR_API_KEY',
    'X-API-Secret': 'YOUR_API_SECRET'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

**PHP Example:**
```php
$ch = curl_init('http://localhost/?page=api&action=v1/users/1');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer YOUR_API_KEY',
    'X-API-Secret: YOUR_API_SECRET'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$data = json_decode($response, true);
```

### Response Format

**Success:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "money": 10000
  }
}
```

**Error:**
```json
{
  "success": false,
  "error": "Insufficient permissions"
}
```

### HTTP Status Codes

- `200` - Success
- `400` - Bad request
- `401` - Unauthorized (invalid credentials)
- `403` - Forbidden (insufficient permissions)
- `404` - Not found
- `405` - Method not allowed
- `429` - Rate limit exceeded

---

## Monitoring

### Request Logs
View in: **Admin Panel → API Manager → Request Logs**

Tracks:
- Timestamp
- Endpoint accessed
- HTTP method
- Response code
- Response time (ms)
- IP address

### Usage Statistics
View in: **Admin Panel → API Manager → Dashboard**

Shows:
- Total API keys
- Active keys
- Requests today
- Failed requests today
- Top endpoints
- Recent activity

---

## Security Features

1. **Hashed Secrets:** API secrets are stored using `password_hash()` (bcrypt)
2. **Rate Limiting:** Prevents abuse with per-key hourly limits
3. **Permissions:** Fine-grained access control
4. **Request Logging:** Full audit trail
5. **Expiration:** Keys can have expiry dates
6. **Instant Revocation:** Keys can be deactivated or deleted anytime
7. **CORS Support:** Configurable cross-origin policies

---

## Testing

1. Create an API key in the admin panel
2. Edit `test_api.php` and add your credentials:
   ```php
   $apiKey = 'your_api_key_here';
   $apiSecret = 'your_api_secret_here';
   ```
3. Run the test script:
   ```bash
   php test_api.php
   ```

Expected output:
```
===========================================
API Test Suite
===========================================

--- Test 1: API Information (No Auth) ---
Testing: Get API Info
URL: http://localhost/?page=api
✓ PASSED - Status: 200

--- Test 2: Authentication Tests ---
Testing: Invalid API Key
✓ PASSED - Status: 401

...

===========================================
Test Summary
===========================================
Passed: 10
Failed: 0

Total: 10

✓ All tests passed!
```

---

## Adding New Endpoints

1. **Add to Database:**
   - Go to API Manager → Endpoints
   - Or manually insert into `api_endpoints` table

2. **Update Routing:**
   - Edit `/modules/installed/api/api.inc.php`
   - Add case to `routeRequest()` switch statement

3. **Create Handler:**
   ```php
   private function handleNewResource($id, $method) {
       // Check permissions
       if (!$this->hasPermission('read:resource')) {
           return $this->errorResponse('Insufficient permissions', 403);
       }
       
       // Handle GET
       if ($method === 'GET') {
           $data = // ... fetch data
           return $this->successResponse($data);
       }
       
       return $this->errorResponse('Method not allowed', 405);
   }
   ```

4. **Update Documentation:**
   - Edit README.md
   - Update docs in API Manager

---

## Files Created

```
modules/installed/apiManager/
├── module.json              # Admin module config
├── apiManager.admin.php     # Admin logic (242 lines)
├── apiManager.tpl.php       # Admin templates (734 lines)
└── hooks.php                # Database schema

modules/installed/api/
├── module.json              # API module config
├── api.inc.php              # API handler (500+ lines)
└── README.md                # Documentation

test_api.php                 # Test script (200+ lines)
```

---

## Next Steps

1. **Test the API:**
   - Create an API key in admin panel
   - Run test script
   - Test with curl/Postman

2. **Customize Endpoints:**
   - Add game-specific endpoints
   - Integrate with existing systems (mail, crimes, etc.)

3. **External Integration:**
   - Build mobile apps
   - Create external dashboards
   - Integrate with third-party services

4. **Documentation:**
   - Share API docs with developers
   - Create example applications
   - Document rate limits and permissions

---

## Troubleshooting

**401 Unauthorized:**
- Check API key is correct
- Check API secret is correct
- Verify key is active
- Check key hasn't expired

**403 Forbidden:**
- Check key has required permissions
- View permissions in API Manager

**429 Rate Limited:**
- Check usage in request logs
- Increase rate limit if needed
- Wait for rate limit window to reset (1 hour)

**404 Not Found:**
- Check endpoint URL is correct
- Verify API version (v1)
- Check endpoint exists in database

---

## Support

For issues or questions:
1. Check request logs in API Manager
2. Review API documentation in admin panel
3. Test with curl for debugging
4. Check PHP error logs for server errors

# OpenPBBG API Documentation

Version: 3.0  
Base URL: `http://localhost:8000/api`

## Authentication

All API endpoints require authentication using Laravel Sanctum tokens.

### Register
**POST** `/api/register`

**Request:**
```json
{
  "username": "player123",
  "email": "player@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "username": "player123",
    "email": "player@example.com",
    "level": 1,
    "experience": 0,
    "cash": 1000,
    "bank": 0,
    "health": 100,
    "max_health": 100,
    "energy": 100,
    "max_energy": 100,
    "current_rank": {
      "name": "Thug",
      "required_exp": 0
    },
    "location": {
      "name": "Detroit"
    }
  },
  "token": "1|abcdef..."
}
```

### Login
**POST** `/api/login`

**Request:**
```json
{
  "login": "player123",
  "password": "password123"
}
```

**Response:** Same as register

### Get User
**GET** `/api/user`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "user": {
    "id": 1,
    "username": "player123",
    ...
  }
}
```

### Logout
**POST** `/api/logout`

**Headers:** `Authorization: Bearer {token}`

---

## Game Endpoints

All game endpoints require authentication: `Authorization: Bearer {token}`

### Dashboard
**GET** `/api/dashboard`

Returns player info, modules, daily rewards, active timers, and notifications.

### Player Profile
**GET** `/api/player/{id}`

Get another player's profile.

### Travel
**GET** `/api/travel`

List all locations.

**POST** `/api/travel/{location_id}`

Travel to a location.

### Notifications
**GET** `/api/notifications` - List all notifications  
**GET** `/api/notifications/recent` - Get recent notifications  
**GET** `/api/notifications/unread-count` - Get unread count  
**POST** `/api/notifications/{id}/read` - Mark as read  
**POST** `/api/notifications/mark-all-read` - Mark all as read  
**DELETE** `/api/notifications/{id}` - Delete notification

### Chat
**GET** `/api/channels` - List all channels  
**POST** `/api/channels` - Create channel  
**GET** `/api/channels/{channel}` - Get channel details  
**GET** `/api/channels/{channel}/messages` - Get messages  
**POST** `/api/channels/{channel}/messages` - Send message

### Direct Messages
**GET** `/api/direct-messages` - List conversations  
**GET** `/api/direct-messages/{user}` - Get conversation  
**POST** `/api/direct-messages` - Send message

---

## Module Endpoints

All modules are registered automatically under `/api/{module-name}`.

### Crimes
**GET** `/api/crimes` - List available crimes  
**POST** `/api/crimes/{crime}` - Commit a crime

### Gym
**GET** `/api/gym` - View training options  
**POST** `/api/gym/train` - Train stats

### Hospital
**GET** `/api/hospital` - View hospital status  
**POST** `/api/hospital/heal` - Heal player

### Jail
**GET** `/api/jail` - View jail status  
**POST** `/api/jail/bust/{player}` - Bust player from jail

### Bank
**GET** `/api/bank` - View bank account  
**POST** `/api/bank/deposit` - Deposit cash  
**POST** `/api/bank/withdraw` - Withdraw cash

### Combat
**GET** `/api/combat` - List players to attack  
**POST** `/api/combat/attack/{player}` - Attack player

### Bounties
**GET** `/api/bounties` - List active bounties  
**POST** `/api/bounties/{player}` - Place bounty

### Detective
**POST** `/api/detective/search` - Search for player

### Theft
**GET** `/api/theft` - List cars to steal  
**POST** `/api/theft/{car}` - Steal car  
**GET** `/api/theft/garage` - View garage

### Achievements
**GET** `/api/achievements` - List achievements  
**GET** `/api/achievements/progress` - Get progress

### Missions
**GET** `/api/missions` - List available missions  
**POST** `/api/missions/{mission}/start` - Start mission

### Organized Crime
**GET** `/api/organized-crimes` - List organized crimes  
**POST** `/api/organized-crimes/{crime}/join` - Join crime

### Gangs
**GET** `/api/gangs` - List gangs  
**POST** `/api/gangs/create` - Create gang  
**POST** `/api/gangs/{gang}/join` - Join gang

### Inventory
**GET** `/api/inventory` - View inventory  
**POST** `/api/inventory/{item}/use` - Use item

### Shop
**GET** `/api/shop` - List items for sale  
**POST** `/api/shop/{item}/buy` - Buy item

### Leaderboards
**GET** `/api/leaderboards` - View leaderboards

### Forum
**GET** `/api/forum/categories` - List categories  
**GET** `/api/forum/topics` - List topics  
**POST** `/api/forum/topics` - Create topic  
**POST** `/api/forum/topics/{topic}/reply` - Reply to topic

---

## Error Responses

All endpoints return standard error responses:

```json
{
  "message": "Error description",
  "errors": {
    "field": ["Error message"]
  }
}
```

**Status Codes:**
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Rate Limiting

API requests are rate limited to prevent abuse. Current limits:
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated users

---

## Admin Dashboard

The admin dashboard is available at `/admin` for users with admin permissions. It uses Filament and is separate from the API.

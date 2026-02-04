# Plugin API Reference

Complete API endpoint reference for all built-in plugins.

---

## Crimes Plugin

Commit crimes to earn money and experience.

### Attempt Crime

```http
POST /api/crimes/{crime}/attempt
Authorization: Bearer {token}
```

**Response:**

```json
{
  "success": true,
  "message": "You successfully committed the crime!",
  "rewards": {
    "cash": 150,
    "experience": 25,
    "respect": 5
  },
  "cooldown": 30
}
```

### List Crimes

```http
GET /api/crimes
Authorization: Bearer {token}
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Pickpocket",
      "description": "Steal from unsuspecting pedestrians",
      "difficulty": "easy",
      "min_reward": 50,
      "max_reward": 200,
      "cooldown": 30,
      "level_required": 1
    }
  ]
}
```

---

## Banking Plugin

Manage player finances.

### Deposit Money

```http
POST /api/bank/deposit
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 1000
}
```

### Withdraw Money

```http
POST /api/bank/withdraw
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 500
}
```

### Transfer Money

```http
POST /api/bank/transfer
Authorization: Bearer {token}
Content-Type: application/json

{
  "recipient_id": 123,
  "amount": 250,
  "message": "For the job"
}
```

### Get Balance

```http
GET /api/bank/balance
Authorization: Bearer {token}
```

**Response:**

```json
{
  "cash": 5000,
  "bank": 25000,
  "total": 30000
}
```

---

## Combat Plugin

Player vs Player combat system.

### Attack Player

```http
POST /api/combat/attack/{user}
Authorization: Bearer {token}
```

**Response:**

```json
{
  "success": true,
  "result": "win",
  "damage_dealt": 45,
  "damage_received": 12,
  "rewards": {
    "cash": 500,
    "experience": 50
  },
  "attacker_health": 88,
  "defender_health": 0
}
```

### Get Attack Log

```http
GET /api/combat/history
Authorization: Bearer {token}
```

---

## Gangs Plugin

Gang management system.

### Create Gang

```http
POST /api/gangs
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "The Syndicate",
  "tag": "SYN",
  "description": "A powerful crime organization"
}
```

### Join Gang

```http
POST /api/gangs/{gang}/join
Authorization: Bearer {token}
```

### Leave Gang

```http
POST /api/gangs/leave
Authorization: Bearer {token}
```

### Get Gang Details

```http
GET /api/gangs/{gang}
Authorization: Bearer {token}
```

**Response:**

```json
{
  "id": 1,
  "name": "The Syndicate",
  "tag": "SYN",
  "level": 5,
  "respect": 15000,
  "bank": 500000,
  "leader": {
    "id": 1,
    "username": "Boss"
  },
  "members_count": 25,
  "created_at": "2024-01-01T00:00:00Z"
}
```

### Invite Member

```http
POST /api/gangs/{gang}/invite
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 123
}
```

### Kick Member

```http
DELETE /api/gangs/{gang}/members/{user}
Authorization: Bearer {token}
```

### Deposit to Gang Bank

```http
POST /api/gangs/{gang}/bank/deposit
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 10000
}
```

---

## Items Plugin

Item and inventory management.

### Get Inventory

```http
GET /api/inventory
Authorization: Bearer {token}
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "item": {
        "id": 5,
        "name": "Pistol",
        "type": "weapon",
        "stats": {
          "damage": 15
        }
      },
      "quantity": 1,
      "equipped": true
    }
  ],
  "capacity": {
    "used": 5,
    "max": 50
  }
}
```

### Use Item

```http
POST /api/inventory/{userItem}/use
Authorization: Bearer {token}
```

### Equip Item

```http
POST /api/inventory/{userItem}/equip
Authorization: Bearer {token}
```

### Unequip Item

```http
POST /api/inventory/{userItem}/unequip
Authorization: Bearer {token}
```

### Drop Item

```http
DELETE /api/inventory/{userItem}
Authorization: Bearer {token}

{
  "quantity": 1
}
```

---

## Properties Plugin

Property ownership and income.

### List Available Properties

```http
GET /api/properties
Authorization: Bearer {token}
```

### Purchase Property

```http
POST /api/properties/{property}/purchase
Authorization: Bearer {token}
```

### Collect Income

```http
POST /api/properties/{userProperty}/collect
Authorization: Bearer {token}
```

### Upgrade Property

```http
POST /api/properties/{userProperty}/upgrade
Authorization: Bearer {token}
```

### Get My Properties

```http
GET /api/properties/mine
Authorization: Bearer {token}
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "property": {
        "name": "Apartment Complex",
        "type": "residential"
      },
      "level": 3,
      "income_per_hour": 500,
      "last_collected": "2024-01-15T10:00:00Z",
      "pending_income": 2500
    }
  ],
  "total_hourly_income": 2500
}
```

---

## Jobs Plugin

Employment and job system.

### List Jobs

```http
GET /api/jobs
Authorization: Bearer {token}
```

### Apply for Job

```http
POST /api/jobs/{job}/apply
Authorization: Bearer {token}
```

### Work

```http
POST /api/jobs/work
Authorization: Bearer {token}
```

**Response:**

```json
{
  "success": true,
  "message": "You worked as a Store Clerk",
  "earnings": 75,
  "experience": 10,
  "next_shift_available": "2024-01-15T11:00:00Z"
}
```

### Quit Job

```http
POST /api/jobs/quit
Authorization: Bearer {token}
```

---

## Missions Plugin

Story-driven missions and quests.

### List Available Missions

```http
GET /api/missions
Authorization: Bearer {token}
```

### Start Mission

```http
POST /api/missions/{mission}/start
Authorization: Bearer {token}
```

### Complete Mission Step

```http
POST /api/missions/{userMission}/step/{step}/complete
Authorization: Bearer {token}
```

### Get Active Missions

```http
GET /api/missions/active
Authorization: Bearer {token}
```

---

## Travel Plugin

Location travel system.

### Get Locations

```http
GET /api/locations
Authorization: Bearer {token}
```

### Travel to Location

```http
POST /api/travel/{location}
Authorization: Bearer {token}
```

**Response:**

```json
{
  "success": true,
  "message": "You have arrived in Los Santos",
  "location": {
    "id": 2,
    "name": "Los Santos",
    "description": "The city of dreams and crime"
  },
  "travel_time": 60,
  "cost": 500
}
```

### Get Travel Status

```http
GET /api/travel/status
Authorization: Bearer {token}
```

---

## Casino Plugin

Gambling games.

### Play Slots

```http
POST /api/casino/slots
Authorization: Bearer {token}
Content-Type: application/json

{
  "bet": 100
}
```

**Response:**

```json
{
  "success": true,
  "result": [
    "cherry",
    "cherry",
    "cherry"
  ],
  "win": true,
  "payout": 500,
  "multiplier": 5
}
```

### Play Blackjack

```http
POST /api/casino/blackjack/deal
Authorization: Bearer {token}
Content-Type: application/json

{
  "bet": 500
}
```

### Blackjack Actions

```http
POST /api/casino/blackjack/{gameId}/hit
POST /api/casino/blackjack/{gameId}/stand
POST /api/casino/blackjack/{gameId}/double
Authorization: Bearer {token}
```

### Play Roulette

```http
POST /api/casino/roulette
Authorization: Bearer {token}
Content-Type: application/json

{
  "bets": [
    { "type": "number", "value": 17, "amount": 100 },
    { "type": "color", "value": "red", "amount": 200 }
  ]
}
```

---

## Messaging Plugin

Private messaging system.

### Send Message

```http
POST /api/messages
Authorization: Bearer {token}
Content-Type: application/json

{
  "recipient_id": 123,
  "subject": "Hello",
  "body": "How are you?"
}
```

### Get Inbox

```http
GET /api/messages/inbox
Authorization: Bearer {token}
```

### Get Sent Messages

```http
GET /api/messages/sent
Authorization: Bearer {token}
```

### Read Message

```http
GET /api/messages/{message}
Authorization: Bearer {token}
```

### Delete Message

```http
DELETE /api/messages/{message}
Authorization: Bearer {token}
```

---

## Forum Plugin

Community forums.

### List Categories

```http
GET /api/forum/categories
```

### List Threads

```http
GET /api/forum/categories/{category}/threads
```

### Create Thread

```http
POST /api/forum/categories/{category}/threads
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Thread Title",
  "body": "Thread content..."
}
```

### Reply to Thread

```http
POST /api/forum/threads/{thread}/replies
Authorization: Bearer {token}
Content-Type: application/json

{
  "body": "Reply content..."
}
```

---

## Tickets Plugin

Support ticket system.

### Create Ticket

```http
POST /api/tickets
Authorization: Bearer {token}
Content-Type: application/json

{
  "subject": "Account Issue",
  "message": "I need help with...",
  "priority": "high"
}
```

### Get My Tickets

```http
GET /api/tickets
Authorization: Bearer {token}
```

### Reply to Ticket

```http
POST /api/tickets/{ticket}/reply
Authorization: Bearer {token}
Content-Type: application/json

{
  "message": "Additional information..."
}
```

### Close Ticket

```http
POST /api/tickets/{ticket}/close
Authorization: Bearer {token}
```

---

## Notifications Plugin

User notifications.

### Get Notifications

```http
GET /api/notifications
Authorization: Bearer {token}
```

### Mark as Read

```http
POST /api/notifications/{notification}/read
Authorization: Bearer {token}
```

### Mark All as Read

```http
POST /api/notifications/read-all
Authorization: Bearer {token}
```

### Get Unread Count

```http
GET /api/notifications/unread-count
Authorization: Bearer {token}
```

**Response:**

```json
{
  "count": 5
}
```

---

## Hospital Plugin

Health recovery system.

### Heal Self

```http
POST /api/hospital/heal
Authorization: Bearer {token}
```

### Buy Medical Supplies

```http
POST /api/hospital/supplies
Authorization: Bearer {token}
Content-Type: application/json

{
  "item_id": 1,
  "quantity": 5
}
```

### Get Hospital Status

```http
GET /api/hospital/status
Authorization: Bearer {token}
```

---

## Jail Plugin

Jail system and busting.

### Get Jail Status

```http
GET /api/jail/status
Authorization: Bearer {token}
```

### Bust Player

```http
POST /api/jail/bust/{user}
Authorization: Bearer {token}
```

### Pay Bail

```http
POST /api/jail/bail
Authorization: Bearer {token}
```

### List Jailed Players

```http
GET /api/jail/inmates
Authorization: Bearer {token}
```

---

## Stores Plugin

Item purchasing.

### List Stores

```http
GET /api/stores
Authorization: Bearer {token}
```

### Get Store Items

```http
GET /api/stores/{store}/items
Authorization: Bearer {token}
```

### Purchase Item

```http
POST /api/stores/{store}/purchase
Authorization: Bearer {token}
Content-Type: application/json

{
  "item_id": 5,
  "quantity": 1
}
```

---

## Next Steps

- [Authentication API](Authentication-API) - Auth endpoints
- [Admin API](Admin-API) - Admin panel endpoints
- [Hook System](Hook-System) - Plugin communication

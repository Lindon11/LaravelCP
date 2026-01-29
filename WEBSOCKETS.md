# WebSocket Real-Time Communication System

## Overview

The WebSocket system provides real-time bidirectional communication between the server and clients. Built with Ratchet for PHP and native WebSocket API for JavaScript, it enables instant notifications, live updates, and real-time interactions.

## Architecture

```
┌─────────────────┐     HTTP POST      ┌──────────────────┐
│  PHP Backend    │ ───────────────────>│  WebSocket       │
│  (Port 80)      │    (Port 8081)     │  Server          │
│                 │                     │  (Port 8080)     │
└─────────────────┘                     └──────────────────┘
                                               │
                                               │ ws://
                                               │
                                        ┌──────▼──────┐
                                        │  Browser    │
                                        │  Clients    │
                                        └─────────────┘
```

### Components

1. **WebSocket Server** (`websocket-server.php`)
   - Runs on port 8080 (WebSocket connections)
   - Runs on port 8081 (Internal HTTP API)
   - Handles client connections, authentication, channels
   - Built with Ratchet/React PHP

2. **Game WebSocket Handler** (`class/gameWebSocket.php`)
   - Manages connections and channels
   - Handles authentication via session tokens
   - Implements pub/sub pattern
   - Permission-based channel access

3. **Broadcaster Class** (`class/webSocketBroadcaster.php`)
   - PHP helper for sending messages from backend
   - Simple API for common events (kills, crimes, mail)
   - Communicates with WebSocket server via HTTP

4. **JavaScript Client** (`themes/default/js/websocket-client.js`)
   - Browser-side WebSocket client
   - Auto-reconnection with exponential backoff
   - Event system for message handling
   - Browser notifications integration

## Quick Start

### 1. Start the WebSocket Server

```bash
php websocket-server.php
```

You should see:
```
==========================================
Gangster Legends WebSocket Server
==========================================
Starting servers...

✓ WebSocket server: ws://0.0.0.0:8080
✓ Internal API: http://127.0.0.1:8081

Available channels:
  - global: Global announcements
  - user.{id}: User-specific notifications
  - gang.{id}: Gang chat and notifications
  - announcements: Admin announcements
```

### 2. Test in Browser

Open `http://your-domain/test-websocket.php` in your browser to access the test console.

### 3. Send Test Messages from PHP

```bash
php test-websocket-broadcast.php
```

## Channel System

### Public Channels
- **global**: All connected clients receive messages
- **announcements**: Admin announcements (read-only for users)

### Private Channels
- **user.{id}**: User-specific notifications (authenticated only)
- **gang.{id}**: Gang members only
- **admin.***: Admin users only

### Channel Permissions

```php
// User can only subscribe to their own user channel
user.1 → Only accessible by user ID 1

// Gang members can subscribe to their gang channel
gang.5 → Only accessible by members of gang ID 5

// Admin channels require admin role
admin.logs → Only accessible by admins
```

## PHP Integration

### Using the Broadcaster

```php
// Get broadcaster instance
$broadcaster = wsbroadcast(); // Helper function
// or
$broadcaster = WebSocketBroadcaster::getInstance();

// Send notification to user
$broadcaster->notifyUser(
    $userId, 
    'New Message', 
    'You have a new message from Admin',
    ['messageId' => 123]
);

// Broadcast kill event
$broadcaster->broadcastKill(
    $killerId,
    $killerName,
    $victimId,
    $victimName
);

// Broadcast crime result
$broadcaster->broadcastCrime(
    $userId,
    'robbery',
    true,  // success
    5000   // reward
);

// Send mail notification
$broadcaster->broadcastMail(
    $recipientId,
    $senderId,
    $senderName,
    'Message subject'
);

// Broadcast to gang
$broadcaster->broadcastToGang(
    $gangId,
    'Gang war started!',
    ['targetGang' => 'Rivals']
);

// Send announcement
$broadcaster->broadcastAnnouncement(
    'Server Maintenance',
    'Server will restart in 5 minutes'
);

// Money change notification
$broadcaster->broadcastMoneyChange(
    $userId,
    10000,
    'Crime reward'
);

// Generic broadcast
$broadcaster->broadcast('channel-name', [
    'type' => 'custom',
    'data' => 'your data'
]);
```

### Integration Examples

#### Kill Module
```php
// In your kill processing code
if ($killSuccess) {
    // ... existing kill logic ...
    
    // Send real-time notification
    wsbroadcast()->broadcastKill(
        $killer['id'],
        $killer['username'],
        $victim['id'],
        $victim['username']
    );
}
```

#### Crime Module
```php
// In your crime processing code
$success = rand(0, 100) < $crimeSuccess;

if ($success) {
    // ... add money, update stats ...
    
    wsbroadcast()->broadcastCrime(
        $userId,
        $crimeType,
        true,
        $reward
    );
} else {
    wsbroadcast()->broadcastCrime(
        $userId,
        $crimeType,
        false
    );
}
```

#### Mail Module
```php
// When sending a message
$db->query("INSERT INTO mail ...");

wsbroadcast()->broadcastMail(
    $recipientId,
    $senderId,
    $senderName,
    $subject
);
```

## JavaScript Integration

### Basic Setup

```html
<script src="/themes/default/js/websocket-client.js"></script>
<script>
// Create WebSocket instance
const ws = new GLWebSocket();

// Connect (with optional authentication)
ws.connect(userId, sessionToken);

// Subscribe to channels
ws.subscribe('global');
ws.subscribe('user.' + userId);
</script>
```

### Event Handlers

```javascript
// Notification event
ws.on('notification', (data) => {
    console.log('Notification:', data.title, data.message);
    // Show notification UI
});

// Kill event
ws.on('kill', (data) => {
    if (data.role === 'killer') {
        alert(`You killed ${data.victimName}!`);
    } else {
        alert(`You were killed by ${data.killerName}!`);
    }
});

// Crime event
ws.on('crime', (data) => {
    if (data.success) {
        showSuccess(`Crime successful! +$${data.reward}`);
    } else {
        showError('Crime failed!');
    }
});

// Mail event
ws.on('mail', (data) => {
    updateMailBadge();
    showNotification(`New mail from ${data.from}`);
});

// Money change
ws.on('money', (data) => {
    updateMoneyDisplay(data.amount);
    showFloatingText(data.message);
});

// Generic message handler
ws.on('message', (data) => {
    console.log('Received:', data);
});
```

### Connection Management

```javascript
// Connection state events
ws.on('connected', () => {
    console.log('Connected to server');
});

ws.on('disconnected', () => {
    console.log('Disconnected from server');
});

ws.on('reconnecting', (data) => {
    console.log(`Reconnecting (${data.attempt}/${data.maxAttempts})...`);
});

// Manual disconnect
ws.disconnect();

// Manual reconnect
ws.connect(userId, sessionToken);
```

### Channel Management

```javascript
// Subscribe to channel
ws.subscribe('gang.5');

// Unsubscribe from channel
ws.unsubscribe('gang.5');

// Send message to channel
ws.send('custom-channel', {
    type: 'chat',
    message: 'Hello everyone!'
});
```

## Message Formats

### Notification
```json
{
    "type": "notification",
    "title": "Welcome!",
    "message": "Welcome to Gangster Legends",
    "data": {},
    "timestamp": 1234567890
}
```

### Kill Event
```json
{
    "type": "kill",
    "killerId": 1,
    "killerName": "Boss",
    "victimId": 2,
    "victimName": "Target",
    "timestamp": 1234567890
}
```

### Crime Event
```json
{
    "type": "crime",
    "crimeType": "robbery",
    "success": true,
    "reward": 5000,
    "message": "Crime successful! +$5000",
    "timestamp": 1234567890
}
```

### Mail Event
```json
{
    "type": "mail",
    "from": "Admin",
    "senderId": 1,
    "subject": "Welcome",
    "message": "New message from Admin",
    "timestamp": 1234567890
}
```

### Money Change
```json
{
    "type": "money",
    "amount": 10000,
    "reason": "Crime reward",
    "message": "+10,000",
    "timestamp": 1234567890
}
```

## Running in Production

### Using Supervisor

Create `/etc/supervisor/conf.d/websocket.conf`:

```ini
[program:gangster-legends-websocket]
command=/usr/bin/php /var/www/html/websocket-server.php
directory=/var/www/html
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/websocket-server.log
```

Start the service:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start gangster-legends-websocket
```

### Using Systemd

Create `/etc/systemd/system/websocket.service`:

```ini
[Unit]
Description=Gangster Legends WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/html
ExecStart=/usr/bin/php /var/www/html/websocket-server.php
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable websocket
sudo systemctl start websocket
sudo systemctl status websocket
```

### Docker

Add to your `docker-compose.yml`:

```yaml
services:
  websocket:
    build: .
    command: php websocket-server.php
    ports:
      - "8080:8080"
    volumes:
      - ./:/var/www/html
    restart: always
```

## Security Considerations

1. **Authentication**: Session tokens verify user identity
2. **Channel Permissions**: Users can only access authorized channels
3. **Rate Limiting**: Implement rate limiting for message sending
4. **SSL/TLS**: Use `wss://` in production with SSL certificates
5. **Internal API**: Port 8081 should only be accessible from localhost

## Troubleshooting

### Server won't start
```bash
# Check if ports are in use
netstat -tulpn | grep 8080
netstat -tulpn | grep 8081

# Check PHP version (requires 7.4+)
php -v

# Check if composer dependencies are installed
composer install
```

### Clients can't connect
- Check firewall allows port 8080
- Verify WebSocket server is running
- Check browser console for errors
- Try test page: `/test-websocket.php`

### Messages not being delivered
```bash
# Test internal API
curl -X POST http://127.0.0.1:8081/broadcast \
  -H "Content-Type: application/json" \
  -d '{"channel":"global","data":{"type":"test","message":"hello"}}'

# Check server stats
curl http://127.0.0.1:8081/stats
```

### High memory usage
- Limit max connections in `gameWebSocket.php`
- Implement connection cleanup for idle clients
- Monitor with `top` or `htop`

## Performance Tips

1. **Connection Pooling**: Reuse WebSocket connections
2. **Message Batching**: Group multiple updates into single message
3. **Channel Optimization**: Use specific channels instead of broadcasting to all
4. **Heartbeat**: Implement ping/pong to detect dead connections
5. **Load Balancing**: Use multiple WebSocket servers behind a load balancer

## API Reference

### WebSocketBroadcaster Methods

- `notifyUser($userId, $title, $message, $data = [])`
- `broadcastKill($killerId, $killerName, $victimId, $victimName)`
- `broadcastCrime($userId, $crimeType, $success, $reward = 0)`
- `broadcastMail($recipientId, $senderId, $senderName, $subject)`
- `broadcastAnnouncement($title, $message)`
- `broadcastToGang($gangId, $message, $data = [])`
- `broadcastMoneyChange($userId, $amount, $reason)`
- `broadcast($channel, $data)`

### GLWebSocket Methods

- `connect(userId, sessionToken)`
- `disconnect()`
- `subscribe(channel)`
- `unsubscribe(channel)`
- `send(channel, data)`
- `on(event, callback)`
- `off(event, callback)`

## Examples

See:
- `/test-websocket.php` - Browser test console
- `/test-websocket-broadcast.php` - PHP broadcast examples

## Support

For issues or questions:
1. Check server logs: `tail -f /var/log/websocket-server.log`
2. Review browser console for client errors
3. Test with provided test scripts
4. Check that WebSocket server is running: `ps aux | grep websocket`

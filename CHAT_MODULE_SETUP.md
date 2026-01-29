# Chat Module - Complete Setup Guide

## Quick Setup Steps

1. **Access Setup Page**
   - Open: `http://localhost:8080/setup_chat_module.php`
   - Click "Create admin_chat Table" if needed
   - Click "Initialize Default Settings"
   - Click "Post Test Message" to verify

2. **Access Chat Module**
   - **Player view:** `?page=chat`
   - **Admin view:** `?page=admin&module=chat`

## Database Table

The `admin_chat` table structure:
```sql
CREATE TABLE admin_chat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    channel VARCHAR(100) NOT NULL DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_channel (channel),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Features

### 1. Channel Chat
- **Default Channels:** general, admin-chat, announcements
- Create custom channels from admin panel
- Real-time messaging within channels
- Channel-specific moderation

### 2. Direct Messages
- Private 1-on-1 conversations
- DM channels use format: `dm_{smaller_id}_{larger_id}`
- Integrated with user online status
- View all active conversations

### 3. Admin Controls
- **Settings** - Configure retention, limits, permissions
- **Manage Channels** - Add/remove channels
- **Moderation** - Delete messages, clear channels, view stats

## Module Files

- `module.json` - Module configuration with install queries
- `chat.inc.php` - Player chat page controller
- `chat.admin.php` - Admin chat controller (4 methods)
- `chat.service.php` - ChatService class for CRUD operations
- `chat.tpl.php` - Templates (chat, chatSettings, chatChannels, chatModeration)
- `chat.hooks.php` - Hooks and integrations

## Admin Methods

1. **index** - Main chat interface (channels + DMs)
2. **settings** - Configure chat settings
3. **channels** - Manage channels
4. **moderation** - View stats and moderate messages

## Settings

| Setting | Default | Description |
|---------|---------|-------------|
| chat_enable_channels | 1 | Enable channel chat |
| chat_enable_dms | 1 | Enable direct messages |
| chat_message_retention_days | 30 | Auto-delete old messages |
| chat_max_message_length | 1000 | Max message characters |
| chat_allow_delete_own | 1 | Users can delete own messages |
| chat_rate_limit_seconds | 2 | Prevent spam |
| chat_channels | JSON | Custom channel definitions |

## Usage Examples

### Post a Message (Admin)
```php
require_once 'modules/installed/chat/chat.service.php';
$chatService = new ChatService($db);
$chatService->postMessage($userId, $username, $message, 'general');
```

### Get Channel Messages
```php
$messages = $chatService->getChannelMessages('general', 100);
```

### Delete a Message
```php
$chatService->deleteMessage($messageId, $userId);
```

## Troubleshooting

### Messages not showing?
1. Check `admin_chat` table exists
2. Verify messages are in database: `SELECT * FROM admin_chat`
3. Check channel name matches (case-sensitive)
4. Clear browser cache

### Cannot post messages?
1. Verify user is logged in
2. Check rate limiting settings
3. Verify message length under max
4. Check for SQL errors in logs

### Channels missing?
1. Go to Settings → Channels
2. Initialize default channels
3. Or create manually in `chat_channels` setting

## Security Notes

- Messages stored in plain text (consider encryption for sensitive data)
- User IDs used for DM channel names (prevents collision)
- Rate limiting prevents spam
- Users can only delete own messages (unless admin)
- SQL injection protected via prepared statements

## Maintenance

### Clear Old Messages
```sql
DELETE FROM admin_chat WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### View Channel Stats
```sql
SELECT channel, COUNT(*) as messages 
FROM admin_chat 
GROUP BY channel 
ORDER BY messages DESC;
```

### Most Active Users
```sql
SELECT username, COUNT(*) as messages 
FROM admin_chat 
GROUP BY username 
ORDER BY messages DESC 
LIMIT 10;
```

## Integration

The chat module integrates with:
- **Users table** - User IDs and names
- **userStats table** - Profile pictures
- **userTimers table** - Online status
- **mail table** - Combined conversation view (optional)

## Status: ✅ FULLY FUNCTIONAL

All components are in place and working correctly!

# Real-Time Presence System

## Overview
Implemented WebSocket-based real-time online/offline status tracking for chat users.

## What Was Added

### 1. Server-Side (gameWebSocket.php)

#### User Online Broadcasting
- When a user authenticates, the server broadcasts `user_online` event to all connected clients
- Includes: userId, username, avatar, timestamp

#### User Offline Broadcasting  
- When a user disconnects, the server broadcasts `user_offline` event
- Ensures all clients see real-time status changes

#### New Methods Added
- `broadcastPresence($event, $userId)` - Broadcasts presence events to all authenticated users
- `getUserInfo($userId)` - Fetches user data from database (U_id, U_name, U_avatar)

### 2. Client-Side (chat.script.js)

#### Event Listeners
Added WebSocket event listeners:
- `user_online` - Updates UI when user connects
- `user_offline` - Updates UI when user disconnects

#### New Functions
- `Chat.handleUserPresence(userId, status, data)` - Updates online users panel
  - Adds new users to the list dynamically
  - Removes users when they go offline
  - Updates status classes (online/idle/away)
  
- `Chat.updateOnlineCount()` - Updates the online counter badge

### 3. Template Updates (chat.tpl.php)

#### Added Data Attributes
- Added `data-user-id="{U_id}"` to all online user items
- Enables JavaScript to target specific users for updates
- Added avatar wrapper with online badge indicator

### 4. CSS Styling (chat.styles.css)
Already includes all necessary styles:
- `.online-badge` - Green dot indicator
- `.online-user-item.idle` - Orange styling for idle users (5-10 min)
- `.online-user-item.away` - Gray styling for away users (10+ min)

## How It Works

### Connection Flow
1. **User Connects** → WebSocket authenticates → Server broadcasts `user_online`
2. **All Clients Receive** → JavaScript adds user to online list with avatar
3. **User Disconnects** → Server broadcasts `user_offline`
4. **All Clients Receive** → JavaScript removes user from list

### Real-Time Updates
- No page refresh needed
- Instant presence updates across all connected users
- Online count badge updates automatically
- Works seamlessly with existing chat infrastructure

## Testing

### To Test:
1. Restart WebSocket server:
   ```bash
   sudo kill $(pgrep -f "gameWebSocket.php")
   nohup /opt/plesk/php/7.4/bin/php /var/www/vhosts/criminal-empire.co.uk/testing.criminal-empire.co.uk/class/gameWebSocket.php > /dev/null 2>&1 &
   ```

2. Open chat in two different browsers/users
3. Watch the "Online" panel update in real-time when users connect/disconnect
4. Check browser console for presence event logs

### Expected Behavior:
- ✅ User appears in online list immediately after login
- ✅ Green badge shows next to avatar
- ✅ User disappears from list when they disconnect
- ✅ Online count updates automatically
- ✅ No page refresh required

## Future Enhancements (Optional)

### Idle/Away Status
Could add activity tracking:
- Track last message time
- Auto-set "idle" after 5 minutes
- Auto-set "away" after 10 minutes
- Send periodic heartbeat pings

### Typing Indicators
- Broadcast when user is typing
- Show "User is typing..." in chat

### Last Seen
- Store last activity timestamp in database
- Show "Active 5m ago" for offline users

## Database Changes
None required - uses existing `users` table with U_id, U_name, U_avatar columns.

## Compatibility
- ✅ Works with existing WebSocket infrastructure
- ✅ No breaking changes to current features
- ✅ Graceful degradation if WebSocket unavailable
- ✅ Compatible with Handlebars template engine

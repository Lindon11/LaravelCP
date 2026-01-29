# Webhook System

The webhook system allows you to receive real-time notifications when events occur in your game. This is perfect for integrating with Discord, Slack, external dashboards, or custom applications.

## Features

✅ **Event-Driven**: Subscribe to 19+ game events  
✅ **Secure**: HMAC-SHA256 signature verification  
✅ **Reliable**: Automatic retry with exponential backoff (4 attempts)  
✅ **Monitored**: Full delivery logs and statistics  
✅ **Testable**: Built-in test delivery function  

## Available Events

| Event | Description |
|-------|-------------|
| `user.registered` | New user signs up |
| `user.killed` | User was killed |
| `user.kill` | User killed someone |
| `user.jailed` | User was sent to jail |
| `user.released` | User released from jail |
| `user.hospitalized` | User sent to hospital |
| `crime.committed` | Any crime attempt |
| `crime.success` | Successful crime |
| `crime.failed` | Failed crime |
| `money.transferred` | Money sent between users |
| `bank.deposit` | Money deposited |
| `bank.withdrawal` | Money withdrawn |
| `gang.created` | New gang created |
| `gang.joined` | User joined a gang |
| `gang.left` | User left a gang |
| `property.purchased` | Property bought |
| `car.purchased` | Car bought |
| `message.sent` | Private message sent |
| `admin.action` | Admin performed action |

## Creating a Webhook

### Via Admin Panel

1. Go to **Admin Panel → API Manager → Webhooks**
2. Click **"Create Webhook"**
3. Fill in:
   - **Name**: Descriptive name (e.g., "Discord Kills")
   - **URL**: Your endpoint (e.g., `https://discord.com/api/webhooks/...`)
   - **Event**: Select event to subscribe to
   - **Active**: Enable immediately
   - **Retries**: Enable automatic retry on failure
4. **Save the secret key** - shown only once!

### Webhook Payload Format

```json
{
  "event": "user.killed",
  "timestamp": 1705540800,
  "data": {
    "killer": {
      "id": 123,
      "name": "PlayerName",
      "stats": 5000
    },
    "victim": {
      "id": 456,
      "name": "VictimName",
      "stats": 3000
    }
  }
}
```

### Headers Sent

```
Content-Type: application/json
X-Webhook-Signature: abc123...
X-Webhook-Event: user.killed
User-Agent: GangsterLegends-Webhook/1.0
```

## Verifying Webhook Signatures

### Node.js

```javascript
const crypto = require('crypto');

function verifyWebhook(req, secret) {
    const signature = req.headers['x-webhook-signature'];
    const payload = JSON.stringify(req.body);
    
    const expectedSignature = crypto
        .createHmac('sha256', secret)
        .update(payload)
        .digest('hex');
    
    return signature === expectedSignature;
}

// Express example
app.post('/webhook', (req, res) => {
    if (!verifyWebhook(req, 'your-webhook-secret')) {
        return res.status(401).send('Invalid signature');
    }
    
    const { event, data } = req.body;
    console.log(`Received ${event}:`, data);
    
    res.status(200).send('OK');
});
```

### PHP

```php
function verifyWebhook($payload, $signature, $secret) {
    $expectedSignature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($expectedSignature, $signature);
}

// Usage
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
$secret = 'your-webhook-secret';

if (!verifyWebhook($payload, $signature, $secret)) {
    http_response_code(401);
    exit('Invalid signature');
}

$data = json_decode($payload, true);
// Process webhook...
```

### Python

```python
import hmac
import hashlib
import json

def verify_webhook(payload, signature, secret):
    expected_signature = hmac.new(
        secret.encode(),
        payload.encode(),
        hashlib.sha256
    ).hexdigest()
    
    return hmac.compare_digest(signature, expected_signature)

# Flask example
from flask import Flask, request

@app.route('/webhook', methods=['POST'])
def webhook():
    signature = request.headers.get('X-Webhook-Signature')
    payload = request.get_data(as_text=True)
    
    if not verify_webhook(payload, signature, 'your-webhook-secret'):
        return 'Invalid signature', 401
    
    data = json.loads(payload)
    print(f"Received {data['event']}: {data['data']}")
    
    return 'OK', 200
```

## Retry Logic

If webhook delivery fails (non-2xx response or timeout), the system automatically retries:

- **Attempt 1**: Immediate
- **Attempt 2**: 5 minutes later
- **Attempt 3**: 15 minutes later
- **Attempt 4**: 1 hour later
- **Attempt 5**: 6 hours later

After 4 failed attempts, delivery is abandoned.

## Setup Cron Job

To process retries, add to crontab:

```bash
*/5 * * * * php /var/www/html/cron/process_webhook_retries.php
```

## Triggering Webhooks from Code

```php
require_once 'class/webhook.php';

$webhook = new Webhook();

// Trigger a webhook
$webhook->trigger('user.killed', [
    'killer' => [
        'id' => 123,
        'name' => 'PlayerName'
    ],
    'victim' => [
        'id' => 456,
        'name' => 'VictimName'
    ]
]);
```

See `webhook_examples.php` for complete integration examples.

## Testing Webhooks

### Via Admin Panel

1. Go to **Webhooks** list
2. Click **"Test"** button next to webhook
3. A test payload will be sent immediately

### Test Payload

```json
{
  "event": "your.event",
  "timestamp": 1705540800,
  "data": {
    "test": true,
    "message": "This is a test webhook delivery"
  }
}
```

## Monitoring

### Delivery Stats

Each webhook tracks:
- **Total Deliveries**: All attempts
- **Successful**: 2xx responses
- **Failed**: Non-2xx or errors
- **Last Delivery**: Timestamp of last attempt
- **24h Activity**: Recent delivery count

### Delivery Logs

View detailed logs:
1. Go to webhook list
2. Click **"Logs"** button
3. See: timestamp, HTTP code, response time, errors

Logs include:
- Event name
- HTTP response code
- Response time (ms)
- Error messages
- Full response body (truncated to 1000 chars)

## Discord Integration Example

```php
// Create a Discord webhook
// URL: https://discord.com/api/webhooks/YOUR_WEBHOOK_ID/YOUR_TOKEN

// In your kill module
function notifyDiscordKill($killer, $victim) {
    $webhook = new Webhook();
    $webhook->trigger('user.killed', [
        'killer' => ['name' => $killer->info->U_name],
        'victim' => ['name' => $victim->info->U_name]
    ]);
}

// Discord endpoint transforms payload to Discord format
// POST https://your-server.com/discord-proxy
{
    "content": "💀 **PlayerName** killed **VictimName**"
}
```

## Troubleshooting

**Webhook not firing:**
- Check webhook is **Active**
- Verify event is being triggered in code
- Check delivery logs for errors

**Authentication failures:**
- Verify signature verification code
- Ensure using correct secret
- Check payload is JSON string (not object)

**Timeouts:**
- Endpoint must respond within 30 seconds
- Return 2xx status code quickly
- Process data asynchronously if needed

**Retries not working:**
- Ensure cron job is running
- Check `webhook_retries` table
- Verify **Retry Enabled** is checked

## Security Best Practices

1. **Always verify signatures** - Never trust unverified webhooks
2. **Use HTTPS** - Webhook URLs should use SSL
3. **Keep secrets secure** - Store in environment variables
4. **Rate limit** - Protect your endpoint from abuse
5. **Validate payloads** - Check expected fields exist
6. **Log everything** - Monitor for suspicious activity

## API Endpoints

Webhooks can also be managed via API:

```bash
# List webhooks
GET /api/v1/webhooks

# Create webhook
POST /api/v1/webhooks

# Update webhook
PUT /api/v1/webhooks/{id}

# Delete webhook
DELETE /api/v1/webhooks/{id}

# Test webhook
POST /api/v1/webhooks/{id}/test
```

## Database Schema

### api_webhooks
- `id`, `name`, `url`, `event`, `secret`
- `is_active`, `retry_enabled`
- `total_deliveries`, `successful_deliveries`, `failed_deliveries`
- `last_delivery_at`, `last_success_at`, `last_failure_at`
- `created_at`

### webhook_delivery_logs
- `id`, `webhook_id`, `event`, `payload`
- `http_code`, `response`, `error_message`
- `response_time`, `success`, `created_at`

### webhook_retries
- `id`, `webhook_id`, `payload`, `attempt_number`
- `scheduled_at`, `processed`, `created_at`

## Support

For issues or questions about webhooks, check the delivery logs in the admin panel for detailed error information.

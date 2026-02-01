# Frontend Error Logging Integration

## Overview
OpenPBBG now logs all JavaScript errors, Vue component errors, and API failures to the LaravelCP backend for admin monitoring.

## Features

### 1. **Automatic Error Capture**
- Unhandled JavaScript errors
- Unhandled promise rejections
- Vue component errors
- API errors (500+ status codes)

### 2. **Rate Limiting**
- Max 10 errors per minute to prevent spam
- Protects backend from error storms

### 3. **Error Grouping**
- Duplicate errors are grouped together
- Tracks occurrence count
- Records last seen timestamp

### 4. **Captured Data**
- Error message and stack trace
- File, line, and column numbers
- Current URL and user agent
- Component name (for Vue errors)
- Request/response data (for API errors)
- User ID (if authenticated)

## API Endpoints

### Log JavaScript Error
```
POST /api/log-frontend-error
{
  "message": "Cannot read property 'x' of undefined",
  "source": "main.js",
  "line": 42,
  "column": 15,
  "stack": "Error: ...",
  "url": "https://game.com/dashboard",
  "user_agent": "Mozilla/5.0...",
  "severity": "error"
}
```

### Log API Error
```
POST /api/log-api-error
{
  "endpoint": "/api/crimes/attempt",
  "method": "POST",
  "status_code": 500,
  "error_message": "Internal Server Error",
  "request_data": {...},
  "response_data": {...}
}
```

### Log Vue Error
```
POST /api/log-vue-error
{
  "error": "Cannot read property...",
  "component": "CrimesView",
  "hook": "mounted",
  "info": "..."
}
```

## Usage in OpenPBBG

### Automatic (Already Configured)
The error logger is automatically set up in `main.ts` and will catch all errors.

### Manual Error Logging
```javascript
// In any Vue component
this.$errorLogger.logError(
  new Error('Something went wrong'),
  'MyComponent.vue',
  42,
  0
)

// Or directly
import { errorLogger } from '@/plugins/errorLogger'
errorLogger.logError(error, source, line, column)
```

### API Error Logging
Automatically logs API errors via Axios interceptor:
```javascript
// Happens automatically for all axios requests
axios.post('/api/something', data)
  .catch(error => {
    // Error is automatically logged if status >= 500
  })
```

## Admin Monitoring

View errors in LaravelCP admin:
```
GET /api/admin/error-logs?type=FrontendError
GET /api/admin/error-logs?type=VueComponentError
GET /api/admin/error-logs?type=FrontendApiError
```

## Files Modified

### LaravelCP
1. `app/Http/Controllers/Api/FrontendErrorController.php` - Error logging controller
2. `routes/api.php` - Added 3 public error logging endpoints

### OpenPBBG
1. `src/plugins/errorLogger.js` - Error logging plugin (NEW)
2. `src/main.ts` - Integrated error logger

## Benefits

✅ **Proactive Monitoring** - Catch frontend errors before users report them
✅ **Better Debugging** - Full stack traces and context
✅ **User Experience** - See what errors real users encounter
✅ **API Reliability** - Track API failures from client perspective
✅ **Vue Performance** - Catch component lifecycle issues

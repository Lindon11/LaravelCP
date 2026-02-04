// errorLogger.js - Frontend Error Logging Plugin for LaravelCP Admin
import api from '@/services/api'

const API_BASE = '/api'

class ErrorLogger {
  constructor() {
    this.isEnabled = true
    this.maxErrorsPerMinute = 10
    this.errorCount = 0
    this.lastResetTime = Date.now()
    this.source = 'admin' // Identifies this as admin panel
  }

  /**
   * Check if we should log this error (rate limiting)
   */
  shouldLog() {
    const now = Date.now()

    // Reset counter every minute
    if (now - this.lastResetTime > 60000) {
      this.errorCount = 0
      this.lastResetTime = now
    }

    // Check if we're over the limit
    if (this.errorCount >= this.maxErrorsPerMinute) {
      console.warn('Error logging rate limit reached')
      return false
    }

    this.errorCount++
    return this.isEnabled
  }

  /**
   * Log JavaScript error
   */
  async logError(error, source = 'unknown', line = 0, column = 0) {
    if (!this.shouldLog()) return

    try {
      await api.post('/log-frontend-error', {
        message: error.message || String(error),
        source: source,
        line: line,
        column: column,
        stack: error.stack || null,
        url: window.location.href,
        user_agent: navigator.userAgent,
        severity: 'error',
        component: 'LaravelCP-Admin'
      })
    } catch (err) {
      console.error('Failed to log error:', err)
    }
  }

  /**
   * Log API error
   */
  async logApiError(endpoint, method, statusCode, errorMessage, requestData = null, responseData = null) {
    if (!this.shouldLog()) return

    // Don't log auth errors (401/403) - these are expected
    if (statusCode === 401 || statusCode === 403) return

    try {
      await api.post('/log-api-error', {
        endpoint: endpoint,
        method: method,
        status_code: statusCode,
        error_message: errorMessage,
        request_data: requestData,
        response_data: responseData,
        source: 'admin'
      })
    } catch (err) {
      console.error('Failed to log API error:', err)
    }
  }

  /**
   * Log Vue component error
   */
  async logVueError(error, component, hook = null, info = null) {
    if (!this.shouldLog()) return

    try {
      await api.post('/log-vue-error', {
        error: error.message || String(error),
        component: `Admin:${component}`,
        hook: hook,
        info: info,
        stack: error.stack || null,
        url: window.location.href,
        source: 'admin'
      })
    } catch (err) {
      console.error('Failed to log Vue error:', err)
    }
  }

  /**
   * Log custom event/warning
   */
  async logWarning(message, context = {}) {
    if (!this.shouldLog()) return

    try {
      await api.post('/log-frontend-error', {
        message: message,
        source: 'admin-warning',
        severity: 'warning',
        url: window.location.href,
        user_agent: navigator.userAgent,
        component: 'LaravelCP-Admin',
        context: context
      })
    } catch (err) {
      console.error('Failed to log warning:', err)
    }
  }
}

const errorLogger = new ErrorLogger()

/**
 * Vue Plugin
 */
export default {
  install(app) {
    // Global error handler for Vue
    app.config.errorHandler = (err, instance, info) => {
      console.error('Vue Error:', err, info)

      const componentName = instance?.$options?.name ||
                           instance?.$options?.__name ||
                           'UnknownComponent'

      errorLogger.logVueError(err, componentName, null, info)
    }

    // Global warning handler
    app.config.warnHandler = (msg, instance, trace) => {
      console.warn('Vue Warning:', msg)
      // Optionally log warnings (disabled by default - too noisy)
      // errorLogger.logWarning(msg, { trace })
    }

    // Make error logger available globally
    app.config.globalProperties.$errorLogger = errorLogger
  }
}

/**
 * Setup global error handlers
 */
export function setupGlobalErrorHandlers() {
  // Catch unhandled errors
  window.addEventListener('error', (event) => {
    console.error('Unhandled Error:', event.error)

    errorLogger.logError(
      event.error || new Error(event.message),
      event.filename,
      event.lineno,
      event.colno
    )
  })

  // Catch unhandled promise rejections
  window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled Promise Rejection:', event.reason)

    errorLogger.logError(
      new Error(`Unhandled Promise Rejection: ${event.reason}`),
      'promise',
      0,
      0
    )
  })
}

/**
 * Axios interceptor for API errors
 */
export function setupAxiosErrorInterceptor(axiosInstance) {
  axiosInstance.interceptors.response.use(
    response => response,
    error => {
      // Only log server errors (5xx), not client errors
      if (error.response && error.response.status >= 500) {
        errorLogger.logApiError(
          error.config?.url || 'unknown',
          error.config?.method?.toUpperCase() || 'GET',
          error.response.status,
          error.response.data?.message || error.message,
          null, // Don't log request data for security
          error.response?.data || null
        )
      }

      return Promise.reject(error)
    }
  )
}

export { errorLogger }

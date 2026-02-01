// errorLogger.js - Frontend Error Logging Plugin for OpenPBBG
import axios from 'axios'

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

class ErrorLogger {
  constructor() {
    this.isEnabled = true
    this.maxErrorsPerMinute = 10
    this.errorCount = 0
    this.lastResetTime = Date.now()
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
      await axios.post(`${API_BASE}/log-frontend-error`, {
        message: error.message || String(error),
        source: source,
        line: line,
        column: column,
        stack: error.stack || null,
        url: window.location.href,
        user_agent: navigator.userAgent,
        severity: 'error'
      }, {
        headers: { 'Content-Type': 'application/json' }
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

    try {
      await axios.post(`${API_BASE}/log-api-error`, {
        endpoint: endpoint,
        method: method,
        status_code: statusCode,
        error_message: errorMessage,
        request_data: requestData,
        response_data: responseData
      }, {
        headers: { 'Content-Type': 'application/json' }
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
      await axios.post(`${API_BASE}/log-vue-error`, {
        error: error.message || String(error),
        component: component,
        hook: hook,
        info: info
      }, {
        headers: { 'Content-Type': 'application/json' }
      })
    } catch (err) {
      console.error('Failed to log Vue error:', err)
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
      // Don't log warnings to backend (too noisy)
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
      // Only log actual errors, not auth failures
      if (error.response && error.response.status >= 500) {
        errorLogger.logApiError(
          error.config?.url || 'unknown',
          error.config?.method?.toUpperCase() || 'GET',
          error.response.status,
          error.response.data?.message || error.message,
          error.config?.data ? JSON.parse(error.config.data) : null,
          error.response?.data || null
        )
      }
      
      return Promise.reject(error)
    }
  )
}

export { errorLogger }

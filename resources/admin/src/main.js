import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/main.css'
import errorLoggerPlugin, { setupGlobalErrorHandlers, setupAxiosErrorInterceptor } from './plugins/errorLogger.js'
import api from './services/api'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(errorLoggerPlugin)

// Setup global error handlers
setupGlobalErrorHandlers()

// Setup API error interceptor
setupAxiosErrorInterceptor(api)

app.mount('#app')

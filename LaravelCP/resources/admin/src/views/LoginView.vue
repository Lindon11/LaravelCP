<template>
  <div class="login-page">
    <div class="login-container">
      <div class="login-header">
        <h1>⚙️ Admin Control Panel</h1>
        <p>LaravelCP Management System</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <div class="form-group">
          <label>Email or Username</label>
          <input
            v-model="credentials.login"
            type="text"
            placeholder="Enter your credentials"
            required
          />
        </div>

        <div class="form-group">
          <label>Password</label>
          <input
            v-model="credentials.password"
            type="password"
            placeholder="Enter your password"
            required
          />
        </div>

        <button type="submit" class="login-btn" :disabled="loading">
          <span v-if="loading">Logging in...</span>
          <span v-else>🔐 Sign In</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const credentials = ref({
  login: '',
  password: ''
})

const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  error.value = ''
  loading.value = true

  try {
    const response = await api.post('/login', credentials.value)
    
    localStorage.setItem('admin_token', response.data.token)
    localStorage.setItem('admin_user', JSON.stringify(response.data.user))
    
    router.push('/dashboard')
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed. Please check your credentials.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
  padding: 2rem;
}

.login-container {
  width: 100%;
  max-width: 450px;
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 1rem;
  padding: 3rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.login-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.login-header h1 {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  color: #ffffff;
}

.login-header p {
  color: #94a3b8;
  font-size: 0.95rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.error-message {
  padding: 1rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid #ef4444;
  border-radius: 0.5rem;
  color: #fca5a5;
  font-size: 0.875rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  color: #cbd5e1;
  font-weight: 600;
  font-size: 0.875rem;
}

.form-group input {
  padding: 0.875rem 1rem;
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  color: #ffffff;
  font-size: 1rem;
  transition: all 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.login-btn {
  padding: 1rem;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border: none;
  border-radius: 0.5rem;
  color: #ffffff;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
}

.login-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>

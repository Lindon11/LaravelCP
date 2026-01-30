<template>
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <h2 class="register-title">Create Account</h2>
        <p class="register-subtitle">Join Gangster Legends today</p>
      </div>
      
      <form class="register-form" @submit.prevent="handleRegister">
        <div v-if="authStore.error" class="error-message">
          {{ authStore.error }}
        </div>

        <div class="form-fields">
          <div>
            <input
              v-model="form.username"
              type="text"
              required
              class="form-input"
              placeholder="Username"
            />
          </div>
          <div>
            <input
              v-model="form.email"
              type="email"
              required
              class="form-input"
              placeholder="Email"
            />
          </div>
          <div>
            <input
              v-model="form.password"
              type="password"
              required
              minlength="8"
              class="form-input"
              placeholder="Password (min 8 characters)"
            />
          </div>
          <div>
            <input
              v-model="form.password_confirmation"
              type="password"
              required
              class="form-input"
              placeholder="Confirm Password"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="authStore.loading"
            class="submit-btn"
          >
            <span v-if="authStore.loading">Creating account...</span>
            <span v-else>Create Account</span>
          </button>
        </div>

        <div class="login-link">
          <router-link to="/login">
            Already have an account? Sign in
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  username: '',
  email: '',
  password: '',
  password_confirmation: ''
})

async function handleRegister() {
  const success = await authStore.register(form.value)
  if (success) {
    router.push('/dashboard')
  }
}
</script>

<style scoped>
.register-container {
  min-height: 100vh;
  background-color: #111827;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.register-card {
  max-width: 28rem;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.register-header {
  text-align: center;
}

.register-title {
  margin-top: 1.5rem;
  font-size: 1.875rem;
  font-weight: 800;
  color: white;
}

.register-subtitle {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #9ca3af;
}

.register-form {
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.error-message {
  background-color: rgba(127, 29, 29, 0.5);
  border: 1px solid #ef4444;
  color: #fecaca;
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
}

.form-fields {
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-input {
  appearance: none;
  border-radius: 0.5rem;
  position: relative;
  display: block;
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #374151;
  background-color: #1f2937;
  color: white;
  font-size: 0.875rem;
}

.form-input::placeholder {
  color: #9ca3af;
}

.form-input:focus {
  outline: none;
  ring: 1px solid #06b6d4;
  border-color: #06b6d4;
}

.submit-btn {
  position: relative;
  width: 100%;
  display: flex;
  justify-content: center;
  padding: 0.5rem 1rem;
  border: 1px solid transparent;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 0.375rem;
  color: white;
  background-color: #0891b2;
  cursor: pointer;
  transition: background-color 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background-color: #0e7490;
}

.submit-btn:focus {
  outline: none;
  ring: 2px solid #06b6d4;
  ring-offset: 2px;
}

.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.login-link {
  text-align: center;
}

.login-link a {
  color: #22d3ee;
  font-size: 0.875rem;
  text-decoration: none;
  transition: color 0.2s;
}

.login-link a:hover {
  color: #67e8f9;
}
</style>

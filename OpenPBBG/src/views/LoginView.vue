<template>
  <div class="login-container">
    <!-- Left Side - Branding -->
    <div class="branding-side">
      <div class="pattern-overlay">
        <div class="pattern-bg" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIj48cGF0aCBkPSJNMzYgMzRjMC0yLjIxLTEuNzktNC00LTRzLTQgMS43OS00IDQgMS43OSA0IDQgNCA0LTEuNzkgNC00em0wLTEwYzAtMi4yMS0xLjc5LTQtNC00cy00IDEuNzktNCA0IDEuNzkgNCA0IDQgNC0xLjc5IDQtNHptMC0xMGMwLTIuMjEtMS43OS00LTQtNHMtNCAxLjc5LTQgNCAxLjc5IDQgNCA0IDQtMS43OSA0LTR6TTI2IDM0YzAtMi4yMS0xLjc5LTQtNC00cy00IDEuNzktNCA0IDEuNzkgNCA0IDQgNC0xLjc5IDQtNHptMC0xMGMwLTIuMjEtMS43OS00LTQtNHMtNCAxLjc5LTQgNCAxLjc5IDQgNCA0IDQtMS43OSA0LTR6bTAtMTBjMC0yLjIxLTEuNzktNC00LTRzLTQgMS43OS00IDQgMS43OSA0IDQgNCA0LTEuNzkgNC00ek00NiAzNGMwLTIuMjEtMS43OS00LTQtNHMtNCAxLjc5LTQgNCAxLjc5IDQgNCA0IDQtMS43OSA0LTR6bTAtMTBjMC0yLjIxLTEuNzktNC00LTRzLTQgMS43OS00IDQgMS43OSA0IDQgNCA0LTEuNzkgNC00em0wLTEwYzAtMi4yMS0xLjc5LTQtNC00cy00IDEuNzktNCA0IDEuNzkgNCA0IDQgNC0xLjc5IDQtNHoiLz48L2c+PC9nPjwvc3ZnPg==')"></div>
      </div>
      <div class="branding-content">
        <h1 class="branding-title">🎲 OpenPBBG</h1>
        <p class="branding-subtitle">Build Your Criminal Empire</p>
        <div class="features">
          <div class="feature-item">
            <span class="feature-icon">💰</span>
            <div>
              <h3 class="feature-title">Commit Crimes</h3>
              <p class="feature-desc">Earn cash, gain experience, and climb the ranks</p>
            </div>
          </div>
          <div class="feature-item">
            <span class="feature-icon">⚔️</span>
            <div>
              <h3 class="feature-title">Battle Players</h3>
              <p class="feature-desc">Attack rivals and defend your turf</p>
            </div>
          </div>
          <div class="feature-item">
            <span class="feature-icon">👥</span>
            <div>
              <h3 class="feature-title">Form Gangs</h3>
              <p class="feature-desc">Team up with others to dominate the streets</p>
            </div>
          </div>
          <div class="feature-item">
            <span class="feature-icon">🏆</span>
            <div>
              <h3 class="feature-title">Rule the City</h3>
              <p class="feature-desc">Rise from Thug to Godfather</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="form-side">
      <div class="form-container">
        <!-- Mobile Logo -->
        <div class="mobile-logo">
          <h1 class="mobile-title">🎲 OpenPBBG</h1>
          <p class="mobile-subtitle">Build Your Criminal Empire</p>
        </div>

        <div class="welcome-header">
          <h2 class="welcome-title">Welcome Back</h2>
          <p class="welcome-subtitle">Sign in to continue your criminal empire</p>
        </div>

        <div v-if="authStore.error" class="error-message">
          <p>{{ authStore.error }}</p>
        </div>

        <form class="login-form" @submit.prevent="handleLogin">
          <div class="form-fields">
            <div class="form-group">
              <label for="email" class="form-label">Email Address</label>
              <input
                id="email"
                v-model="form.email"
                type="text"
                required
                autofocus
                autocomplete="username"
                class="form-input"
                placeholder="Enter your email"
              />
            </div>

            <div class="form-group">
              <label for="password" class="form-label">Password</label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                required
                autocomplete="current-password"
                class="form-input"
                placeholder="Enter your password"
              />
            </div>
          </div>

          <div class="form-options">
            <div class="remember-me">
              <input
                id="remember"
                v-model="form.remember"
                type="checkbox"
                class="checkbox-input"
              />
              <label for="remember" class="checkbox-label">Remember me</label>
            </div>

            <div class="forgot-link">
              <a href="#">Forgot password?</a>
            </div>
          </div>

          <div class="submit-container">
            <button type="submit" :disabled="authStore.loading" class="submit-btn">
              <span class="btn-icon">
                <svg v-if="!authStore.loading" class="lock-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="spinner-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="spinner-circle" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="spinner-path" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              {{ authStore.loading ? 'Signing in...' : 'Sign in' }}
            </button>
          </div>

          <div class="register-link">
            <p>
              Don't have an account?
              <router-link to="/register">Create one now</router-link>
            </p>
          </div>
        </form>
      </div>
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
  email: '',
  password: '',
  remember: false
})

async function handleLogin() {
  const success = await authStore.login({
    login: form.value.email,
    password: form.value.password
  })
  if (success) {
    router.push('/dashboard')
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
}

/* Left Side - Branding */
.branding-side {
  display: none;
  width: 50%;
  background: linear-gradient(to bottom right, #7f1d1d, #1f2937, #000000);
  position: relative;
  overflow: hidden;
}

@media (min-width: 1024px) {
  .branding-side {
    display: flex;
  }
}

.pattern-overlay {
  position: absolute;
  inset: 0;
  opacity: 0.1;
}

.pattern-bg {
  position: absolute;
  inset: 0;
  background-repeat: repeat;
}

.branding-content {
  position: relative;
  z-index: 10;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 0 3rem;
  color: white;
}

.branding-title {
  font-size: 3rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.branding-subtitle {
  font-size: 1.5rem;
  margin-bottom: 2rem;
  color: #d1d5db;
}

.features {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  font-size: 1.125rem;
}

.feature-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.feature-icon {
  color: #ef4444;
  font-size: 1.5rem;
}

.feature-title {
  font-weight: 600;
}

.feature-desc {
  color: #9ca3af;
}

/* Right Side - Form */
.form-side {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #111827;
  padding: 1rem 1.5rem;
}

@media (min-width: 640px) {
  .form-side {
    padding: 1.5rem;
  }
}

@media (min-width: 1024px) {
  .form-side {
    padding: 2rem;
  }
}

.form-container {
  max-width: 28rem;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.mobile-logo {
  text-align: center;
}

@media (min-width: 1024px) {
  .mobile-logo {
    display: none;
  }
}

.mobile-title {
  font-size: 2.25rem;
  font-weight: bold;
  color: white;
}

.mobile-subtitle {
  margin-top: 0.5rem;
  color: #9ca3af;
}

.welcome-header {
  color: white;
}

.welcome-title {
  font-size: 1.875rem;
  font-weight: 800;
}

.welcome-subtitle {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #9ca3af;
}

.error-message {
  border-radius: 0.375rem;
  background-color: rgba(127, 29, 29, 0.2);
  padding: 1rem;
  border: 1px solid #991b1b;
}

.error-message p {
  font-size: 0.875rem;
  font-weight: 500;
  color: #fca5a5;
}

.login-form {
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #d1d5db;
  margin-bottom: 0.25rem;
}

.form-input {
  appearance: none;
  position: relative;
  display: block;
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #4b5563;
  border-radius: 0.5rem;
  background-color: #1f2937;
  color: white;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.form-input::placeholder {
  color: #6b7280;
}

.form-input:focus {
  outline: none;
  ring: 2px solid #ef4444;
  border-color: transparent;
}

.form-options {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.remember-me {
  display: flex;
  align-items: center;
}

.checkbox-input {
  height: 1rem;
  width: 1rem;
  accent-color: #dc2626;
  border-radius: 0.25rem;
}

.checkbox-label {
  margin-left: 0.5rem;
  display: block;
  font-size: 0.875rem;
  color: #d1d5db;
}

.forgot-link {
  font-size: 0.875rem;
}

.forgot-link a {
  font-weight: 500;
  color: #dc2626;
  text-decoration: none;
  transition: color 0.2s;
}

.forgot-link a:hover {
  color: #ef4444;
}

.submit-container {
  margin-top: 0.5rem;
}

.submit-btn {
  position: relative;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0.75rem 1rem;
  border: none;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 0.5rem;
  color: white;
  background: linear-gradient(to right, #dc2626, #b91c1c);
  transition: all 0.2s;
  cursor: pointer;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.submit-btn:hover:not(:disabled) {
  background: linear-gradient(to right, #b91c1c, #991b1b);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.submit-btn:focus {
  outline: none;
  ring: 2px solid #ef4444;
  ring-offset: 2px;
}

.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-icon {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  padding-left: 0.75rem;
}

.lock-icon {
  height: 1.25rem;
  width: 1.25rem;
  color: #fca5a5;
}

.submit-btn:hover .lock-icon {
  color: #fecaca;
}

.spinner-icon {
  height: 1.25rem;
  width: 1.25rem;
  color: #fca5a5;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.spinner-circle {
  opacity: 0.25;
}

.spinner-path {
  opacity: 0.75;
}

.register-link {
  text-align: center;
}

.register-link p {
  font-size: 0.875rem;
  color: #9ca3af;
}

.register-link a {
  font-weight: 500;
  color: #dc2626;
  text-decoration: none;
  transition: color 0.2s;
}

.register-link a:hover {
  color: #ef4444;
}
</style>

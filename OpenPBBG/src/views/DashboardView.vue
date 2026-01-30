<template>
  <div class="dashboard-container">
    <!-- Header -->
    <header class="dashboard-header">
      <div class="header-content">
        <div class="header-row">
          <div class="logo-container">
            <h1 class="logo-title">Gangster Legends</h1>
          </div>
          
          <!-- User Stats -->
          <div v-if="dashboardData" class="user-stats">
            <div class="stat-item">
              <span class="stat-label">Level:</span>
              <span class="stat-value">{{ dashboardData.player.level }}</span>
            </div>
            <div class="stat-item">
              <span>💵</span>
              <span class="stat-value-green">${{ formatNumber(dashboardData.player.cash) }}</span>
            </div>
            <div class="stat-item">
              <span>❤️</span>
              <span class="stat-value-red">{{ dashboardData.player.health }}/{{ dashboardData.player.max_health }}</span>
            </div>
            <div class="stat-item">
              <span>⚡</span>
              <span class="stat-value-yellow">{{ dashboardData.player.energy }}/{{ dashboardData.player.max_energy }}</span>
            </div>
          </div>

          <div class="user-actions">
            <span class="username">{{ authStore.user?.username }}</span>
            <button
              @click="handleLogout"
              class="logout-button"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p class="loading-text">Loading...</p>
      </div>

      <div v-else-if="error" class="error-message">
        {{ error }}
      </div>

      <div v-else-if="dashboardData" class="dashboard-content">
        <!-- Player Info -->
        <div class="player-info-card">
          <h2 class="welcome-title">Welcome, {{ dashboardData.player.username }}!</h2>
          <div class="player-info-grid">
            <div>
              <p class="info-label">Rank</p>
              <p class="info-value">{{ dashboardData.player.current_rank?.name || 'Thug' }}</p>
            </div>
            <div>
              <p class="info-label">Location</p>
              <p class="info-value">{{ dashboardData.player.location?.name || 'Unknown' }}</p>
            </div>
            <div>
              <p class="info-label">Experience</p>
              <p class="info-value">{{ formatNumber(dashboardData.player.experience) }} XP</p>
            </div>
          </div>
        </div>

        <!-- Game Features Grid -->
        <div>
          <h3 class="features-title">Game Features</h3>
          <div class="features-grid">
            <router-link
              v-for="module in dashboardData.modules"
              :key="module.name"
              :to="getModuleRoute(module.name)"
              class="module-card"
            >
              <div class="module-icon">{{ getModuleIcon(module.name) }}</div>
              <div class="module-name">{{ module.display_name || module.name }}</div>
              <div class="module-description">{{ module.description || 'Play now' }}</div>
            </router-link>
          </div>
        </div>

        <!-- Daily Reward -->
        <div v-if="dashboardData.dailyReward" class="daily-reward">
          <h3 class="daily-reward-title">Daily Reward</h3>
          <p class="daily-reward-message">{{ dashboardData.dailyReward.message || 'Claim your daily reward!' }}</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(true)
const error = ref(null)
const dashboardData = ref(null)

const moduleIcons = {
  'crimes': '🔫',
  'gym': '💪',
  'hospital': '🏥',
  'jail': '⛓️',
  'bank': '🏦',
  'combat': '⚔️',
  'bounties': '🎯',
  'detective': '🔍',
  'theft': '🚗',
  'travel': '✈️',
  'achievements': '🏆',
  'missions': '📋',
  'organized-crime': '🎭',
  'gangs': '👥',
  'forum': '💬',
  'inventory': '🎒',
  'shop': '🛒',
  'leaderboards': '📊',
  'racing': '🏁',
  'drugs': '💊',
  'properties': '🏠'
}

function getModuleIcon(name) {
  return moduleIcons[name.toLowerCase()] || '🎮'
}

function getModuleRoute(name) {
  const routes = {
    'crimes': '/crimes',
    'gym': '/gym',
    'hospital': '/hospital',
    'jail': '/jail',
    'bank': '/bank',
    'combat': '/combat',
    'bounty': '/bounty',
    'drugs': '/drugs',
    'theft': '/theft',
    'racing': '/racing',
    'travel': '/travel',
    'inventory': '/inventory',
    'properties': '/properties',
    'gang': '/gang',
    'missions': '/missions',
    'achievements': '/achievements',
    'leaderboards': '/leaderboards',
    'forum': '/forum',
    'organized-crime': '/organized-crime',
    'detective': '/detective',
    'bullets': '/bullets'
  }
  return routes[name.toLowerCase()] || '/dashboard'
}

function formatNumber(num) {
  return new Intl.NumberFormat().format(num)
}

async function loadDashboard() {
  loading.value = true
  error.value = null
  try {
    const response = await api.get('/dashboard')
    dashboardData.value = response.data
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load dashboard'
    console.error('Dashboard error:', err)
  } finally {
    loading.value = false
  }
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  loadDashboard()
})
</script>

<style scoped>
/* Dashboard Container */
.dashboard-container {
  min-height: 100vh;
  background-color: #111827;
}

/* Header Styles */
.dashboard-header {
  background-color: #1f2937;
  border-bottom: 1px solid #374151;
}

.header-content {
  max-width: 80rem;
  margin-left: auto;
  margin-right: auto;
  padding-left: 1rem;
  padding-right: 1rem;
}

@media (min-width: 640px) {
  .header-content {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }
}

@media (min-width: 1024px) {
  .header-content {
    padding-left: 2rem;
    padding-right: 2rem;
  }
}

.header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 4rem;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #22d3ee;
}

/* User Stats */
.user-stats {
  display: none;
  align-items: center;
  gap: 1.5rem;
  font-size: 0.875rem;
}

@media (min-width: 768px) {
  .user-stats {
    display: flex;
  }
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stat-label {
  color: #9ca3af;
}

.stat-value {
  color: #ffffff;
  font-weight: 700;
}

.stat-value-green {
  color: #4ade80;
  font-weight: 700;
}

.stat-value-red {
  color: #f87171;
  font-weight: 700;
}

.stat-value-yellow {
  color: #facc15;
  font-weight: 700;
}

/* User Actions */
.user-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.username {
  color: #ffffff;
}

.logout-button {
  padding: 0.5rem 1rem;
  background-color: #dc2626;
  color: #ffffff;
  border-radius: 0.25rem;
  border: none;
  cursor: pointer;
  transition: background-color 0.15s;
}

.logout-button:hover {
  background-color: #b91c1c;
}

/* Main Content */
.main-content {
  max-width: 80rem;
  margin-left: auto;
  margin-right: auto;
  padding: 2rem 1rem;
}

@media (min-width: 640px) {
  .main-content {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }
}

@media (min-width: 1024px) {
  .main-content {
    padding-left: 2rem;
    padding-right: 2rem;
  }
}

/* Loading */
.loading-container {
  text-align: center;
  color: #ffffff;
  padding: 3rem 0;
}

.spinner {
  display: inline-block;
  width: 3rem;
  height: 3rem;
  border-radius: 9999px;
  border-bottom: 2px solid #22d3ee;
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

.loading-text {
  margin-top: 1rem;
}

/* Error Message */
.error-message {
  background-color: rgba(127, 29, 29, 0.5);
  border: 1px solid #ef4444;
  color: #fecaca;
  padding: 0.75rem 1rem;
  border-radius: 0.25rem;
}

/* Dashboard Content */
.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Player Info Card */
.player-info-card {
  background-color: #1f2937;
  border-radius: 0.5rem;
  padding: 1.5rem;
  border: 1px solid #374151;
}

.welcome-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 1rem;
}

.player-info-grid {
  display: grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: 1rem;
}

@media (min-width: 768px) {
  .player-info-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

.info-label {
  color: #9ca3af;
  font-size: 0.875rem;
}

.info-value {
  color: #ffffff;
  font-weight: 700;
  font-size: 1.125rem;
}

/* Features Section */
.features-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 1rem;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

@media (min-width: 768px) {
  .features-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

/* Module Card */
.module-card {
  background: linear-gradient(to bottom right, #0891b2, #1e40af);
  padding: 1.5rem;
  border-radius: 0.5rem;
  transition: all 0.3s;
  transform: scale(1);
  text-decoration: none;
  display: block;
}

.module-card:hover {
  background: linear-gradient(to bottom right, #06b6d4, #1d4ed8);
  transform: scale(1.05);
}

.module-icon {
  font-size: 2.25rem;
  margin-bottom: 0.5rem;
}

.module-name {
  color: #ffffff;
  font-weight: 700;
}

.module-description {
  color: #a5f3fc;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

/* Daily Reward */
.daily-reward {
  background: linear-gradient(to right, #d97706, #ea580c);
  border-radius: 0.5rem;
  padding: 1.5rem;
}

.daily-reward-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 0.5rem;
}

.daily-reward-message {
  color: #fef3c7;
}
</style>

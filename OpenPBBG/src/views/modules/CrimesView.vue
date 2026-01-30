<template>
  <div class="crimes-container">
    <!-- Header with Player Stats -->
    <div class="header">
      <div class="header-content">
        <router-link to="/dashboard" class="back-link">
          ← Back to Dashboard
        </router-link>
        <div class="stats-bar">
          <div class="stat-item">
            <span class="stat-icon">💰</span> {{ formatMoney(player?.cash || 0) }}
          </div>
          <div class="stat-item">
            <span class="stat-icon">⚡</span> {{ player?.energy || 0 }}/{{ player?.max_energy || 100 }}
          </div>
          <div class="stat-item">
            <span class="stat-icon">❤️</span> {{ player?.health || 0 }}/{{ player?.max_health || 100 }}
          </div>
        </div>
      </div>
    </div>

    <div class="content-wrapper">
      <h1 class="page-title">🔫 Crimes</h1>

      <!-- Cooldown Timer -->
      <div v-if="remainingCooldown > 0" class="cooldown-alert">
        <p class="cooldown-text">
          ⏱️ Cooldown: {{ formatTime(remainingCooldown) }}
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p class="loading-text">Loading crimes...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-alert">
        <p class="error-text">{{ error }}</p>
      </div>

      <!-- Crimes Grid -->
      <div v-else class="crimes-grid">
        <div
          v-for="crime in crimes"
          :key="crime.id"
          class="crime-card"
          :class="{ 'disabled': !canAttemptCrime(crime) }"
        >
          <h3 class="crime-name">{{ crime.name }}</h3>
          <p class="crime-description">{{ crime.description }}</p>

          <div class="crime-stats">
            <div class="stat-row">
              <span>Success Rate:</span>
              <span class="success-rate">{{ crime.success_rate }}%</span>
            </div>
            <div class="stat-row">
              <span>Energy Cost:</span>
              <span class="energy-cost">{{ crime.energy_cost }}</span>
            </div>
            <div class="stat-row">
              <span>Reward:</span>
              <span class="reward">{{ formatMoney(crime.min_reward) }} - {{ formatMoney(crime.max_reward) }}</span>
            </div>
            <div v-if="crime.required_level > 1" class="stat-row">
              <span>Required Level:</span>
              <span class="required-level">{{ crime.required_level }}</span>
            </div>
          </div>

          <button
            @click="attemptCrime(crime)"
            :disabled="!canAttemptCrime(crime) || processing"
            class="crime-button"
            :class="canAttemptCrime(crime) && !processing ? 'enabled' : 'disabled'"
          >
            {{ processing ? 'Processing...' : 'Attempt Crime' }}
          </button>
        </div>
      </div>

      <!-- Result Modal -->
      <div v-if="result" class="modal-overlay" @click="result = null">
        <div 
          class="modal-content"
          :class="result.success ? 'success' : 'failure'"
          @click.stop
        >
          <h3 class="modal-title" :class="result.success ? 'success' : 'failure'">
            {{ result.success ? '✅ Success!' : '❌ Failed!' }}
          </h3>
          <p class="modal-message">{{ result.message }}</p>
          <button
            @click="result = null"
            class="modal-close"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const player = ref(null)
const crimes = ref([])
const loading = ref(true)
const error = ref(null)
const processing = ref(false)
const remainingCooldown = ref(0)
const result = ref(null)

let cooldownInterval = null

const formatMoney = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatTime = (seconds) => {
  if (seconds <= 0) return '0s'
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  
  if (hours > 0) {
    return `${hours}h ${minutes}m ${secs}s`
  } else if (minutes > 0) {
    return `${minutes}m ${secs}s`
  } else {
    return `${secs}s`
  }
}

const canAttemptCrime = (crime) => {
  if (!player.value) return false
  if (remainingCooldown.value > 0) return false
  if (player.value.energy < crime.energy_cost) return false
  if (player.value.level < crime.required_level) return false
  return true
}

const loadData = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Load crimes list
    const crimesResponse = await api.get('/crimes')
    crimes.value = crimesResponse.data.crimes || []
    remainingCooldown.value = Math.floor(crimesResponse.data.cooldown || 0)
    
    // Load player data
    const playerResponse = await api.get('/user')
    player.value = playerResponse.data
    
    // Start cooldown timer if needed
    if (remainingCooldown.value > 0) {
      startCooldownTimer()
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load crimes'
    console.error('Error loading crimes:', err)
  } finally {
    loading.value = false
  }
}

const startCooldownTimer = () => {
  if (cooldownInterval) {
    clearInterval(cooldownInterval)
  }
  
  cooldownInterval = setInterval(() => {
    if (remainingCooldown.value > 0) {
      remainingCooldown.value--
    } else {
      clearInterval(cooldownInterval)
      cooldownInterval = null
    }
  }, 1000)
}

const attemptCrime = async (crime) => {
  if (!canAttemptCrime(crime) || processing.value) return
  
  try {
    processing.value = true
    const response = await api.post('/crimes/attempt', {
      crime_id: crime.id
    })
    
    result.value = {
      success: response.data.success,
      message: response.data.message
    }
    
    // Update player data
    player.value.cash = response.data.player.cash
    player.value.energy = response.data.player.energy
    player.value.experience = response.data.player.experience
    
    // Set cooldown
    remainingCooldown.value = Math.floor(response.data.cooldown || 0)
    if (remainingCooldown.value > 0) {
      startCooldownTimer()
    }
  } catch (err) {
    result.value = {
      success: false,
      message: err.response?.data?.message || 'Failed to attempt crime'
    }
    console.error('Error attempting crime:', err)
  } finally {
    processing.value = false
  }
}

onMounted(() => {
  loadData()
})

onUnmounted(() => {
  if (cooldownInterval) {
    clearInterval(cooldownInterval)
  }
})
</script>

<style scoped>
.crimes-container {
  min-height: 100vh;
  background: linear-gradient(to bottom right, #111827, #581c87, #111827);
}

.header {
  background-color: rgba(31, 41, 55, 0.5);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(168, 85, 247, 0.3);
  padding: 1rem;
}

.header-content {
  max-width: 80rem;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.back-link {
  color: #c084fc;
  text-decoration: none;
}

.back-link:hover {
  color: #d8b4fe;
}

.stats-bar {
  display: flex;
  gap: 1.5rem;
  font-size: 0.875rem;
}

.stat-item {
  color: #d1d5db;
}

.stat-icon {
  color: #c084fc;
}

.content-wrapper {
  max-width: 80rem;
  margin: 0 auto;
  padding: 1.5rem;
}

.page-title {
  font-size: 2.25rem;
  font-weight: bold;
  color: white;
  margin-bottom: 2rem;
}

.cooldown-alert {
  background-color: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.5);
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.cooldown-text {
  color: #fca5a5;
  text-align: center;
}

.loading-state {
  text-align: center;
  padding: 3rem 0;
}

.spinner {
  display: inline-block;
  width: 3rem;
  height: 3rem;
  border: 2px solid transparent;
  border-bottom-color: #a855f7;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-text {
  color: #9ca3af;
  margin-top: 1rem;
}

.error-alert {
  background-color: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.5);
  border-radius: 0.5rem;
  padding: 1.5rem;
}

.error-text {
  color: #fca5a5;
}

.crimes-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .crimes-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .crimes-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.crime-card {
  background-color: rgba(31, 41, 55, 0.5);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(168, 85, 247, 0.3);
  border-radius: 0.5rem;
  padding: 1.5rem;
  transition: border-color 0.3s;
}

.crime-card:hover {
  border-color: rgba(168, 85, 247, 0.6);
}

.crime-card.disabled {
  opacity: 0.5;
}

.crime-name {
  font-size: 1.25rem;
  font-weight: bold;
  color: white;
  margin-bottom: 0.5rem;
}

.crime-description {
  color: #9ca3af;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.crime-stats {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  color: #d1d5db;
}

.success-rate,
.reward {
  color: #4ade80;
}

.energy-cost {
  color: #facc15;
}

.required-level {
  color: #c084fc;
}

.crime-button {
  width: 100%;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: bold;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.crime-button.enabled {
  background: linear-gradient(to right, #9333ea, #ec4899);
  color: white;
}

.crime-button.enabled:hover {
  background: linear-gradient(to right, #a855f7, #f472b6);
}

.crime-button.disabled {
  background-color: #374151;
  color: #6b7280;
  cursor: not-allowed;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.modal-content {
  background-color: #1f2937;
  border: 2px solid;
  border-radius: 0.5rem;
  padding: 2rem;
  max-width: 28rem;
  width: 100%;
  margin: 0 1rem;
}

.modal-content.success {
  border-color: #22c55e;
}

.modal-content.failure {
  border-color: #ef4444;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.modal-title.success {
  color: #4ade80;
}

.modal-title.failure {
  color: #f87171;
}

.modal-message {
  color: white;
  margin-bottom: 1.5rem;
}

.modal-close {
  width: 100%;
  padding: 0.5rem 1rem;
  background-color: #9333ea;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s;
}

.modal-close:hover {
  background-color: #a855f7;
}
</style>

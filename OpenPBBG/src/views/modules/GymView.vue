<template>
  <div class="gym-container">
    <div class="header">
      <div class="header-content">
        <router-link to="/dashboard" class="back-link">← Back</router-link>
        <div class="stats-bar">
          <div class="stat-item"><span class="stat-icon">💰</span> {{ formatMoney(player?.cash || 0) }}</div>
          <div class="stat-item"><span class="stat-icon">⚡</span> {{ player?.energy || 0 }}</div>
        </div>
      </div>
    </div>

    <div class="content-wrapper">
      <div class="gym-banner">
        <div class="banner-content">
          <div>
            <h1 class="banner-title">🏋️ Iron Paradise Gym</h1>
            <p class="banner-subtitle">Train your stats to become stronger</p>
          </div>
          <div class="banner-icon">💪</div>
        </div>
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
      </div>

      <div v-else class="training-panel">
        <h3 class="section-title">Select Training</h3>
        
        <div class="attributes-grid">
          <div v-for="attr in attributes" :key="attr.key"
               @click="selectedAttribute = attr.key"
               class="attribute-card"
               :class="selectedAttribute === attr.key ? 'selected' : ''">
            <div class="attribute-header">
              <div class="attribute-icon">{{ attr.icon }}</div>
              <div class="attribute-info">
                <h4 class="attribute-name">{{ attr.name }}</h4>
                <p class="attribute-description">{{ attr.description }}</p>
              </div>
            </div>
            <p class="attribute-cost">{{ formatMoney(costs[attr.key] || 0) }} per session</p>
          </div>
        </div>

        <div class="training-controls">
          <label class="control-label">How many times?</label>
          <div class="times-input-group">
            <input v-model.number="times" type="number" min="1" :max="maxPerSession" 
                   class="times-input">
            <div class="quick-buttons">
              <button @click="times = 1" class="quick-button">1</button>
              <button @click="times = 10" class="quick-button">10</button>
              <button @click="times = 25" class="quick-button">25</button>
              <button @click="times = 50" class="quick-button">50</button>
            </div>
          </div>

          <div class="cost-summary">
            <div class="cost-row">
              <span class="cost-label">Total Cost:</span>
              <span class="cost-value">{{ formatMoney(totalCost) }}</span>
            </div>
          </div>

          <button @click="train" :disabled="processing || player?.cash < totalCost"
                  class="train-button"
                  :class="!processing && player?.cash >= totalCost ? 'enabled' : 'disabled'">
            {{ processing ? 'Training...' : 'Start Training' }}
          </button>
        </div>
      </div>

      <div v-if="result" class="result-message" :class="result.success ? 'success' : 'error'">
        <p>{{ result.message }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const player = ref(null)
const costs = ref({})
const maxPerSession = ref(50)
const selectedAttribute = ref('strength')
const times = ref(1)
const processing = ref(false)
const loading = ref(true)
const result = ref(null)

const attributes = [
  { key: 'strength', name: 'Strength', icon: '💪', description: 'Increase attack power' },
  { key: 'defense', name: 'Defense', icon: '🛡️', description: 'Reduce damage taken' },
  { key: 'speed', name: 'Speed', icon: '⚡', description: 'Move faster, dodge attacks' },
  { key: 'stamina', name: 'Stamina', icon: '❤️', description: 'Increase max health' },
]

const totalCost = computed(() => (costs.value[selectedAttribute.value] || 0) * times.value)

const formatMoney = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(val)

const loadData = async () => {
  try {
    const [gymResponse, playerResponse] = await Promise.all([
      api.get('/gym'),
      api.get('/user')
    ])
    costs.value = gymResponse.data.costs || {}
    maxPerSession.value = gymResponse.data.maxPerSession || 50
    player.value = playerResponse.data
  } catch (err) {
    console.error('Error loading gym:', err)
  } finally {
    loading.value = false
  }
}

const train = async () => {
  if (processing.value) return
  processing.value = true
  result.value = null
  
  try {
    const response = await api.post('/gym/train', {
      attribute: selectedAttribute.value,
      times: times.value
    })
    result.value = { success: true, message: response.data.message }
    player.value = response.data.player
  } catch (err) {
    result.value = { success: false, message: err.response?.data?.message || 'Training failed' }
  } finally {
    processing.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.gym-container {
  min-height: 100vh;
  background: linear-gradient(to bottom right, #111827, #7c2d12, #111827);
}

.header {
  background-color: rgba(31, 41, 55, 0.5);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(249, 115, 22, 0.3);
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
  color: #fb923c;
  text-decoration: none;
}

.back-link:hover {
  color: #fdba74;
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
  color: #fb923c;
}

.content-wrapper {
  max-width: 80rem;
  margin: 0 auto;
  padding: 1.5rem;
}

.gym-banner {
  background: linear-gradient(to right, #ea580c, #dc2626);
  color: white;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  margin-bottom: 1.5rem;
}

.banner-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.banner-title {
  font-size: 2.25rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.banner-subtitle {
  color: #fed7aa;
}

.banner-icon {
  font-size: 4.5rem;
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
  border-bottom-color: #f97316;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.training-panel {
  background-color: rgba(31, 41, 55, 0.5);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(249, 115, 22, 0.3);
  border-radius: 0.5rem;
  padding: 1.5rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  margin-bottom: 1.5rem;
}

.attributes-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
  .attributes-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.attribute-card {
  border: 2px solid #374151;
  border-radius: 0.5rem;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.3s;
}

.attribute-card:hover {
  border-color: #f97316;
}

.attribute-card.selected {
  border-color: #ea580c;
  background-color: rgba(124, 45, 18, 0.3);
}

.attribute-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.attribute-icon {
  font-size: 3rem;
}

.attribute-info {
  flex: 1;
}

.attribute-name {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
}

.attribute-description {
  color: #9ca3af;
  font-size: 0.875rem;
}

.attribute-cost {
  color: #fb923c;
  font-weight: bold;
  font-size: 1.125rem;
}

.training-controls {
  border-top: 1px solid #374151;
  padding-top: 1.5rem;
}

.control-label {
  display: block;
  font-size: 1.125rem;
  font-weight: 600;
  color: white;
  margin-bottom: 0.75rem;
}

.times-input-group {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.times-input {
  flex: 1;
  padding: 0.75rem 1rem;
  background-color: #374151;
  color: white;
  border: 1px solid #4b5563;
  border-radius: 0.5rem;
  font-size: 1.125rem;
}

.quick-buttons {
  display: flex;
  gap: 0.5rem;
}

.quick-button {
  padding: 0.5rem 1rem;
  background-color: #374151;
  color: white;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.quick-button:hover {
  background-color: #4b5563;
}

.cost-summary {
  background-color: rgba(55, 65, 81, 0.5);
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}

.cost-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.125rem;
  color: white;
}

.cost-label {
  font-weight: 600;
}

.cost-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: #fb923c;
}

.train-button {
  width: 100%;
  padding: 1rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: bold;
  font-size: 1.125rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.train-button.enabled {
  background: linear-gradient(to right, #ea580c, #dc2626);
  color: white;
}

.train-button.enabled:hover {
  background: linear-gradient(to right, #f97316, #ef4444);
}

.train-button.disabled {
  background-color: #374151;
  color: #6b7280;
  cursor: not-allowed;
}

.result-message {
  margin-top: 1.5rem;
  padding: 1rem;
  border-radius: 0.5rem;
}

.result-message.success {
  background-color: rgba(34, 197, 94, 0.2);
  border: 1px solid #22c55e;
}

.result-message.error {
  background-color: rgba(239, 68, 68, 0.2);
  border: 1px solid #ef4444;
}

.result-message p {
  color: white;
  margin: 0;
}
</style>

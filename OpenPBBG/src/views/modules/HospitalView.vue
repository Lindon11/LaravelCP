<template>
  <div class="hospital-container">
    <div class="header">
      <div class="header-content">
        <router-link to="/dashboard" class="back-link">← Back</router-link>
        <div class="stats-bar">
          <div class="stat-item"><span class="stat-icon">💰</span> {{ formatMoney(player?.cash || 0) }}</div>
          <div class="stat-item health"><span class="health-icon">❤️</span> {{ player?.health || 0 }}/{{ player?.max_health || 100 }}</div>
        </div>
      </div>
    </div>

    <div class="content-wrapper">
      <div class="hospital-banner">
        <div class="banner-content">
          <div>
            <h1 class="banner-title">🏥 City Hospital</h1>
            <p class="banner-subtitle">Heal your wounds and get back to business</p>
          </div>
          <div class="banner-icon">💉</div>
        </div>
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
      </div>

      <div v-else>
        <!-- Health Status -->
        <div class="health-status-panel">
          <h3 class="section-title">Your Health Status</h3>
          <div class="health-bar-container">
            <div class="health-bar-labels">
              <span>Health: {{ player?.health || 0 }} / {{ player?.max_health || 100 }}</span>
              <span>{{ healthPercent }}%</span>
            </div>
            <div class="health-bar-track">
              <div class="health-bar-fill" :class="healthBarColor" :style="{ width: healthPercent + '%' }"></div>
            </div>
          </div>
          <p v-if="healthMissing > 0" class="health-info">You need {{ healthMissing }} HP</p>
          <p v-else class="health-info full">You're at full health!</p>
        </div>

        <!-- Healing Options -->
        <div class="healing-options-panel">
          <h3 class="section-title">Healing Options</h3>
          
          <!-- Custom Amount -->
          <div class="custom-healing">
            <label class="control-label">Custom Healing Amount</label>
            <div class="quick-buttons-group">
              <button @click="setQuickAmount(25)" class="quick-button">25%</button>
              <button @click="setQuickAmount(50)" class="quick-button">50%</button>
              <button @click="setQuickAmount(75)" class="quick-button">75%</button>
              <button @click="setQuickAmount(100)" class="quick-button">100%</button>
            </div>
            <input v-model.number="customAmount" type="number" min="1" :max="healthMissing"
                   class="amount-input">
            <div class="cost-row">
              <span>Cost:</span>
              <span class="cost-value">{{ formatMoney(customCost) }}</span>
            </div>
            <button @click="healCustom" :disabled="processing || !canAffordCustom"
                    class="heal-button custom"
                    :class="!processing && canAffordCustom ? 'enabled' : 'disabled'">
              {{ processing ? 'Healing...' : 'Heal Custom Amount' }}
            </button>
          </div>

          <!-- Full Heal -->
          <div class="full-heal-section">
            <div class="full-heal-info">
              <div>
                <h4 class="full-heal-title">Full Heal</h4>
                <p class="full-heal-description">Restore all health instantly</p>
              </div>
              <div class="full-heal-price">{{ formatMoney(fullHealCost) }}</div>
            </div>
            <button @click="healFull" :disabled="processing || !canAffordFull || healthMissing === 0"
                    class="heal-button full"
                    :class="!processing && canAffordFull && healthMissing > 0 ? 'enabled' : 'disabled'">
              {{ processing ? 'Healing...' : 'Full Heal' }}
            </button>
          </div>
        </div>

        <div v-if="result" class="result-message" :class="result.success ? 'success' : 'error'">
          <p>{{ result.message }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const player = ref(null)
const costPerHp = ref(100)
const fullHealCost = ref(0)
const customAmount = ref(10)
const processing = ref(false)
const loading = ref(true)
const result = ref(null)

const healthPercent = computed(() => {
  if (!player.value) return 0
  return Math.round((player.value.health / player.value.max_health) * 100)
})

const healthMissing = computed(() => {
  if (!player.value) return 0
  return player.value.max_health - player.value.health
})

const customCost = computed(() => customAmount.value * costPerHp.value)

const canAffordCustom = computed(() => player.value && player.value.cash >= customCost.value)

const canAffordFull = computed(() => player.value && player.value.cash >= fullHealCost.value)

const healthBarColor = computed(() => {
  if (healthPercent.value >= 70) return 'bg-green-500'
  if (healthPercent.value >= 40) return 'bg-yellow-500'
  return 'bg-red-500'
})

const formatMoney = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(val)

const setQuickAmount = (percent) => {
  customAmount.value = Math.ceil(healthMissing.value * (percent / 100))
}

const loadData = async () => {
  try {
    const [hospitalResponse, playerResponse] = await Promise.all([
      api.get('/hospital'),
      api.get('/user')
    ])
    costPerHp.value = hospitalResponse.data.costPerHp || 100
    fullHealCost.value = hospitalResponse.data.fullHealCost || 0
    player.value = playerResponse.data
  } catch (err) {
    console.error('Error loading hospital:', err)
  } finally {
    loading.value = false
  }
}

const healCustom = async () => {
  if (processing.value || !canAffordCustom.value) return
  processing.value = true
  result.value = null
  
  try {
    const response = await api.post('/hospital/heal', { amount: customAmount.value })
    result.value = { success: true, message: response.data.message }
    player.value = response.data.player
  } catch (err) {
    result.value = { success: false, message: err.response?.data?.message || 'Healing failed' }
  } finally {
    processing.value = false
  }
}

const healFull = async () => {
  if (processing.value || !canAffordFull.value) return
  processing.value = true
  result.value = null
  
  try {
    const response = await api.post('/hospital/heal-full')
    result.value = { success: true, message: response.data.message }
    player.value = response.data.player
  } catch (err) {
    result.value = { success: false, message: err.response?.data?.message || 'Healing failed' }
  } finally {
    processing.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.hospital-container {
  min-height: 100vh;
  background: linear-gradient(to bottom right, #111827, #1e3a8a, #111827);
}

.header {
  background-color: rgba(31, 41, 55, 0.5);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(59, 130, 246, 0.3);
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
  color: #60a5fa;
  text-decoration: none;
}

.back-link:hover {
  color: #93c5fd;
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
  color: #60a5fa;
}

.health-icon {
  color: #f87171;
}

.content-wrapper {
  max-width: 56rem;
  margin: 0 auto;
  padding: 1.5rem;
}

.hospital-banner {
  background: linear-gradient(to right, #2563eb, #0891b2);
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
  color: #bfdbfe;
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
  border-bottom-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.health-status-panel,
.healing-options-panel {
  background-color: rgba(31, 41, 55, 0.5);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-radius: 0.5rem;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: bold;
  color: white;
  margin-bottom: 1rem;
}

.health-bar-container {
  margin-bottom: 1rem;
}

.health-bar-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
  color: #9ca3af;
  margin-bottom: 0.5rem;
}

.health-bar-track {
  width: 100%;
  background-color: #374151;
  border-radius: 9999px;
  height: 1rem;
  overflow: hidden;
}

.health-bar-fill {
  height: 100%;
  transition: all 0.3s;
}

.health-bar-fill.bg-green-500 {
  background-color: #22c55e;
}

.health-bar-fill.bg-yellow-500 {
  background-color: #eab308;
}

.health-bar-fill.bg-red-500 {
  background-color: #ef4444;
}

.health-info {
  color: #9ca3af;
}

.health-info.full {
  color: #4ade80;
}

.custom-healing {
  margin-bottom: 1.5rem;
}

.control-label {
  display: block;
  color: white;
  font-weight: 600;
  margin-bottom: 0.75rem;
}

.quick-buttons-group {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.quick-button {
  padding: 0.5rem 0.75rem;
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

.amount-input {
  width: 100%;
  padding: 0.75rem 1rem;
  background-color: #374151;
  color: white;
  border: 1px solid #4b5563;
  border-radius: 0.5rem;
  margin-bottom: 0.75rem;
}

.cost-row {
  display: flex;
  justify-content: space-between;
  color: white;
  margin-bottom: 0.75rem;
}

.cost-value {
  font-weight: bold;
  color: #60a5fa;
}

.heal-button {
  width: 100%;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: bold;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.heal-button.custom.enabled {
  background: linear-gradient(to right, #2563eb, #0891b2);
  color: white;
}

.heal-button.custom.enabled:hover {
  background: linear-gradient(to right, #3b82f6, #06b6d4);
}

.heal-button.disabled {
  background-color: #374151;
  color: #6b7280;
  cursor: not-allowed;
}

.full-heal-section {
  border-top: 1px solid #374151;
  padding-top: 1.5rem;
}

.full-heal-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.full-heal-title {
  color: white;
  font-weight: bold;
  font-size: 1.125rem;
}

.full-heal-description {
  color: #9ca3af;
  font-size: 0.875rem;
}

.full-heal-price {
  font-size: 1.5rem;
  font-weight: bold;
  color: #60a5fa;
}

.heal-button.full.enabled {
  background: linear-gradient(to right, #059669, #10b981);
  color: white;
}

.heal-button.full.enabled:hover {
  background: linear-gradient(to right, #10b981, #34d399);
}

.result-message {
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

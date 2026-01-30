<template>
  <div class="travel-container">
    <!-- Header -->
    <div class="travel-header">
      <h2 class="travel-title">✈️ Travel</h2>
      <router-link to="/dashboard" class="back-link">← Dashboard</router-link>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading destinations...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <!-- Main Content -->
    <div v-else class="travel-content">
      <!-- Flash Messages -->
      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>
      <div v-if="errorMessage" class="error-banner">
        {{ errorMessage }}
      </div>

      <!-- Current Location -->
      <div v-if="currentLocation" class="current-location">
        <div class="location-header">
          <div class="location-info">
            <p class="location-label">Currently in</p>
            <h3 class="location-name">{{ currentLocation.name }}</h3>
            <p class="location-description">{{ currentLocation.description }}</p>
          </div>
          <div class="location-icon">📍</div>
        </div>
        <div v-if="playersHere.length > 0" class="players-here">
          <p class="players-label">Players Online Here ({{ playersHere.length }})</p>
          <div class="players-list">
            <span v-for="p in playersHere.slice(0, 10)" :key="p.id" class="player-badge">
              {{ p.username }} ({{ p.level }})
            </span>
            <span v-if="playersHere.length > 10" class="player-badge">
              +{{ playersHere.length - 10 }} more
            </span>
          </div>
        </div>
      </div>

      <!-- Available Locations -->
      <div class="locations-card">
        <h3 class="locations-title">Available Destinations</h3>
        <div class="locations-grid">
          <div 
            v-for="loc in locations" 
            :key="loc.id" 
            class="location-item"
            :class="{ 'current-location-item': currentLocation?.id === loc.id }"
          >
            <div class="location-card-header">
              <div class="location-card-info">
                <div class="location-name-row">
                  <h4 class="location-card-name">{{ loc.name }}</h4>
                  <span v-if="currentLocation?.id === loc.id" class="current-badge">CURRENT</span>
                </div>
                <p class="location-card-description">{{ loc.description }}</p>
                <div class="location-details">
                  <span class="travel-cost">${{ formatNumber(loc.travel_cost) }}</span>
                  <span class="level-req">Level {{ loc.required_level }}+</span>
                </div>
              </div>
              <div class="location-card-icon">🌆</div>
            </div>
            <button 
              v-if="currentLocation?.id !== loc.id"
              @click="travelTo(loc.id)" 
              :disabled="processing || playerLevel < loc.required_level || playerCash < loc.travel_cost" 
              class="travel-button"
            >
              {{ processing ? 'Traveling...' : 'Travel Here' }}
            </button>
            <div v-else class="here-badge">
              You are here
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const loading = ref(true)
const processing = ref(false)
const error = ref(null)
const successMessage = ref(null)
const errorMessage = ref(null)

const locations = ref([])
const currentLocation = ref(null)
const playersHere = ref([])
const playerLevel = ref(1)
const playerCash = ref(0)

onMounted(async () => {
  await fetchTravelData()
})

async function fetchTravelData() {
  try {
    loading.value = true
    const response = await api.get('/travel')
    locations.value = response.data.locations || []
    currentLocation.value = response.data.currentLocation
    playersHere.value = response.data.playersHere || []
    playerLevel.value = response.data.player?.level || 1
    playerCash.value = response.data.player?.cash || 0
    error.value = null
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load travel data'
  } finally {
    loading.value = false
  }
}

async function travelTo(locationId) {
  if (processing.value) return
  
  processing.value = true
  successMessage.value = null
  errorMessage.value = null
  
  try {
    const response = await api.post(`/travel/${locationId}`)
    successMessage.value = response.data.message || 'Traveled successfully!'
    await fetchTravelData()
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Failed to travel'
  } finally {
    processing.value = false
  }
}

function formatNumber(num) {
  return new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(num)
}
</script>

<style scoped>
.travel-container {
  min-height: 100vh;
  background-color: #111827;
  padding: 1.5rem;
}

.travel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
}

.travel-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: white;
}

.back-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}

.back-link:hover {
  color: #60a5fa;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #9ca3af;
}

.spinner {
  width: 3rem;
  height: 3rem;
  border: 3px solid #374151;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  background-color: rgba(127, 29, 29, 0.5);
  border: 1px solid #ef4444;
  color: #fecaca;
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}

.travel-content {
  max-width: 80rem;
  margin: 0 auto;
}

.success-message {
  background-color: rgba(6, 95, 70, 0.5);
  border: 1px solid #10b981;
  color: #a7f3d0;
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}

.error-banner {
  background-color: rgba(127, 29, 29, 0.5);
  border: 1px solid #ef4444;
  color: #fecaca;
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}

.current-location {
  background: linear-gradient(to right, #2563eb, #1d4ed8);
  color: white;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  margin-bottom: 1.5rem;
}

.location-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.location-label {
  color: #bfdbfe;
  font-size: 0.875rem;
}

.location-name {
  font-size: 2.25rem;
  font-weight: bold;
  margin: 0.5rem 0;
}

.location-description {
  color: #bfdbfe;
  margin-top: 0.5rem;
}

.location-icon {
  font-size: 3.75rem;
}

.players-here {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #60a5fa;
}

.players-label {
  color: #bfdbfe;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.players-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.player-badge {
  padding: 0.25rem 0.75rem;
  background-color: #1e40af;
  border-radius: 9999px;
  font-size: 0.875rem;
}

.locations-card {
  background-color: #1f2937;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
}

.locations-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  margin-bottom: 1.5rem;
}

.locations-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .locations-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.location-item {
  border: 1px solid #374151;
  border-radius: 0.5rem;
  padding: 1.5rem;
  transition: box-shadow 0.2s;
}

.location-item:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.current-location-item {
  border-color: #3b82f6;
  background-color: rgba(59, 130, 246, 0.1);
}

.location-card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.location-card-info {
  flex: 1;
}

.location-name-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.location-card-name {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  margin: 0;
}

.current-badge {
  padding: 0.25rem 0.5rem;
  background-color: #2563eb;
  color: white;
  font-size: 0.75rem;
  font-weight: bold;
  border-radius: 0.25rem;
}

.location-card-description {
  color: #9ca3af;
  margin-bottom: 0.75rem;
}

.location-details {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.875rem;
}

.travel-cost {
  color: #10b981;
  font-weight: 600;
}

.level-req {
  color: #6b7280;
}

.location-card-icon {
  font-size: 2.25rem;
  margin-left: 1rem;
}

.travel-button {
  width: 100%;
  padding: 0.5rem;
  background-color: #2563eb;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.travel-button:hover:not(:disabled) {
  background-color: #1d4ed8;
}

.travel-button:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

.here-badge {
  width: 100%;
  padding: 0.5rem;
  background-color: #dbeafe;
  color: #1e40af;
  text-align: center;
  border-radius: 0.375rem;
  font-weight: 600;
}
</style>

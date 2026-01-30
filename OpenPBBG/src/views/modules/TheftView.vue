<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

interface TheftType {
  id: number;
  name: string;
  description: string;
  success_rate: number;
  min_car_value: number;
  max_car_value: number;
  cooldown: number;
}

interface Player {
  id: number;
  username: string;
  cash: number;
  bank: number;
}

const router = useRouter();

const loading = ref(true);
const error = ref('');
const successMessage = ref('');
const processing = ref(false);

const player = ref<Player | null>(null);
const theftTypes = ref<TheftType[]>([]);
const canAttempt = ref(true);
const cooldownRemaining = ref(0);

let cooldownInterval: number | null = null;

const formatMoney = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
  }).format(amount);
};

const formatTime = (seconds: number): string => {
  const roundedSeconds = Math.floor(seconds);
  if (roundedSeconds <= 0) return '0s';
  const mins = Math.floor(roundedSeconds / 60);
  const secs = roundedSeconds % 60;
  return mins > 0 ? `${mins}m ${secs}s` : `${secs}s`;
};

const getDifficultyColor = (successRate: number): string => {
  if (successRate >= 70) return 'success';
  if (successRate >= 50) return 'warning';
  if (successRate >= 30) return 'danger';
  return 'critical';
};

const getDifficultyBadge = (successRate: number) => {
  if (successRate >= 70) return { text: 'Easy', class: 'badge-success' };
  if (successRate >= 50) return { text: 'Medium', class: 'badge-warning' };
  if (successRate >= 30) return { text: 'Hard', class: 'badge-danger' };
  return { text: 'Very Hard', class: 'badge-critical' };
};

const loadData = async () => {
  try {
    loading.value = true;
    error.value = '';
    
    const response = await api.get('/modules/theft');
    
    player.value = response.data.player;
    theftTypes.value = response.data.theftTypes || [];
    canAttempt.value = response.data.canAttempt !== false;
    cooldownRemaining.value = Math.floor(response.data.cooldownRemaining || 0);
    
    if (cooldownRemaining.value > 0) {
      startCooldownTimer();
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load theft data';
  } finally {
    loading.value = false;
  }
};

const startCooldownTimer = () => {
  if (cooldownInterval) {
    clearInterval(cooldownInterval);
  }
  
  cooldownInterval = window.setInterval(() => {
    if (cooldownRemaining.value > 0) {
      cooldownRemaining.value--;
    } else {
      if (cooldownInterval) {
        clearInterval(cooldownInterval);
        cooldownInterval = null;
      }
      canAttempt.value = true;
    }
  }, 1000);
};

const attemptTheft = async (typeId: number) => {
  if (processing.value || !canAttempt.value) return;
  
  try {
    processing.value = true;
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post(`/modules/theft/attempt/${typeId}`);
    
    successMessage.value = response.data.message || 'Theft attempted successfully!';
    
    // Update player data and cooldown
    if (response.data.player) {
      player.value = response.data.player;
    }
    if (response.data.cooldownRemaining) {
      cooldownRemaining.value = Math.floor(response.data.cooldownRemaining);
      canAttempt.value = false;
      startCooldownTimer();
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to attempt theft';
  } finally {
    processing.value = false;
  }
};

const goToDashboard = () => {
  router.push('/');
};

const goToGarage = () => {
  router.push('/modules/theft/garage');
};

onMounted(() => {
  loadData();
});

onUnmounted(() => {
  if (cooldownInterval) {
    clearInterval(cooldownInterval);
  }
});
</script>

<template>
  <div class="theft-view">
    <div class="header">
      <h1>🚗 Car Theft (GTA)</h1>
      <button @click="goToDashboard" class="back-button">← Dashboard</button>
    </div>

    <div class="content">
      <!-- Loading State -->
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Loading theft data...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-message">
        <strong>Error:</strong> {{ error }}
      </div>

      <!-- Main Content -->
      <div v-else-if="player">
        <!-- Flash Messages -->
        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>

        <!-- Cooldown Warning -->
        <div v-if="cooldownRemaining > 0" class="cooldown-warning">
          <p class="cooldown-title">⏱️ Cooldown Active</p>
          <p>You must wait <strong>{{ formatTime(cooldownRemaining) }}</strong> before attempting another theft.</p>
        </div>

        <!-- Theft Types Grid -->
        <div class="theft-grid">
          <div 
            v-for="type in theftTypes" 
            :key="type.id"
            class="theft-card"
          >
            <div class="theft-card-header">
              <h3>{{ type.name }}</h3>
              <span :class="['badge', getDifficultyBadge(type.success_rate).class]">
                {{ getDifficultyBadge(type.success_rate).text }}
              </span>
            </div>

            <div class="theft-card-body">
              <p class="description">{{ type.description }}</p>

              <div class="stats">
                <div class="stat-row">
                  <span class="stat-label">Success Rate:</span>
                  <span :class="['stat-value', getDifficultyColor(type.success_rate)]">
                    {{ type.success_rate }}%
                  </span>
                </div>
                <div class="stat-row">
                  <span class="stat-label">Car Value Range:</span>
                  <span class="stat-value">
                    {{ formatMoney(type.min_car_value) }} - {{ formatMoney(type.max_car_value) }}
                  </span>
                </div>
                <div class="stat-row">
                  <span class="stat-label">Cooldown:</span>
                  <span class="stat-value">{{ type.cooldown }}s</span>
                </div>
              </div>

              <button
                @click="attemptTheft(type.id)"
                :disabled="processing || !canAttempt"
                class="theft-button"
              >
                {{ processing ? 'Attempting...' : 'Steal a Car' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Quick Link to Garage -->
        <div class="garage-link">
          <div class="garage-info">
            <p class="garage-title">💼 Your Garage</p>
            <p class="garage-subtitle">View and sell your stolen cars</p>
          </div>
          <button @click="goToGarage" class="garage-button">
            View Garage
          </button>
        </div>

        <!-- How It Works -->
        <div class="info-section">
          <h3>🚗 How Car Theft Works</h3>
          <ul>
            <li>Choose a theft difficulty based on your risk tolerance</li>
            <li>Higher difficulty = Lower success rate, but more valuable cars</li>
            <li>If you fail, you might get caught and sent to jail (1 in 3 chance)</li>
            <li>Successfully stolen cars go to your garage with random damage</li>
            <li>Sell cars from your garage to earn cash</li>
            <li>Damage reduces the car's sale value</li>
            <li>3-minute cooldown between theft attempts</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.theft-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #1a1a1a 0%, #2d1810 100%);
  color: #ffffff;
  padding: 20px;
}

.header {
  max-width: 1200px;
  margin: 0 auto 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}

.header h1 {
  font-size: 2rem;
  font-weight: bold;
  color: #ff6b35;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
  margin: 0;
}

.back-button {
  padding: 10px 20px;
  background-color: #444;
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.back-button:hover {
  background-color: #555;
  transform: translateY(-2px);
}

.content {
  max-width: 1200px;
  margin: 0 auto;
}

.loading {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 107, 53, 0.2);
  border-top-color: #ff6b35;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  background-color: rgba(220, 38, 38, 0.2);
  border: 2px solid #dc2626;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.success-message {
  background-color: rgba(34, 197, 94, 0.2);
  border: 2px solid #22c55e;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  color: #86efac;
}

.cooldown-warning {
  background-color: rgba(245, 158, 11, 0.2);
  border-left: 4px solid #f59e0b;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 25px;
}

.cooldown-title {
  font-weight: 700;
  color: #fbbf24;
  margin-bottom: 8px;
  font-size: 1.1rem;
}

.cooldown-warning strong {
  color: #fde047;
}

.theft-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 25px;
  margin-bottom: 30px;
}

.theft-card {
  background: linear-gradient(145deg, #2a2a2a, #1f1f1f);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 107, 53, 0.2);
}

.theft-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(255, 107, 53, 0.3);
}

.theft-card-header {
  background: linear-gradient(135deg, #ff6b35 0%, #ff4500 100%);
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.theft-card-header h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: bold;
  color: #ffffff;
}

.badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.badge-success {
  background-color: rgba(34, 197, 94, 0.3);
  color: #86efac;
}

.badge-warning {
  background-color: rgba(245, 158, 11, 0.3);
  color: #fde047;
}

.badge-danger {
  background-color: rgba(249, 115, 22, 0.3);
  color: #fdba74;
}

.badge-critical {
  background-color: rgba(220, 38, 38, 0.3);
  color: #fca5a5;
}

.theft-card-body {
  padding: 25px;
}

.description {
  color: #aaa;
  margin-bottom: 20px;
  line-height: 1.5;
}

.stats {
  margin-bottom: 20px;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-row:last-child {
  border-bottom: none;
}

.stat-label {
  color: #888;
  font-size: 0.9rem;
}

.stat-value {
  font-weight: 700;
  color: #fff;
}

.stat-value.success {
  color: #86efac;
}

.stat-value.warning {
  color: #fde047;
}

.stat-value.danger {
  color: #fdba74;
}

.stat-value.critical {
  color: #fca5a5;
}

.theft-button {
  width: 100%;
  padding: 15px;
  background: linear-gradient(135deg, #ff6b35 0%, #ff4500 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.theft-button:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(255, 107, 53, 0.5);
}

.theft-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.garage-link {
  background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
  border-left: 4px solid #3b82f6;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  flex-wrap: wrap;
  gap: 15px;
}

.garage-title {
  font-weight: 700;
  color: #93c5fd;
  margin-bottom: 5px;
  font-size: 1.1rem;
}

.garage-subtitle {
  color: #dbeafe;
  font-size: 0.9rem;
}

.garage-button {
  padding: 12px 25px;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.garage-button:hover {
  background-color: #2563eb;
  transform: translateY(-2px);
}

.info-section {
  background: linear-gradient(145deg, #2a2a2a, #1f1f1f);
  border-radius: 12px;
  padding: 25px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.info-section h3 {
  margin-top: 0;
  margin-bottom: 15px;
  color: #ff6b35;
  font-size: 1.2rem;
}

.info-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.info-section li {
  padding: 10px 0 10px 25px;
  position: relative;
  color: #ccc;
  line-height: 1.6;
}

.info-section li::before {
  content: '▸';
  position: absolute;
  left: 5px;
  color: #ff6b35;
  font-weight: bold;
}

@media (max-width: 768px) {
  .header h1 {
    font-size: 1.5rem;
  }
  
  .theft-grid {
    grid-template-columns: 1fr;
  }
  
  .garage-link {
    flex-direction: column;
    text-align: center;
  }
}
</style>

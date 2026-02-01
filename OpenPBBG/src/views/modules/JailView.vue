<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const router = useRouter();

const loading = ref(true);
const error = ref(null);
const processing = ref(false);
const selectedPlayer = ref(null);

const jailedPlayers = ref([]);
const player = ref(null);
const playerStatus = ref(null);
const timeRemaining = ref(0);
const flashMessage = ref(null);
const flashType = ref('success');

// Format money with commas
const formatMoney = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

// Format time remaining
const formatTime = (seconds) => {
  if (seconds <= 0) return '0s';
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;
  
  if (hours > 0) {
    return `${hours}h ${minutes}m ${secs}s`;
  } else if (minutes > 0) {
    return `${minutes}m ${secs}s`;
  } else {
    return `${secs}s`;
  }
};

// Calculate bail cost
const bailCost = computed(() => {
  return timeRemaining.value * 100;
});

// Load jail data
const loadJailData = async () => {
  try {
    loading.value = true;
    error.value = null;
    const response = await api.get('/modules/jail');
    
    jailedPlayers.value = response.data.jailedPlayers || [];
    player.value = response.data.player || null;
    playerStatus.value = response.data.playerStatus || null;
    timeRemaining.value = playerStatus.value?.time_remaining || 0;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load jail data';
    console.error('Error loading jail data:', err);
  } finally {
    loading.value = false;
  }
};

// Countdown timer
let interval = null;
onMounted(async () => {
  await loadJailData();
  
  if (timeRemaining.value > 0) {
    interval = setInterval(() => {
      if (timeRemaining.value > 0) {
        timeRemaining.value--;
      } else {
        clearInterval(interval);
        // Reload data when timer expires
        loadJailData();
      }
    }, 1000);
  }
});

onUnmounted(() => {
  if (interval) {
    clearInterval(interval);
  }
});

// Show flash message
const showFlash = (message, type = 'success') => {
  flashMessage.value = message;
  flashType.value = type;
  setTimeout(() => {
    flashMessage.value = null;
  }, 5000);
};

// Bust out action
const bustOut = async (playerId) => {
  if (processing.value) return;
  
  processing.value = true;
  selectedPlayer.value = playerId;
  
  try {
    const response = await api.post(`/modules/jail/bustout/${playerId}`);
    showFlash(response.data.message, 'success');
    await loadJailData();
  } catch (err) {
    const message = err.response?.data?.message || 'Failed to bust out player';
    showFlash(message, 'error');
  } finally {
    processing.value = false;
    selectedPlayer.value = null;
  }
};

// Bail out action
const bailOutAction = async () => {
  if (processing.value) return;
  
  processing.value = true;
  
  try {
    const response = await api.post('/modules/jail/bailout');
    showFlash(response.data.message, 'success');
    await loadJailData();
  } catch (err) {
    const message = err.response?.data?.message || 'Failed to bail out';
    showFlash(message, 'error');
  } finally {
    processing.value = false;
  }
};

const goToDashboard = () => {
  router.push('/dashboard');
};
</script>

<template>
  <div class="jail-view">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">🚔 Jail</h1>
      <button @click="goToDashboard" class="back-button">
        ← Dashboard
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading jail data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <div class="error-icon">⚠️</div>
      <p>{{ error }}</p>
      <button @click="loadJailData" class="retry-button">Retry</button>
    </div>

    <!-- Main Content -->
    <div v-else class="content">
      <!-- Flash Messages -->
      <div v-if="flashMessage" class="flash-message" :class="flashType">
        {{ flashMessage }}
      </div>

      <!-- Player Jail Status -->
      <div v-if="playerStatus && playerStatus.in_jail" class="jail-status">
        <div class="jail-status-header">
          <svg class="jail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
          </svg>
          <div>
            <h3 class="jail-status-title">
              🚔 You are in {{ playerStatus.in_super_max ? 'Super Max' : 'Jail' }}!
            </h3>
            <p class="jail-status-time">
              Time remaining: <span class="time-remaining">{{ formatTime(timeRemaining) }}</span>
            </p>
          </div>
        </div>

        <!-- Bail Out Option -->
        <div v-if="!playerStatus.in_super_max" class="bail-option">
          <div class="bail-info">
            <p class="bail-title">Bail Out</p>
            <p class="bail-cost">Cost: {{ formatMoney(bailCost) }} ({{ formatMoney(100) }}/second)</p>
            <p class="bail-cash">Your Cash: {{ formatMoney(player.cash) }}</p>
          </div>
          <button
            @click="bailOutAction"
            :disabled="processing || player.cash < bailCost"
            class="bail-button"
          >
            {{ processing ? 'Processing...' : 'Pay Bail' }}
          </button>
        </div>

        <!-- Super Max Warning -->
        <div v-else class="super-max-warning">
          <p>
            ⚠️ You are in Super Max. You cannot bail out and must serve your full sentence.
          </p>
        </div>
      </div>

      <!-- Not in Jail Message -->
      <div v-else class="not-jailed">
        <p>✓ You are not currently in jail.</p>
      </div>

      <!-- Jailed Players List -->
      <div class="jailed-players-section">
        <div class="section-header">
          <h3 class="section-title">
            Players in Jail ({{ jailedPlayers.length.toLocaleString() }})
          </h3>
        </div>

        <div v-if="jailedPlayers.length === 0" class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <p>No players are currently in jail.</p>
        </div>

        <div v-else class="inmates-list">
          <div
            v-for="inmate in jailedPlayers"
            :key="inmate.id"
            class="inmate-card"
            :class="{
              'is-current-user': inmate.is_current_user,
              'is-super-max': inmate.is_super_max,
            }"
          >
            <div class="inmate-info">
              <div class="inmate-header">
                <h4 class="inmate-name">
                  {{ inmate.username }}
                  <span v-if="inmate.is_current_user" class="you-badge">(You)</span>
                  <span v-if="inmate.is_super_max" class="super-max-badge">
                    SUPER MAX
                  </span>
                </h4>
              </div>
              <div class="inmate-details">
                <p><span class="detail-label">Rank:</span> {{ inmate.rank || 'Thug' }}</p>
                <p><span class="detail-label">Time Remaining:</span> {{ formatTime(inmate.time_remaining) }}</p>
                <p v-if="!inmate.is_super_max && inmate.bust_chance > 0" class="bust-chance">
                  <span class="detail-label">Bust Chance:</span> {{ inmate.bust_chance.toFixed(1) }}%
                </p>
              </div>
            </div>

            <!-- Bust Out Button -->
            <div class="inmate-actions">
              <button
                v-if="!inmate.is_super_max"
                @click="bustOut(inmate.id)"
                :disabled="processing || inmate.bust_chance === 0 || playerStatus?.in_super_max"
                class="bust-button"
                :class="{ 'processing': processing && selectedPlayer === inmate.id }"
              >
                {{ processing && selectedPlayer === inmate.id ? 'Busting...' : 'Bust Out' }}
              </button>
              <p v-if="!inmate.is_super_max && inmate.bust_chance === 0" class="cant-bust">
                Can't bust
              </p>
              <span v-if="inmate.is_super_max" class="no-bust">Cannot bust out</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Jail Info -->
      <div class="jail-info">
        <h4 class="info-title">How Jail Works:</h4>
        <ul class="info-list">
          <li>You go to jail when caught committing crimes</li>
          <li>You can pay bail to get out early ({{ formatMoney(100) }} per second remaining)</li>
          <li>Other players can attempt to bust you out</li>
          <li>Failed bust attempts send the rescuer to jail for 90 seconds</li>
          <li>Failed bust attempts while already in jail extend your time and send you to Super Max</li>
          <li>Super Max prisoners cannot be busted out and cannot pay bail</li>
          <li>Bust success chance decreases with higher ranked targets</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style scoped>
.jail-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 1rem;
}

.page-header {
  max-width: 1200px;
  margin: 0 auto 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-title {
  font-size: 2.5rem;
  font-weight: bold;
  color: white;
  margin: 0;
}

.back-button {
  padding: 0.75rem 1.5rem;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.back-button:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.content {
  max-width: 1200px;
  margin: 0 auto;
}

.loading-state,
.error-state {
  background: white;
  border-radius: 12px;
  padding: 3rem;
  text-align: center;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f4f6;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state {
  color: #dc2626;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.retry-button {
  margin-top: 1rem;
  padding: 0.75rem 2rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
}

.retry-button:hover {
  background: #5568d3;
}

.flash-message {
  padding: 1rem 1.5rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-weight: 600;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.flash-message.success {
  background: #d1fae5;
  color: #065f46;
  border: 2px solid #10b981;
}

.flash-message.error {
  background: #fee2e2;
  color: #991b1b;
  border: 2px solid #ef4444;
}

.jail-status {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border-left: 6px solid #dc2626;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 40px rgba(220, 38, 38, 0.2);
}

.jail-status-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.jail-icon {
  width: 3rem;
  height: 3rem;
  color: #dc2626;
  flex-shrink: 0;
}

.jail-status-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: #7f1d1d;
  margin: 0 0 0.5rem 0;
}

.jail-status-time {
  color: #991b1b;
  font-size: 1.125rem;
}

.time-remaining {
  font-weight: bold;
  color: #7f1d1d;
}

.bail-option {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
  border: 2px solid #fecaca;
}

.bail-info {
  flex: 1;
}

.bail-title {
  font-weight: 700;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 1.125rem;
}

.bail-cost {
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.bail-cash {
  color: #9ca3af;
  font-size: 0.875rem;
}

.bail-button {
  padding: 0.75rem 1.5rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.bail-button:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.bail-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.super-max-warning {
  background: #fef2f2;
  border: 2px solid #dc2626;
  border-radius: 8px;
  padding: 1.5rem;
}

.super-max-warning p {
  color: #7f1d1d;
  font-weight: 700;
  margin: 0;
}

.not-jailed {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  border-left: 6px solid #10b981;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 40px rgba(16, 185, 129, 0.2);
}

.not-jailed p {
  color: #065f46;
  font-weight: 700;
  margin: 0;
  font-size: 1.125rem;
}

.jailed-players-section {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 1.5rem 0;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
}

.empty-icon {
  width: 4rem;
  height: 4rem;
  margin: 0 auto 1rem;
  color: #d1d5db;
}

.empty-state p {
  font-size: 1.125rem;
  margin: 0;
}

.inmates-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.inmate-card {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
  background: white;
}

.inmate-card:hover {
  border-color: #9ca3af;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.inmate-card.is-current-user {
  background: #fef3c7;
  border-color: #fbbf24;
}

.inmate-card.is-super-max {
  background: #fee2e2;
  border-color: #dc2626;
}

.inmate-info {
  flex: 1;
}

.inmate-header {
  margin-bottom: 0.75rem;
}

.inmate-name {
  font-size: 1.25rem;
  font-weight: bold;
  color: #111827;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.you-badge {
  font-size: 0.875rem;
  color: #92400e;
  font-weight: normal;
}

.super-max-badge {
  background: #dc2626;
  color: white;
  font-size: 0.75rem;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-weight: bold;
}

.inmate-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.875rem;
  color: #4b5563;
}

.detail-label {
  font-weight: 700;
  color: #374151;
}

.bust-chance {
  color: #059669;
}

.inmate-actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
}

.bust-button {
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.bust-button:hover:not(:disabled) {
  background: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.bust-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.bust-button.processing {
  opacity: 0.7;
}

.cant-bust {
  font-size: 0.75rem;
  color: #dc2626;
  margin: 0;
  text-align: center;
}

.no-bust {
  color: #dc2626;
  font-weight: 700;
  font-size: 0.875rem;
}

.jail-info {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  border-left: 6px solid #3b82f6;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 10px 40px rgba(59, 130, 246, 0.2);
}

.info-title {
  font-weight: bold;
  color: #1e3a8a;
  margin: 0 0 1rem 0;
  font-size: 1.25rem;
}

.info-list {
  color: #1e40af;
  font-size: 0.875rem;
  padding-left: 1.5rem;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.info-list li {
  line-height: 1.5;
}

@media (max-width: 768px) {
  .jail-view {
    padding: 1rem 0.5rem;
  }

  .page-title {
    font-size: 2rem;
  }

  .jail-status-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .bail-option {
    flex-direction: column;
    align-items: stretch;
  }

  .bail-button {
    width: 100%;
  }

  .inmate-card {
    flex-direction: column;
    align-items: stretch;
  }

  .inmate-actions {
    align-items: stretch;
  }

  .bust-button {
    width: 100%;
  }
}
</style>

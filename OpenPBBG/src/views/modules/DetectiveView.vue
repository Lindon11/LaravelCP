<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const router = useRouter();

const player = ref(null);
const reports = ref([]);
const cost = ref(5000);
const investigationTime = ref(5);
const targetId = ref('');
const loading = ref(true);
const processing = ref(false);
const error = ref('');
const successMessage = ref('');
const currentTime = ref(Date.now());

const formatMoney = (val) => {
  return new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'USD', 
    minimumFractionDigits: 0 
  }).format(val);
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num);
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const activeReports = computed(() => {
  return reports.value.filter(r => r.status === 'investigating');
});

const completedReports = computed(() => {
  return reports.value.filter(r => r.status === 'complete');
});

const fetchData = async () => {
  try {
    loading.value = true;
    error.value = '';
    const response = await api.get('/detective');
    player.value = response.data.player;
    reports.value = response.data.reports || [];
    cost.value = response.data.cost || 5000;
    investigationTime.value = response.data.investigationTime || 5;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load detective data';
  } finally {
    loading.value = false;
  }
};

const hireDetective = async () => {
  if (processing.value || !targetId.value) return;
  
  try {
    processing.value = true;
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post('/detective/hire', { 
      target_id: targetId.value 
    });
    
    successMessage.value = response.data.message || 'Detective hired successfully!';
    targetId.value = '';
    await fetchData();
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to hire detective';
  } finally {
    processing.value = false;
  }
};

let interval;
onMounted(() => {
  fetchData();
  interval = setInterval(() => {
    currentTime.value = Date.now();
  }, 1000);
});

onUnmounted(() => {
  if (interval) clearInterval(interval);
});
</script>

<template>
  <div class="detective-view">
    <div class="header">
      <div class="header-content">
        <h1>🕵️ Detective</h1>
        <router-link to="/dashboard" class="back-link">← Dashboard</router-link>
      </div>
    </div>

    <div class="container">
      <!-- Loading State -->
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Loading...</p>
      </div>

      <template v-else-if="player">
        <!-- Messages -->
        <div v-if="successMessage" class="message success">{{ successMessage }}</div>
        <div v-if="error" class="message error">{{ error }}</div>

        <!-- Hire Detective -->
        <div class="hire-section">
          <div class="hire-header">
            <div class="detective-icon">🕵️</div>
            <div class="hire-info">
              <h2>Hire a Private Detective</h2>
              <p class="cost">Find any player's location for {{ formatMoney(cost) }}</p>
              <p class="time-info">Investigation takes {{ investigationTime }} minutes</p>
            </div>
          </div>
          <div class="hire-form">
            <input 
              v-model="targetId" 
              type="number" 
              placeholder="Enter Player ID"
              @keyup.enter="hireDetective"
            >
            <button 
              @click="hireDetective" 
              :disabled="processing || !targetId"
              class="hire-btn"
            >
              {{ processing ? 'Hiring...' : 'Hire Detective' }}
            </button>
          </div>
        </div>

        <!-- Active Investigations -->
        <div v-if="activeReports.length > 0" class="reports-section active-section">
          <h3>🔍 Active Investigations ({{ activeReports.length }})</h3>
          
          <div class="reports-grid">
            <div v-for="report in activeReports" :key="report.id" class="report-card active">
              <div class="report-header">
                <div class="target-info">
                  <h4>{{ report.target }}</h4>
                  <span class="target-id">#{{ formatNumber(report.target_id) }}</span>
                </div>
                <span class="status-badge investigating">INVESTIGATING</span>
              </div>
              <div class="report-body">
                <p class="time-remaining">
                  ⏱️ Report ready in: <strong>{{ formatTime(report.time_remaining) }}</strong>
                </p>
                <p class="hired-at">Hired {{ report.hired_at }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Completed Reports -->
        <div class="reports-section">
          <h3>📋 My Detective Reports ({{ completedReports.length }})</h3>
          
          <div v-if="completedReports.length === 0" class="empty-state">
            <p class="empty-title">No completed reports yet</p>
            <p class="empty-subtitle">Hire a detective to track down other players</p>
          </div>

          <div v-else class="reports-grid">
            <div v-for="report in completedReports" :key="report.id" class="report-card complete">
              <div class="report-header">
                <div class="target-info">
                  <h4>{{ report.target }}</h4>
                  <span class="target-id">#{{ formatNumber(report.target_id) }}</span>
                </div>
                <span class="status-badge complete">COMPLETE</span>
              </div>
              <div class="report-body">
                <p v-if="report.location_info" class="location">
                  📍 {{ report.location_info }}
                </p>
                <p class="hired-at">Hired {{ report.hired_at }}</p>
              </div>
            </div>
          </div>
        </div>
      </template>

      <div v-else class="error-state">
        <p>Failed to load player data</p>
        <button @click="fetchData" class="retry-btn">Retry</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.detective-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding-bottom: 3rem;
}

.header {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  padding: 1.5rem 0;
  margin-bottom: 2rem;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header h1 {
  font-size: 2rem;
  font-weight: bold;
  color: white;
  margin: 0;
}

.back-link {
  color: white;
  text-decoration: none;
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  background: rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.back-link:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateX(-4px);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.loading {
  text-align: center;
  padding: 4rem 2rem;
  color: white;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.message {
  padding: 1rem 1.5rem;
  border-radius: 0.5rem;
  margin-bottom: 1.5rem;
  font-weight: 500;
}

.message.success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #10b981;
}

.message.error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #ef4444;
}

.hire-section {
  background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  margin-bottom: 2rem;
}

.hire-header {
  display: flex;
  align-items: center;
  margin-bottom: 2rem;
  gap: 1.5rem;
}

.detective-icon {
  font-size: 4rem;
  line-height: 1;
}

.hire-info {
  flex: 1;
}

.hire-info h2 {
  font-size: 2rem;
  font-weight: bold;
  color: white;
  margin: 0 0 0.5rem 0;
}

.hire-info .cost {
  font-size: 1.25rem;
  color: #d1d5db;
  margin: 0.25rem 0;
}

.hire-info .time-info {
  font-size: 0.875rem;
  color: #9ca3af;
  margin: 0;
}

.hire-form {
  display: flex;
  gap: 1rem;
}

.hire-form input {
  flex: 1;
  padding: 1rem 1.5rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 1.125rem;
  outline: none;
  background: white;
}

.hire-btn {
  padding: 1rem 2rem;
  background: #fbbf24;
  color: #1f2937;
  border: none;
  border-radius: 0.5rem;
  font-weight: bold;
  font-size: 1.125rem;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.hire-btn:hover:not(:disabled) {
  background: #f59e0b;
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(251, 191, 36, 0.3);
}

.hire-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.reports-section {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  margin-bottom: 2rem;
}

.active-section {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.reports-section h3 {
  font-size: 1.75rem;
  font-weight: bold;
  margin: 0 0 1.5rem 0;
  color: #1f2937;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #6b7280;
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
}

.empty-subtitle {
  font-size: 0.875rem;
  margin: 0;
}

.reports-grid {
  display: grid;
  gap: 1rem;
}

.report-card {
  border: 2px solid;
  border-radius: 0.75rem;
  padding: 1.5rem;
  transition: all 0.3s ease;
}

.report-card.active {
  border-color: #fbbf24;
  background: #fffbeb;
}

.report-card.complete {
  border-color: #10b981;
  background: #ecfdf5;
}

.report-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.report-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.target-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.target-info h4 {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0;
  color: #1f2937;
}

.target-id {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
}

.status-badge.investigating {
  background: #fbbf24;
  color: #1f2937;
}

.status-badge.complete {
  background: #10b981;
  color: white;
}

.report-body {
  color: #374151;
}

.time-remaining {
  font-size: 1rem;
  font-weight: 600;
  color: #92400e;
  margin: 0 0 0.5rem 0;
}

.location {
  font-size: 1rem;
  font-weight: 600;
  color: #065f46;
  margin: 0 0 0.5rem 0;
}

.hired-at {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.error-state {
  text-align: center;
  padding: 4rem 2rem;
  color: white;
}

.retry-btn {
  margin-top: 1rem;
  padding: 0.75rem 2rem;
  background: white;
  color: #667eea;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

@media (max-width: 768px) {
  .header h1 {
    font-size: 1.5rem;
  }

  .hire-header {
    flex-direction: column;
    text-align: center;
  }

  .hire-info h2 {
    font-size: 1.5rem;
  }

  .hire-form {
    flex-direction: column;
  }

  .hire-btn {
    width: 100%;
  }

  .report-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .target-info {
    width: 100%;
  }
}
</style>

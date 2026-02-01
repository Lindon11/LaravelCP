<template>
  <div class="organized-crime-container">
    <div class="page-header">
      <h1>🎭 Organized Crime</h1>
      <router-link to="/dashboard" class="back-link">← Back to Dashboard</router-link>
    </div>

    <div class="content-wrapper">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading organized crimes...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button @click="fetchCrimes" class="retry-btn">Retry</button>
      </div>

      <!-- Content -->
      <div v-else>
        <!-- Flash Messages -->
        <div v-if="successMessage" class="flash-message success">
          {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="flash-message error">
          {{ errorMessage }}
        </div>

        <!-- No Gang Warning -->
        <div v-if="!gang" class="warning-card">
          <div class="warning-icon">⚠️</div>
          <div class="warning-content">
            <h3>Gang Required</h3>
            <p>You must be in a gang to attempt organized crimes. Only gang leaders can initiate them.</p>
          </div>
        </div>

        <!-- Gang Content -->
        <div v-else>
          <!-- Gang Info Banner -->
          <div class="gang-banner">
            <div class="gang-info">
              <div class="gang-icon">🎭</div>
              <div>
                <h3>{{ gang.name }}</h3>
                <p>{{ gang.members?.length || 0 }} members</p>
              </div>
            </div>
            <div v-if="isLeader" class="leader-badge">
              👑 Gang Leader
            </div>
          </div>

          <!-- Available Crimes -->
          <div class="crimes-card">
            <div class="card-header">
              <h2>Available Organized Crimes</h2>
              <p class="subtitle">Select a crime to attempt with your gang</p>
            </div>

            <div class="crimes-list">
              <div
                v-for="crime in crimes"
                :key="crime.id"
                class="crime-item"
                :class="{ selected: selectedCrime?.id === crime.id }"
                @click="selectCrime(crime)"
              >
                <div class="crime-header">
                  <h3 class="crime-name">{{ crime.name }}</h3>
                  <span class="success-rate" :style="{ color: getSuccessColor(crime.success_rate) }">
                    {{ crime.success_rate }}% success
                  </span>
                </div>
                <p class="crime-description">{{ crime.description }}</p>
                <div class="crime-stats">
                  <div class="stat-item">
                    <span class="stat-label">Reward:</span>
                    <span class="stat-value">{{ formatMoney(crime.min_reward) }} - {{ formatMoney(crime.max_reward) }}</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-label">Members:</span>
                    <span class="stat-value">{{ crime.required_members }}+</span>
                  </div>
                  <div class="stat-item" v-if="crime.required_rank">
                    <span class="stat-label">Rank:</span>
                    <span class="stat-value">{{ crime.required_rank }}+</span>
                  </div>
                </div>
              </div>

              <div v-if="!crimes || crimes.length === 0" class="empty-state">
                <div class="empty-icon">🎭</div>
                <p>No organized crimes available.</p>
              </div>
            </div>
          </div>

          <!-- Member Selection (Leader Only) -->
          <div v-if="selectedCrime && isLeader" class="members-card">
            <div class="card-header">
              <h2>Select Gang Members</h2>
              <p class="subtitle">Choose {{ selectedCrime.required_members }}+ members to participate</p>
            </div>

            <div class="members-grid">
              <div
                v-for="member in gang.members"
                :key="member.id"
                class="member-card"
                :class="{ selected: selectedMembers.includes(member.id) }"
                @click="toggleMember(member.id)"
              >
                <div class="member-avatar">👤</div>
                <div class="member-info">
                  <div class="member-name">{{ member.username }}</div>
                  <div class="member-rank">{{ member.rank || 'Thug' }}</div>
                </div>
                <div v-if="selectedMembers.includes(member.id)" class="check-icon">✓</div>
              </div>
            </div>

            <button
              @click="attemptCrime"
              :disabled="processing || selectedMembers.length < selectedCrime.required_members"
              class="attempt-btn"
            >
              <span v-if="processing">Attempting...</span>
              <span v-else>
                Attempt Crime ({{ selectedMembers.length }}/{{ selectedCrime.required_members }} selected)
              </span>
            </button>
          </div>

          <!-- Not Leader Message -->
          <div v-else-if="selectedCrime && !isLeader" class="info-card">
            <div class="info-icon">ℹ️</div>
            <p>Only the gang leader can initiate organized crimes.</p>
          </div>

          <!-- Gang Crime History -->
          <div v-if="history && history.length > 0" class="history-card">
            <div class="card-header">
              <h2>Gang Crime History</h2>
              <p class="subtitle">Recent organized crime attempts</p>
            </div>

            <div class="history-list">
              <div
                v-for="(record, index) in history"
                :key="index"
                class="history-item"
                :class="{ success: record.success, failed: !record.success }"
              >
                <div class="history-icon">
                  {{ record.success ? '✅' : '❌' }}
                </div>
                <div class="history-content">
                  <h4 class="history-name">{{ record.name }}</h4>
                  <p class="history-message">{{ record.result_message }}</p>
                  <p class="history-date">{{ formatDate(record.attempted_at) }}</p>
                </div>
                <div class="history-badge" :class="{ success: record.success, failed: !record.success }">
                  {{ record.success ? 'SUCCESS' : 'FAILED' }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';

const loading = ref(true);
const error = ref(null);
const processing = ref(false);
const successMessage = ref(null);
const errorMessage = ref(null);

const player = ref(null);
const crimes = ref([]);
const gang = ref(null);
const history = ref([]);

const selectedCrime = ref(null);
const selectedMembers = ref([]);

const isLeader = computed(() => {
  return gang.value && player.value && gang.value.leader_id === player.value.id;
});

const fetchCrimes = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await api.get('/modules/organized-crime');
    player.value = response.data.player || null;
    crimes.value = response.data.crimes || [];
    gang.value = response.data.gang || null;
    history.value = response.data.history || [];
  } catch (err) {
    console.error('Error fetching organized crimes:', err);
    error.value = err.response?.data?.message || 'Failed to load organized crimes. Please try again.';
  } finally {
    loading.value = false;
  }
};

const selectCrime = (crime) => {
  if (!isLeader.value) {
    errorMessage.value = 'Only the gang leader can select crimes.';
    setTimeout(() => errorMessage.value = null, 3000);
    return;
  }
  
  selectedCrime.value = crime;
  selectedMembers.value = [];
};

const toggleMember = (memberId) => {
  const index = selectedMembers.value.indexOf(memberId);
  if (index > -1) {
    selectedMembers.value.splice(index, 1);
  } else {
    selectedMembers.value.push(memberId);
  }
};

const attemptCrime = async () => {
  if (processing.value || !selectedCrime.value || selectedMembers.value.length < selectedCrime.value.required_members) {
    return;
  }

  processing.value = true;
  successMessage.value = null;
  errorMessage.value = null;

  try {
    const response = await api.post(`/modules/organized-crime/${selectedCrime.value.id}/attempt`, {
      participants: selectedMembers.value
    });

    successMessage.value = response.data.message || 'Crime attempted successfully!';
    
    // Reset selection
    selectedCrime.value = null;
    selectedMembers.value = [];
    
    // Refresh data
    await fetchCrimes();
    
    setTimeout(() => successMessage.value = null, 5000);
  } catch (err) {
    console.error('Error attempting crime:', err);
    errorMessage.value = err.response?.data?.message || 'Failed to attempt crime. Please try again.';
    setTimeout(() => errorMessage.value = null, 5000);
  } finally {
    processing.value = false;
  }
};

const formatMoney = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getSuccessColor = (rate) => {
  if (rate >= 70) return '#10b981';
  if (rate >= 40) return '#f59e0b';
  return '#ef4444';
};

onMounted(() => {
  fetchCrimes();
});
</script>

<style scoped>
.organized-crime-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 50%, #fca5a5 100%);
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

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 800;
  color: #7f1d1d;
  margin: 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.back-link {
  color: #dc2626;
  text-decoration: none;
  font-weight: 600;
  font-size: 1rem;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  background: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.back-link:hover {
  background: #fef2f2;
  transform: translateX(-4px);
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
}

.loading-state,
.error-state {
  background: white;
  border-radius: 1rem;
  padding: 3rem;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #fee2e2;
  border-top: 4px solid #ef4444;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state p {
  color: #991b1b;
  font-size: 1.1rem;
  margin-bottom: 1rem;
}

.retry-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  background: #dc2626;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.flash-message {
  padding: 1rem 1.5rem;
  border-radius: 0.5rem;
  margin-bottom: 1.5rem;
  font-weight: 500;
}

.flash-message.success {
  background: #d1fae5;
  border: 2px solid #10b981;
  color: #065f46;
}

.flash-message.error {
  background: #fee2e2;
  border: 2px solid #ef4444;
  color: #991b1b;
}

.warning-card {
  background: #fef3c7;
  border: 2px solid #f59e0b;
  border-radius: 1rem;
  padding: 2rem;
  display: flex;
  gap: 1.5rem;
  align-items: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.warning-icon {
  font-size: 3rem;
  flex-shrink: 0;
}

.warning-content h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #92400e;
  margin: 0 0 0.5rem;
}

.warning-content p {
  color: #78350f;
  margin: 0;
  line-height: 1.6;
}

.gang-banner {
  background: linear-gradient(135deg, #1f2937, #374151);
  color: white;
  border-radius: 1rem;
  padding: 1.5rem;
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.gang-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.gang-icon {
  font-size: 2.5rem;
}

.gang-info h3 {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.gang-info p {
  color: #d1d5db;
  font-size: 0.875rem;
  margin: 0;
}

.leader-badge {
  background: #fbbf24;
  color: #78350f;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 700;
  font-size: 0.875rem;
}

.crimes-card,
.members-card,
.history-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 2rem;
}

.card-header {
  padding: 2rem;
  background: linear-gradient(135deg, #fef2f2, #fee2e2);
  border-bottom: 2px solid #fecaca;
}

.card-header h2 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #7f1d1d;
  margin: 0 0 0.5rem;
}

.subtitle {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0;
}

.crimes-list {
  padding: 1.5rem;
}

.crime-item {
  border: 2px solid #fee2e2;
  border-radius: 0.75rem;
  padding: 1.5rem;
  margin-bottom: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.crime-item:hover {
  border-color: #ef4444;
  background: #fef2f2;
  transform: translateX(4px);
}

.crime-item.selected {
  border-color: #7c3aed;
  background: #f5f3ff;
}

.crime-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.crime-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: #7f1d1d;
  margin: 0;
}

.success-rate {
  font-size: 0.875rem;
  font-weight: 700;
}

.crime-description {
  color: #64748b;
  margin: 0 0 1rem;
  line-height: 1.6;
}

.crime-stats {
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stat-label {
  color: #94a3b8;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  color: #0f172a;
  font-weight: 700;
  font-size: 0.95rem;
}

.members-grid {
  padding: 1.5rem;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.member-card {
  border: 2px solid #fee2e2;
  border-radius: 0.75rem;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  position: relative;
}

.member-card:hover {
  border-color: #ef4444;
  background: #fef2f2;
}

.member-card.selected {
  border-color: #7c3aed;
  background: #f5f3ff;
}

.member-avatar {
  font-size: 2rem;
  flex-shrink: 0;
}

.member-info {
  flex: 1;
}

.member-name {
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.25rem;
}

.member-rank {
  color: #64748b;
  font-size: 0.875rem;
}

.check-icon {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  color: #7c3aed;
  font-size: 1.5rem;
  font-weight: 700;
}

.attempt-btn {
  width: 100%;
  padding: 1.25rem;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border: none;
  border-radius: 0 0 1rem 1rem;
  font-size: 1.125rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.attempt-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.attempt-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
  transform: none;
}

.info-card {
  background: #dbeafe;
  border: 2px solid #3b82f6;
  border-radius: 1rem;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 2rem;
}

.info-icon {
  font-size: 2rem;
}

.info-card p {
  color: #1e3a8a;
  font-weight: 600;
  margin: 0;
}

.history-list {
  padding: 1.5rem;
}

.history-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  border-radius: 0.75rem;
  margin-bottom: 1rem;
}

.history-item.success {
  background: #d1fae5;
  border: 2px solid #10b981;
}

.history-item.failed {
  background: #fee2e2;
  border: 2px solid #ef4444;
}

.history-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.history-content {
  flex: 1;
}

.history-name {
  font-size: 1.125rem;
  font-weight: 700;
  margin: 0 0 0.25rem;
}

.history-item.success .history-name {
  color: #065f46;
}

.history-item.failed .history-name {
  color: #991b1b;
}

.history-message {
  margin: 0 0 0.5rem;
  line-height: 1.5;
}

.history-item.success .history-message {
  color: #047857;
}

.history-item.failed .history-message {
  color: #b91c1c;
}

.history-date {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0;
}

.history-badge {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 700;
  font-size: 0.75rem;
  text-transform: uppercase;
  flex-shrink: 0;
}

.history-badge.success {
  background: #10b981;
  color: white;
}

.history-badge.failed {
  background: #ef4444;
  color: white;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state p {
  color: #94a3b8;
  font-size: 1.1rem;
}

@media (max-width: 768px) {
  .page-header h1 {
    font-size: 2rem;
  }

  .gang-banner {
    flex-direction: column;
    gap: 1rem;
  }

  .crime-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .crime-stats {
    gap: 1rem;
  }

  .members-grid {
    grid-template-columns: 1fr;
  }

  .history-item {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

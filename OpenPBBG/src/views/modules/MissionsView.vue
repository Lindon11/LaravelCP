<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const router = useRouter();

const missions = ref([]);
const stats = ref({
  total_missions: 0,
  completed: 0,
  in_progress: 0,
  completion_rate: 0
});
const selectedType = ref('all');
const loading = ref(true);
const error = ref(null);
const startingMission = ref(false);

const filteredMissions = computed(() => {
  if (selectedType.value === 'all') {
    return missions.value;
  }
  return missions.value.filter(m => m.type === selectedType.value);
});

const missionsByType = computed(() => {
  return {
    one_time: missions.value.filter(m => m.type === 'one_time'),
    daily: missions.value.filter(m => m.type === 'daily'),
    repeatable: missions.value.filter(m => m.type === 'repeatable'),
    story: missions.value.filter(m => m.type === 'story'),
  };
});

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num);
};

const getMissionTypeLabel = (type) => {
  const labels = {
    one_time: 'One Time',
    daily: 'Daily',
    repeatable: 'Repeatable',
    story: 'Story',
  };
  return labels[type] || type;
};

const getProgressPercentage = (progress, required) => {
  return Math.min(100, (progress / required) * 100);
};

const getStatusLabel = (status) => {
  const labels = {
    available: 'Available',
    in_progress: 'In Progress',
    completed: 'Completed',
  };
  return labels[status] || status;
};

const fetchMissions = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get('/missions');
    missions.value = response.data.missions || [];
    stats.value = response.data.stats || stats.value;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load missions';
    console.error('Error fetching missions:', err);
  } finally {
    loading.value = false;
  }
};

const startMission = async (mission) => {
  if (!confirm(`Start mission: ${mission.name}?`)) return;

  startingMission.value = true;
  error.value = null;
  
  try {
    await api.post('/missions/start', {
      mission_id: mission.id
    });
    
    // Refresh missions after starting
    await fetchMissions();
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to start mission';
    console.error('Error starting mission:', err);
  } finally {
    startingMission.value = false;
  }
};

const goToDashboard = () => {
  router.push('/dashboard');
};

onMounted(() => {
  fetchMissions();
});
</script>

<template>
  <div class="missions-view">
    <div class="container">
      <!-- Header -->
      <div class="header">
        <h1 class="title">📋 Missions</h1>
        <button @click="goToDashboard" class="back-button">
          ← Dashboard
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Loading missions...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-message">
        <p>{{ error }}</p>
        <button @click="fetchMissions" class="retry-button">Retry</button>
      </div>

      <!-- Content -->
      <template v-else>
        <!-- Stats Overview -->
        <div class="stats-card">
          <h2 class="stats-title">Your Progress</h2>
          <div class="stats-grid">
            <div class="stat-item">
              <p class="stat-label">Total Missions</p>
              <p class="stat-value stat-value-purple">{{ formatNumber(stats.total_missions) }}</p>
            </div>
            <div class="stat-item">
              <p class="stat-label">Completed</p>
              <p class="stat-value stat-value-green">{{ formatNumber(stats.completed) }}</p>
            </div>
            <div class="stat-item">
              <p class="stat-label">In Progress</p>
              <p class="stat-value stat-value-yellow">{{ formatNumber(stats.in_progress) }}</p>
            </div>
            <div class="stat-item">
              <p class="stat-label">Completion Rate</p>
              <p class="stat-value stat-value-cyan">{{ stats.completion_rate }}%</p>
            </div>
          </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
          <button 
            @click="selectedType = 'all'"
            :class="{ active: selectedType === 'all' }"
            class="tab-button">
            All ({{ missions.length }})
          </button>
          <button 
            @click="selectedType = 'one_time'"
            :class="{ active: selectedType === 'one_time' }"
            class="tab-button">
            One Time ({{ missionsByType.one_time.length }})
          </button>
          <button 
            @click="selectedType = 'daily'"
            :class="{ active: selectedType === 'daily' }"
            class="tab-button">
            Daily ({{ missionsByType.daily.length }})
          </button>
          <button 
            @click="selectedType = 'repeatable'"
            :class="{ active: selectedType === 'repeatable' }"
            class="tab-button">
            Repeatable ({{ missionsByType.repeatable.length }})
          </button>
        </div>

        <!-- Empty State -->
        <div v-if="filteredMissions.length === 0" class="empty-state">
          <p class="empty-title">No missions available</p>
          <p class="empty-subtitle">Level up or complete other missions to unlock more</p>
        </div>

        <!-- Missions Grid -->
        <div v-else class="missions-grid">
          <div v-for="mission in filteredMissions" :key="mission.id" class="mission-card">
            <!-- Mission Header -->
            <div class="mission-header">
              <div class="mission-title-section">
                <h3 class="mission-title">{{ mission.name }}</h3>
                <div class="mission-badges">
                  <span :class="`badge badge-${mission.type}`">
                    {{ getMissionTypeLabel(mission.type) }}
                  </span>
                  <span class="badge badge-gray">
                    Level {{ mission.required_level }}+
                  </span>
                  <span v-if="mission.location" class="mission-location">
                    📍 {{ mission.location }}
                  </span>
                </div>
              </div>
              <span :class="`mission-status mission-status-${mission.status}`">
                {{ getStatusLabel(mission.status) }}
              </span>
            </div>

            <!-- Description -->
            <p class="mission-description">{{ mission.description }}</p>

            <!-- Progress Bar (for in progress missions) -->
            <div v-if="mission.status === 'in_progress'" class="progress-section">
              <div class="progress-header">
                <span class="progress-label">Progress</span>
                <span class="progress-value">
                  {{ formatNumber(mission.progress) }} / {{ formatNumber(mission.objective_count) }}
                </span>
              </div>
              <div class="progress-bar">
                <div 
                  class="progress-fill"
                  :style="`width: ${getProgressPercentage(mission.progress, mission.objective_count)}%`">
                </div>
              </div>
            </div>

            <!-- Objective -->
            <div class="objective-box">
              <p class="objective-label">Objective:</p>
              <p class="objective-text">
                {{ mission.objective_type.replace('_', ' ').toUpperCase() }} 
                × {{ formatNumber(mission.objective_count) }}
              </p>
            </div>

            <!-- Rewards -->
            <div class="rewards-section">
              <p class="rewards-label">Rewards:</p>
              <div class="rewards-grid">
                <div v-if="mission.rewards.cash > 0" class="reward-item">
                  <span class="reward-icon">💵</span>
                  <span>${{ formatNumber(mission.rewards.cash) }}</span>
                </div>
                <div v-if="mission.rewards.respect > 0" class="reward-item">
                  <span class="reward-icon">⭐</span>
                  <span>{{ formatNumber(mission.rewards.respect) }} Respect</span>
                </div>
                <div v-if="mission.rewards.experience > 0" class="reward-item">
                  <span class="reward-icon">📊</span>
                  <span>{{ formatNumber(mission.rewards.experience) }} XP</span>
                </div>
                <div v-if="mission.rewards.item" class="reward-item">
                  <span class="reward-icon">🎁</span>
                  <span>{{ mission.rewards.item }} ({{ mission.rewards.item_quantity }})</span>
                </div>
              </div>
            </div>

            <!-- Cooldown Info -->
            <div v-if="mission.cooldown_hours > 0 && mission.available_again_at" class="cooldown-info">
              Next available: {{ new Date(mission.available_again_at).toLocaleString() }}
            </div>

            <!-- Action Button -->
            <button 
              v-if="mission.status === 'available' && mission.can_start"
              @click="startMission(mission)"
              :disabled="startingMission"
              class="action-button action-button-primary">
              {{ startingMission ? 'Starting...' : 'Start Mission' }}
            </button>
            <div v-else-if="mission.status === 'in_progress'" class="action-button action-button-progress">
              In Progress - Keep Going!
            </div>
            <div v-else-if="mission.status === 'completed'" class="action-button action-button-completed">
              ✅ Completed
            </div>
            <div v-else-if="!mission.can_start" class="action-button action-button-disabled">
              On Cooldown
            </div>
          </div>
        </div>

        <!-- Info Box -->
        <div class="info-box">
          <h3 class="info-title">💡 Mission Tips</h3>
          <ul class="info-list">
            <li>One-time missions can only be completed once</li>
            <li>Daily missions reset at midnight and can be done again</li>
            <li>Repeatable missions have cooldown periods after completion</li>
            <li>Your progress is tracked automatically as you play</li>
            <li>Some missions require specific locations or levels</li>
          </ul>
        </div>
      </template>
    </div>
  </div>
</template>

<style scoped>
.missions-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 24px 0;
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 16px;
}

/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.title {
  font-size: 32px;
  font-weight: bold;
  color: white;
  margin: 0;
}

.back-button {
  padding: 10px 20px;
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.back-button:hover {
  background-color: rgba(255, 255, 255, 0.3);
}

/* Loading & Error States */
.loading {
  background: white;
  border-radius: 12px;
  padding: 60px;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  background: #fee;
  border: 2px solid #fcc;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  color: #c33;
}

.retry-button {
  margin-top: 12px;
  padding: 8px 16px;
  background: #c33;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

.retry-button:hover {
  background: #a22;
}

/* Stats Card */
.stats-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.stats-title {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 16px;
  color: #1a202c;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.stat-item {
  text-align: center;
}

.stat-label {
  font-size: 14px;
  color: #718096;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 32px;
  font-weight: bold;
  margin: 0;
}

.stat-value-purple { color: #9f7aea; }
.stat-value-green { color: #48bb78; }
.stat-value-yellow { color: #ecc94b; }
.stat-value-cyan { color: #0891b2; }

/* Filter Tabs */
.filter-tabs {
  background: white;
  border-radius: 12px;
  margin-bottom: 24px;
  display: flex;
  overflow-x: auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.tab-button {
  flex: 1;
  min-width: 120px;
  padding: 16px 24px;
  border: none;
  background: transparent;
  color: #718096;
  font-weight: 600;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  transition: all 0.2s;
}

.tab-button:hover {
  color: #0891b2;
  background: #f7fafc;
}

.tab-button.active {
  color: #0891b2;
  border-bottom-color: #0891b2;
  background: #f0fdfa;
}

/* Empty State */
.empty-state {
  background: white;
  border-radius: 12px;
  padding: 60px;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.empty-title {
  font-size: 18px;
  color: #4a5568;
  margin-bottom: 8px;
}

.empty-subtitle {
  font-size: 14px;
  color: #a0aec0;
}

/* Missions Grid */
.missions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

@media (max-width: 768px) {
  .missions-grid {
    grid-template-columns: 1fr;
  }
}

.mission-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  transition: box-shadow 0.3s;
}

.mission-card:hover {
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

/* Mission Header */
.mission-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.mission-title-section {
  flex: 1;
}

.mission-title {
  font-size: 20px;
  font-weight: bold;
  color: #1a202c;
  margin: 0 0 8px 0;
}

.mission-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

.badge {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.badge-one_time {
  background: #dbeafe;
  color: #1e40af;
}

.badge-daily {
  background: #d1fae5;
  color: #065f46;
}

.badge-repeatable {
  background: #e9d5ff;
  color: #6b21a8;
}

.badge-story {
  background: #fef3c7;
  color: #92400e;
}

.badge-gray {
  background: #e2e8f0;
  color: #4a5568;
}

.mission-location {
  font-size: 12px;
  color: #718096;
}

.mission-status {
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
}

.mission-status-available { color: #0891b2; }
.mission-status-in_progress { color: #eab308; }
.mission-status-completed { color: #16a34a; }

/* Description */
.mission-description {
  color: #4a5568;
  margin-bottom: 16px;
  line-height: 1.5;
}

/* Progress Section */
.progress-section {
  margin-bottom: 16px;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  margin-bottom: 6px;
}

.progress-label {
  color: #718096;
}

.progress-value {
  font-weight: 600;
  color: #1a202c;
}

.progress-bar {
  width: 100%;
  height: 12px;
  background: #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #06b6d4, #0891b2);
  border-radius: 6px;
  transition: width 0.5s ease;
}

/* Objective Box */
.objective-box {
  background: #f7fafc;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.objective-label {
  font-size: 13px;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 4px;
}

.objective-text {
  font-size: 14px;
  color: #2d3748;
  margin: 0;
}

/* Rewards Section */
.rewards-section {
  margin-bottom: 16px;
}

.rewards-label {
  font-size: 13px;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 8px;
}

.rewards-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}

.reward-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: #2d3748;
}

.reward-icon {
  font-size: 16px;
}

/* Cooldown Info */
.cooldown-info {
  font-size: 12px;
  color: #718096;
  margin-bottom: 12px;
}

/* Action Buttons */
.action-button {
  width: 100%;
  padding: 12px 16px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.action-button-primary {
  background: linear-gradient(135deg, #06b6d4, #0891b2);
  color: white;
}

.action-button-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(8, 145, 178, 0.3);
}

.action-button-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.action-button-progress {
  background: #fef3c7;
  color: #92400e;
}

.action-button-completed {
  background: #d1fae5;
  color: #065f46;
}

.action-button-disabled {
  background: #e2e8f0;
  color: #718096;
  cursor: not-allowed;
}

/* Info Box */
.info-box {
  background: #eff6ff;
  border-left: 4px solid #3b82f6;
  border-radius: 8px;
  padding: 20px;
}

.info-title {
  color: #1e40af;
  font-weight: 600;
  margin: 0 0 12px 0;
}

.info-list {
  list-style: none;
  padding: 0;
  margin: 0;
  color: #1e3a8a;
}

.info-list li {
  margin-bottom: 6px;
  font-size: 14px;
  line-height: 1.5;
}

/* Responsive */
@media (max-width: 768px) {
  .title {
    font-size: 24px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .missions-grid {
    grid-template-columns: 1fr;
  }
  
  .header {
    flex-direction: column;
    gap: 16px;
  }
  
  .back-button {
    width: 100%;
  }
}
</style>

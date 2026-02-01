<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const router = useRouter();

const achievements = ref({});
const stats = ref({
  earned: 0,
  total: 0,
  percentage: 0
});
const loading = ref(true);
const error = ref(null);

const achievementTypes = {
  'crime_count': { name: 'Crime Master', icon: '🎭', color: 'red' },
  'rank_reached': { name: 'Rank Progress', icon: '⭐', color: 'yellow' },
  'cash_earned': { name: 'Wealth', icon: '💰', color: 'green' },
  'kills': { name: 'Combat', icon: '⚔️', color: 'purple' },
  'properties_owned': { name: 'Real Estate', icon: '🏢', color: 'blue' },
  'gang_joined': { name: 'Gang Life', icon: '🤝', color: 'orange' }
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num);
};

const fetchAchievements = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get('/achievements');
    achievements.value = response.data.achievements || {};
    stats.value = response.data.stats || stats.value;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load achievements';
    console.error('Error fetching achievements:', err);
  } finally {
    loading.value = false;
  }
};

const goToDashboard = () => {
  router.push('/dashboard');
};

onMounted(() => {
  fetchAchievements();
});
</script>

<template>
  <div class="achievements-view">
    <div class="container">
      <!-- Header -->
      <div class="header">
        <h1 class="title">🏆 Achievements</h1>
        <button @click="goToDashboard" class="back-button">
          ← Dashboard
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Loading achievements...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-message">
        <p>{{ error }}</p>
        <button @click="fetchAchievements" class="retry-button">Retry</button>
      </div>

      <!-- Content -->
      <template v-else>
        <!-- Stats Banner -->
        <div class="stats-banner">
          <div class="stats-content">
            <div>
              <h3 class="stats-title">🏆 Your Progress</h3>
              <p class="stats-subtitle">Keep unlocking achievements to earn rewards!</p>
            </div>
            <div class="stats-badge">
              <div class="stats-number">{{ stats.earned }}/{{ stats.total }}</div>
              <div class="stats-label">Unlocked ({{ stats.percentage }}%)</div>
            </div>
          </div>
        </div>

        <!-- Achievement Categories -->
        <div v-for="(categoryAchievements, type) in achievements" :key="type" class="category-section">
          <div class="category-card">
            <div class="category-header">
              <h3 class="category-title">
                <span class="category-icon">{{ achievementTypes[type]?.icon || '🎯' }}</span>
                {{ achievementTypes[type]?.name || type }}
              </h3>
            </div>
            
            <div class="achievements-grid">
              <div 
                v-for="achievement in categoryAchievements" 
                :key="achievement.id"
                :class="['achievement-card', `achievement-${achievementTypes[type]?.color || 'gray'}`, { earned: achievement.is_earned }]">
                
                <div class="achievement-content">
                  <div class="achievement-header">
                    <span class="achievement-icon">{{ achievement.icon }}</span>
                    <div class="achievement-info">
                      <h4 class="achievement-name">{{ achievement.name }}</h4>
                      <p class="achievement-description">{{ achievement.description }}</p>
                    </div>
                  </div>
                  
                  <!-- Progress Bar -->
                  <div v-if="!achievement.is_earned" class="progress-container">
                    <div class="progress-header">
                      <span class="progress-label">Progress</span>
                      <span class="progress-value">
                        {{ formatNumber(achievement.progress) }} / {{ formatNumber(achievement.requirement) }}
                      </span>
                    </div>
                    <div class="progress-bar">
                      <div 
                        class="progress-fill"
                        :style="{ width: achievement.percentage + '%' }">
                      </div>
                    </div>
                    <div class="progress-percentage">{{ achievement.percentage }}%</div>
                  </div>
                  
                  <!-- Earned Badge -->
                  <div v-else class="earned-badge">
                    ✓ UNLOCKED
                  </div>
                  
                  <!-- Rewards -->
                  <div class="rewards">
                    <div v-if="achievement.reward_cash > 0" class="reward">
                      <span class="reward-icon">💰</span>
                      <span>${{ formatNumber(achievement.reward_cash) }}</span>
                    </div>
                    <div v-if="achievement.reward_xp > 0" class="reward">
                      <span class="reward-icon">⭐</span>
                      <span>{{ formatNumber(achievement.reward_xp) }} XP</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Info Box -->
        <div class="info-box">
          <h3 class="info-title">✨ Achievement Tips</h3>
          <ul class="info-list">
            <li>Achievements are earned automatically as you play</li>
            <li>Each achievement provides rewards when unlocked</li>
            <li>Track your progress for each achievement</li>
            <li>Some achievements unlock special features or items</li>
            <li>Check back regularly to see your progress</li>
          </ul>
        </div>
      </template>
    </div>
  </div>
</template>

<style scoped>
.achievements-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
  border-top-color: #f59e0b;
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

/* Stats Banner */
.stats-banner {
  background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  color: white;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.stats-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
}

.stats-title {
  font-size: 28px;
  font-weight: bold;
  margin: 0 0 8px 0;
}

.stats-subtitle {
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
}

.stats-badge {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  min-width: 150px;
}

.stats-number {
  font-size: 40px;
  font-weight: bold;
  margin-bottom: 8px;
}

.stats-label {
  font-size: 13px;
  opacity: 0.9;
}

/* Category Section */
.category-section {
  margin-bottom: 32px;
}

.category-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.category-header {
  background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
  padding: 16px 24px;
}

.category-title {
  color: white;
  font-size: 20px;
  font-weight: bold;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.category-icon {
  font-size: 28px;
}

/* Achievements Grid */
.achievements-grid {
  padding: 24px;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 16px;
}

@media (max-width: 768px) {
  .achievements-grid {
    grid-template-columns: 1fr;
  }
}

.achievement-card {
  border-left: 4px solid;
  border-radius: 8px;
  padding: 16px;
  transition: all 0.2s;
}

.achievement-card:hover {
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Color variants */
.achievement-red { border-color: #ef4444; background: #fef2f2; }
.achievement-red.earned { background: #fee2e2; }

.achievement-yellow { border-color: #f59e0b; background: #fffbeb; }
.achievement-yellow.earned { background: #fef3c7; }

.achievement-green { border-color: #10b981; background: #f0fdf4; }
.achievement-green.earned { background: #d1fae5; }

.achievement-purple { border-color: #8b5cf6; background: #faf5ff; }
.achievement-purple.earned { background: #ede9fe; }

.achievement-blue { border-color: #3b82f6; background: #eff6ff; }
.achievement-blue.earned { background: #dbeafe; }

.achievement-orange { border-color: #f97316; background: #fff7ed; }
.achievement-orange.earned { background: #ffedd5; }

.achievement-gray { border-color: #6b7280; background: #f9fafb; }
.achievement-gray.earned { background: #f3f4f6; }

.achievement-card:not(.earned) {
  opacity: 0.7;
}

/* Achievement Content */
.achievement-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.achievement-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.achievement-icon {
  font-size: 32px;
  flex-shrink: 0;
}

.achievement-info {
  flex: 1;
}

.achievement-name {
  font-size: 16px;
  font-weight: bold;
  color: #1a202c;
  margin: 0 0 4px 0;
}

.achievement-description {
  font-size: 14px;
  color: #4a5568;
  margin: 0;
  line-height: 1.4;
}

/* Progress Container */
.progress-container {
  margin-top: 8px;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  margin-bottom: 6px;
}

.progress-label {
  color: #718096;
  font-weight: 600;
}

.progress-value {
  color: #2d3748;
  font-weight: 600;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 4px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  border-radius: 4px;
  transition: width 0.5s ease;
}

.progress-percentage {
  font-size: 11px;
  font-weight: bold;
  text-align: center;
  color: #4a5568;
}

/* Earned Badge */
.earned-badge {
  display: inline-flex;
  align-items: center;
  background: #10b981;
  color: white;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: bold;
  letter-spacing: 0.5px;
}

/* Rewards */
.rewards {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.reward {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 13px;
  font-weight: 600;
  color: #2d3748;
}

.reward-icon {
  font-size: 16px;
}

/* Info Box */
.info-box {
  background: #fef3c7;
  border-left: 4px solid #f59e0b;
  border-radius: 8px;
  padding: 20px;
  margin-top: 24px;
}

.info-title {
  color: #92400e;
  font-weight: 600;
  margin: 0 0 12px 0;
}

.info-list {
  list-style: none;
  padding: 0;
  margin: 0;
  color: #78350f;
}

.info-list li {
  margin-bottom: 6px;
  font-size: 14px;
  line-height: 1.5;
  padding-left: 16px;
  position: relative;
}

.info-list li::before {
  content: '•';
  position: absolute;
  left: 0;
  font-weight: bold;
}

/* Responsive */
@media (max-width: 768px) {
  .title {
    font-size: 24px;
  }
  
  .stats-content {
    flex-direction: column;
  }
  
  .stats-badge {
    width: 100%;
  }
  
  .achievements-grid {
    grid-template-columns: 1fr;
    padding: 16px;
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

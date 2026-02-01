<template>
  <div class="leaderboards-container">
    <div class="page-header">
      <h1>🏆 Leaderboards</h1>
      <router-link to="/dashboard" class="back-link">← Back to Dashboard</router-link>
    </div>

    <div class="content-wrapper">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading leaderboards...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button @click="fetchLeaderboards" class="retry-btn">Retry</button>
      </div>

      <!-- Leaderboards Content -->
      <div v-else>
        <!-- Tabs -->
        <div class="tabs-container">
          <div class="tabs">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="activeTab = tab.key"
              :class="['tab-button', { active: activeTab === tab.key }]"
            >
              <span class="tab-icon">{{ tab.icon }}</span>
              <span class="tab-label">{{ tab.label }}</span>
            </button>
          </div>
        </div>

        <!-- Leaderboard Table -->
        <div class="leaderboard-card">
          <div class="table-wrapper">
            <table class="leaderboard-table">
              <thead>
                <tr>
                  <th>Rank</th>
                  <th>Player</th>
                  <th>Title</th>
                  <th class="text-right">{{ currentTabLabel }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="player in currentLeaderboard"
                  :key="player.id"
                  class="player-row"
                >
                  <td>
                    <span :class="['rank-badge', getRankClass(player.rank)]">
                      {{ getRankBadge(player.rank) }}
                    </span>
                  </td>
                  <td>
                    <span class="player-name">{{ player.username }}</span>
                  </td>
                  <td>
                    <span class="rank-title">{{ player.rank_title || player.rank || 'Thug' }}</span>
                  </td>
                  <td class="text-right">
                    <span class="stat-value">{{ formatStatValue(player) }}</span>
                  </td>
                </tr>
                <tr v-if="!currentLeaderboard || currentLeaderboard.length === 0">
                  <td colspan="5" class="empty-state">
                    No players found
                  </td>
                </tr>
              </tbody>
            </table>
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
const activeTab = ref('xp');
const leaderboards = ref({
  xp: [],
  respect: [],
  cash: [],
  networth: []
});

const tabs = [
  { key: 'xp', label: 'Experience', icon: '🏆' },
  { key: 'respect', label: 'Respect', icon: '⭐' },
  { key: 'cash', label: 'Cash', icon: '💰' },
  { key: 'networth', label: 'Networth', icon: '💎' },
];

const currentLeaderboard = computed(() => leaderboards.value[activeTab.value] || []);
const currentTabLabel = computed(() => tabs.find(t => t.key === activeTab.value)?.label || '');

const fetchLeaderboards = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await api.get('/modules/leaderboards');
    leaderboards.value = response.data.leaderboards || response.data;
  } catch (err) {
    console.error('Error fetching leaderboards:', err);
    error.value = err.response?.data?.message || 'Failed to load leaderboards. Please try again.';
  } finally {
    loading.value = false;
  }
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num);
};

const formatMoney = (num) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num);
};

const getRankBadge = (rank) => {
  if (rank === 1) return '🥇';
  if (rank === 2) return '🥈';
  if (rank === 3) return '🥉';
  return `#${rank}`;
};

const getRankClass = (rank) => {
  if (rank === 1) return 'gold';
  if (rank === 2) return 'silver';
  if (rank === 3) return 'bronze';
  return '';
};

const formatStatValue = (player) => {
  if (activeTab.value === 'xp') {
    return formatNumber(player.experience || 0) + ' XP';
  } else if (activeTab.value === 'respect') {
    return formatNumber(player.respect);
  } else if (activeTab.value === 'cash') {
    return formatMoney(player.cash);
  } else if (activeTab.value === 'networth') {
    return formatMoney(player.networth);
  }
  return '';
};

onMounted(() => {
  fetchLeaderboards();
});
</script>

<style scoped>
.leaderboards-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 50%, #f9a8d4 100%);
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
  color: #831843;
  margin: 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.back-link {
  color: #be185d;
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
  background: #fdf2f8;
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
  border: 4px solid #fce7f3;
  border-top: 4px solid #ec4899;
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
  background: #ec4899;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  background: #db2777;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.tabs-container {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
  overflow: hidden;
}

.tabs {
  display: flex;
  border-bottom: 2px solid #fce7f3;
}

.tab-button {
  flex: 1;
  padding: 1.25rem 1rem;
  background: white;
  border: none;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #64748b;
}

.tab-button:hover {
  background: #fdf2f8;
}

.tab-button.active {
  background: linear-gradient(135deg, #ec4899, #db2777);
  color: white;
  border-bottom: 3px solid #be185d;
}

.tab-icon {
  font-size: 1.5rem;
}

.tab-label {
  font-size: 1rem;
}

.leaderboard-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.table-wrapper {
  overflow-x: auto;
}

.leaderboard-table {
  width: 100%;
  border-collapse: collapse;
}

.leaderboard-table thead {
  background: linear-gradient(135deg, #fce7f3, #fbcfe8);
}

.leaderboard-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 700;
  color: #831843;
  text-transform: uppercase;
  font-size: 0.875rem;
  letter-spacing: 0.05em;
}

.leaderboard-table th.text-right {
  text-align: right;
}

.leaderboard-table tbody tr {
  border-bottom: 1px solid #fce7f3;
  transition: background 0.2s ease;
}

.leaderboard-table tbody tr:hover {
  background: #fdf2f8;
}

.leaderboard-table td {
  padding: 1.25rem 1rem;
}

.leaderboard-table td.text-right {
  text-align: right;
}

.rank-badge {
  font-size: 1.25rem;
  font-weight: 700;
}

.rank-badge.gold {
  color: #fbbf24;
}

.rank-badge.silver {
  color: #9ca3af;
}

.rank-badge.bronze {
  color: #d97706;
}

.player-name {
  color: #ec4899;
  font-weight: 600;
  font-size: 1rem;
}

.rank-title {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: linear-gradient(135deg, #f3e8ff, #e9d5ff);
  color: #7c3aed;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 9999px;
}

.stat-value {
  font-weight: 700;
  color: #0f172a;
  font-size: 1rem;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #94a3b8;
  font-size: 1.1rem;
}

@media (max-width: 768px) {
  .page-header h1 {
    font-size: 2rem;
  }

  .tab-button {
    flex-direction: column;
    padding: 1rem 0.5rem;
    font-size: 0.875rem;
  }

  .tab-icon {
    font-size: 1.25rem;
  }

  .leaderboard-table th,
  .leaderboard-table td {
    padding: 0.75rem 0.5rem;
    font-size: 0.875rem;
  }

  .rank-badge {
    font-size: 1rem;
  }
}
</style>

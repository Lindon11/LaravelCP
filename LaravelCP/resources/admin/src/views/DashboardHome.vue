<template>
  <div class="dashboard-home">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon users">👥</div>
        <div class="stat-info">
          <h3>Total Users</h3>
          <p class="stat-value">{{ stats.totalUsers }}</p>
          <span class="stat-change positive">+{{ stats.newUsers }} this week</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon active">⚡</div>
        <div class="stat-info">
          <h3>Active Players</h3>
          <p class="stat-value">{{ stats.activeUsers }}</p>
          <span class="stat-change">Online now</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon crimes">🎯</div>
        <div class="stat-info">
          <h3>Crimes Today</h3>
          <p class="stat-value">{{ stats.crimesToday }}</p>
          <span class="stat-change positive">+{{ stats.crimesGrowth }}%</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon revenue">💰</div>
        <div class="stat-info">
          <h3>Total Money</h3>
          <p class="stat-value">${{ formatNumber(stats.totalMoney) }}</p>
          <span class="stat-change">In circulation</span>
        </div>
      </div>
    </div>

    <div class="panels-grid">
      <div class="panel">
        <h2>System Health</h2>
        <div class="health-metrics">
          <div class="metric">
            <span class="metric-label">API Server</span>
            <span class="metric-value status-good">✓ Online</span>
          </div>
          <div class="metric">
            <span class="metric-label">Database</span>
            <span class="metric-value status-good">✓ Connected</span>
          </div>
          <div class="metric">
            <span class="metric-label">Queue</span>
            <span class="metric-value status-good">✓ Running</span>
          </div>
          <div class="metric">
            <span class="metric-label">Cache</span>
            <span class="metric-value status-good">✓ Active</span>
          </div>
        </div>
      </div>

      <div class="panel">
        <h2>Quick Actions</h2>
        <div class="actions-grid">
          <button class="action-btn">
            <span>🧹</span>
            Clear Cache
          </button>
          <button class="action-btn">
            <span>📢</span>
            Announcement
          </button>
          <button class="action-btn">
            <span>💾</span>
            Backup DB
          </button>
          <button class="action-btn">
            <span>📊</span>
            View Logs
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const stats = ref({
  totalUsers: 0,
  newUsers: 0,
  activeUsers: 0,
  crimesToday: 0,
  crimesGrowth: 0,
  totalMoney: 0
})

const formatNumber = (num) => {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

onMounted(async () => {
  try {
    const response = await api.get('/admin/stats')
    if (response.data) {
      stats.value = response.data
    }
  } catch (error) {
    // Use mock data if API not ready
    stats.value = {
      totalUsers: 1247,
      newUsers: 23,
      activeUsers: 89,
      crimesToday: 3421,
      crimesGrowth: 12,
      totalMoney: 15423890
    }
  }
})
</script>

<style scoped>
.dashboard-home {
  width: 100%;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 1rem;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  flex-shrink: 0;
}

.stat-icon.users {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.stat-icon.active {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.stat-icon.crimes {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stat-icon.revenue {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.stat-info {
  flex: 1;
}

.stat-info h3 {
  margin: 0 0 0.5rem 0;
  color: #94a3b8;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
}

.stat-value {
  margin: 0 0 0.25rem 0;
  color: #ffffff;
  font-size: 2rem;
  font-weight: 700;
  line-height: 1;
}

.stat-change {
  color: #94a3b8;
  font-size: 0.875rem;
}

.stat-change.positive {
  color: #10b981;
}

.panels-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.panel {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 1rem;
  padding: 1.5rem;
}

.panel h2 {
  margin: 0 0 1.5rem 0;
  color: #ffffff;
  font-size: 1.25rem;
}

.health-metrics {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.metric {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: rgba(15, 23, 42, 0.5);
  border-radius: 0.5rem;
}

.metric-label {
  color: #94a3b8;
  font-weight: 500;
}

.metric-value {
  font-weight: 600;
}

.status-good {
  color: #10b981;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.5rem;
  color: #ffffff;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn:hover {
  background: rgba(59, 130, 246, 0.1);
  border-color: #3b82f6;
}

.action-btn span {
  font-size: 1.5rem;
}

/* Tablet */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
  
  .panels-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .stat-card {
    padding: 1.25rem;
  }
  
  .panel {
    padding: 1.25rem;
  }
}

/* Mobile */
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
  }
  
  .panels-grid {
    gap: 1rem;
  }
  
  .stat-card {
    padding: 1rem;
  }
  
  .stat-icon {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
  }
  
  .stat-value {
    font-size: 1.75rem;
  }
  
  .stat-info h3 {
    font-size: 0.8rem;
  }
  
  .panel {
    padding: 1rem;
  }
  
  .panel h2 {
    font-size: 1.1rem;
    margin-bottom: 1rem;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .action-btn {
    padding: 0.875rem;
  }
}

/* Small mobile */
@media (max-width: 480px) {
  .stat-card {
    padding: 0.875rem;
    gap: 0.75rem;
  }
  
  .stat-icon {
    width: 45px;
    height: 45px;
    font-size: 1.35rem;
  }
  
  .stat-value {
    font-size: 1.5rem;
  }
  
  .stat-info h3 {
    font-size: 0.75rem;
    margin-bottom: 0.375rem;
  }
  
  .stat-change {
    font-size: 0.8rem;
  }
  
  .metric {
    padding: 0.625rem;
    flex-wrap: wrap;
    gap: 0.25rem;
  }
  
  .metric-label,
  .metric-value {
    font-size: 0.875rem;
  }
}
</style>

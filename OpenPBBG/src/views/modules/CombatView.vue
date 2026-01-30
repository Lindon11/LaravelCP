<template>
  <div class="combat-page">
    <header class="combat-header">
      <h1>⚔️ Combat</h1>
      <router-link to="/dashboard" class="back-link">← Dashboard</router-link>
    </header>

    <div class="container">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading combat arena...</p>
      </div>

      <!-- Main Content -->
      <div v-else class="combat-content">
        <!-- Player Stats -->
        <div class="player-stats">
          <h2>Your Combat Stats</h2>
          <div class="stats-grid">
            <div class="stat-item">
              <p class="stat-label">Health</p>
              <div class="health-bar-container">
                <div class="health-bar">
                  <div 
                    class="health-fill"
                    :class="getHealthClass(player.health, player.max_health)"
                    :style="`width: ${getHealthPercentage(player.health, player.max_health)}%`"
                  ></div>
                </div>
                <span class="health-text">{{ player.health }}/{{ player.max_health }}</span>
              </div>
            </div>
            <div class="stat-item">
              <p class="stat-label">Weapon</p>
              <p class="stat-value">{{ getEquippedWeapon() }}</p>
            </div>
            <div class="stat-item">
              <p class="stat-label">Armor</p>
              <p class="stat-value">{{ getEquippedArmor() }}</p>
            </div>
            <div class="stat-item">
              <p class="stat-label">Level</p>
              <p class="stat-value level-badge">{{ player.level }}</p>
            </div>
          </div>
        </div>

        <div class="combat-grid">
          <!-- Available Targets -->
          <div class="targets-section">
            <h2>🎯 Available Targets</h2>

            <div v-if="player.health <= 0" class="alert alert-dead">
              <p class="alert-title">☠️ You are dead!</p>
              <p class="alert-text">Visit the hospital to recover.</p>
            </div>

            <div v-if="availableTargets.length === 0" class="empty-state">
              <p class="empty-title">No targets available</p>
              <p class="empty-subtitle">No players are in your location or online</p>
            </div>

            <div v-else class="targets-list">
              <div 
                v-for="target in availableTargets" 
                :key="target.id"
                class="target-card"
              >
                <div class="target-header">
                  <div class="target-info">
                    <div class="target-name-row">
                      <h3>{{ target.username }}</h3>
                      <span class="level-badge">Lvl {{ target.level }}</span>
                      <span class="rank-text">{{ target.rank }}</span>
                    </div>
                  </div>
                </div>

                <div class="target-health">
                  <span class="health-label">HP:</span>
                  <div class="health-bar">
                    <div 
                      class="health-fill"
                      :class="getHealthClass(target.health, target.max_health)"
                      :style="`width: ${getHealthPercentage(target.health, target.max_health)}%`"
                    ></div>
                  </div>
                  <span class="health-text">{{ target.health }}/{{ target.max_health }}</span>
                </div>

                <button 
                  @click="attack(target)"
                  :disabled="player.health <= 0 || processing"
                  class="btn btn-attack"
                >
                  ⚔️ Attack
                </button>
              </div>
            </div>
          </div>

          <!-- Combat History -->
          <div class="history-section">
            <h2>📜 Combat History</h2>

            <div v-if="combatHistory.length === 0" class="empty-state">
              <p class="empty-title">No combat history</p>
              <p class="empty-subtitle">Attack someone to see logs here</p>
            </div>

            <div v-else class="history-list">
              <div 
                v-for="log in combatHistory" 
                :key="log.id"
                class="history-card"
              >
                <div class="history-header">
                  <div class="history-info">
                    <div class="history-outcome">
                      <span :class="getOutcomeClass(log.outcome)">
                        {{ getOutcomeText(log.outcome) }}
                      </span>
                      <span class="history-divider">•</span>
                      <span class="history-time">
                        {{ formatDate(log.created_at) }}
                      </span>
                    </div>
                    
                    <p class="history-text" v-if="log.attacker_id === player.id">
                      You attacked <strong>{{ log.defender?.username }}</strong>
                    </p>
                    <p class="history-text" v-else>
                      <strong>{{ log.attacker?.username }}</strong> attacked you
                    </p>
                  </div>
                </div>

                <div class="history-stats">
                  <div class="history-stat">
                    <p class="stat-label">Damage</p>
                    <p class="stat-damage">{{ log.damage_dealt }}</p>
                  </div>
                  <div class="history-stat" v-if="log.cash_stolen > 0">
                    <p class="stat-label">Cash Stolen</p>
                    <p class="stat-cash">${{ formatNumber(log.cash_stolen) }}</p>
                  </div>
                  <div class="history-stat" v-if="log.respect_gained > 0">
                    <p class="stat-label">Respect</p>
                    <p class="stat-respect">+{{ log.respect_gained }}</p>
                  </div>
                  <div class="history-stat" v-if="log.weapon">
                    <p class="stat-label">Weapon</p>
                    <p class="stat-weapon">{{ log.weapon.name }}</p>
                  </div>
                </div>

                <div v-if="log.outcome === 'killed'" class="kill-banner">
                  <p>☠️ {{ log.defender?.username }} was killed!</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Combat Tips -->
        <div class="tips-section">
          <h3>💡 Combat Tips</h3>
          <ul>
            <li>• Equip better weapons and armor from the Shop to increase damage and defense</li>
            <li>• Higher level players have better stats but give more respect when defeated</li>
            <li>• If you miss your attack, the defender will counter-attack you</li>
            <li>• Killing a player steals 10% of their cash and 5% of their respect</li>
            <li>• Keep your health high by visiting the Hospital after battles</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';

const player = ref({});
const availableTargets = ref([]);
const combatHistory = ref([]);
const loading = ref(true);
const processing = ref(false);

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num);
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString();
};

const getHealthPercentage = (health, maxHealth) => {
  return Math.round((health / maxHealth) * 100);
};

const getHealthClass = (health, maxHealth) => {
  const percentage = (health / maxHealth) * 100;
  if (percentage > 60) return 'health-high';
  if (percentage > 30) return 'health-medium';
  return 'health-low';
};

const getEquippedWeapon = () => {
  const weapon = player.value.equipped_items?.find(i => i.slot === 'weapon');
  return weapon?.item?.name || 'Fists';
};

const getEquippedArmor = () => {
  const armor = player.value.equipped_items?.find(i => i.slot === 'armor');
  return armor?.item?.name || 'None';
};

const getOutcomeClass = (outcome) => {
  if (outcome === 'killed') return 'outcome-killed';
  if (outcome === 'success') return 'outcome-success';
  return 'outcome-miss';
};

const getOutcomeText = (outcome) => {
  if (outcome === 'killed') return 'KILLED';
  if (outcome === 'success') return 'HIT';
  return 'MISS';
};

const loadCombatData = async () => {
  try {
    loading.value = true;
    const response = await api.get('/combat');
    player.value = response.data.player || {};
    availableTargets.value = response.data.availableTargets || [];
    combatHistory.value = response.data.combatHistory || [];
  } catch (error) {
    console.error('Failed to load combat data:', error);
  } finally {
    loading.value = false;
  }
};

const attack = async (target) => {
  if (!confirm(`Attack ${target.username}?`)) return;
  if (processing.value) return;

  try {
    processing.value = true;
    await api.post('/combat/attack', { defender_id: target.id });
    await loadCombatData();
  } catch (error) {
    console.error('Attack failed:', error);
    alert(error.response?.data?.message || 'Attack failed');
  } finally {
    processing.value = false;
  }
};

onMounted(() => {
  loadCombatData();
});
</script>

<style scoped>
.combat-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  padding: 20px;
}

.combat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto 30px;
}

.combat-header h1 {
  font-size: 2rem;
  font-weight: bold;
  color: #991b1b;
  margin: 0;
}

.back-link {
  padding: 10px 20px;
  background: #dc2626;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  transition: background 0.3s;
}

.back-link:hover {
  background: #b91c1c;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
}

.loading-state {
  background: white;
  border-radius: 12px;
  padding: 60px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.spinner {
  width: 50px;
  height: 50px;
  margin: 0 auto 20px;
  border: 4px solid #e5e7eb;
  border-top: 4px solid #dc2626;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.player-stats {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 24px;
  margin-bottom: 24px;
}

.player-stats h2 {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.stat-value {
  font-size: 1.125rem;
  font-weight: bold;
  color: #1f2937;
  margin: 0;
}

.level-badge {
  display: inline-block;
  padding: 4px 12px;
  background: #c084fc;
  color: white;
  border-radius: 6px;
  font-size: 1.125rem;
  font-weight: bold;
}

.health-bar-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.health-bar {
  flex: 1;
  height: 12px;
  background: #e5e7eb;
  border-radius: 6px;
  overflow: hidden;
}

.health-fill {
  height: 100%;
  transition: width 0.3s, background 0.3s;
  border-radius: 6px;
}

.health-fill.health-high {
  background: #10b981;
}

.health-fill.health-medium {
  background: #f59e0b;
}

.health-fill.health-low {
  background: #ef4444;
}

.health-text {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2937;
  white-space: nowrap;
}

.combat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.targets-section,
.history-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 24px;
}

.targets-section h2,
.history-section h2 {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.alert {
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.alert-dead {
  background: #fee2e2;
  border-left: 4px solid #ef4444;
}

.alert-title {
  font-weight: bold;
  color: #991b1b;
  margin: 0 0 4px 0;
}

.alert-text {
  color: #b91c1c;
  margin: 0;
  font-size: 0.875rem;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  background: #f9fafb;
  border-radius: 8px;
}

.empty-title {
  font-size: 1.125rem;
  color: #6b7280;
  margin: 0 0 8px 0;
}

.empty-subtitle {
  font-size: 0.875rem;
  color: #9ca3af;
  margin: 0;
}

.targets-list,
.history-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.history-list {
  max-height: 600px;
  overflow-y: auto;
}

.target-card {
  background: white;
  border: 2px solid #fecaca;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s;
}

.target-card:hover {
  border-color: #dc2626;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
}

.target-header {
  margin-bottom: 16px;
}

.target-name-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.target-name-row h3 {
  font-size: 1.25rem;
  font-weight: bold;
  color: #1f2937;
  margin: 0;
}

.rank-text {
  font-size: 0.875rem;
  color: #6b7280;
}

.target-health {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.health-label {
  font-size: 0.75rem;
  color: #6b7280;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
  width: 100%;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-attack {
  background: #dc2626;
  color: white;
}

.btn-attack:hover:not(:disabled) {
  background: #b91c1c;
}

.history-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
}

.history-header {
  margin-bottom: 16px;
}

.history-outcome {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.outcome-killed {
  font-weight: bold;
  color: #dc2626;
}

.outcome-success {
  font-weight: bold;
  color: #10b981;
}

.outcome-miss {
  font-weight: bold;
  color: #f59e0b;
}

.history-divider {
  color: #d1d5db;
}

.history-time {
  font-size: 0.875rem;
  color: #6b7280;
}

.history-text {
  font-size: 0.875rem;
  color: #1f2937;
  margin: 0;
}

.history-text strong {
  font-weight: 600;
}

.history-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
  gap: 12px;
  margin-top: 12px;
}

.history-stat {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-damage {
  font-weight: bold;
  color: #dc2626;
  margin: 0;
}

.stat-cash {
  font-weight: bold;
  color: #10b981;
  margin: 0;
}

.stat-respect {
  font-weight: bold;
  color: #c084fc;
  margin: 0;
}

.stat-weapon {
  font-weight: bold;
  color: #1f2937;
  margin: 0;
}

.kill-banner {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 2px solid #fecaca;
}

.kill-banner p {
  font-weight: bold;
  color: #dc2626;
  margin: 0;
  font-size: 0.875rem;
}

.tips-section {
  background: #dbeafe;
  border-left: 4px solid #3b82f6;
  border-radius: 8px;
  padding: 20px;
}

.tips-section h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e40af;
  margin: 0 0 12px 0;
}

.tips-section ul {
  margin: 0;
  padding: 0;
  list-style: none;
  color: #1e40af;
}

.tips-section li {
  font-size: 0.875rem;
  margin-bottom: 6px;
}

@media (max-width: 1024px) {
  .combat-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .combat-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .combat-grid {
    grid-template-columns: 1fr;
  }
}
</style>

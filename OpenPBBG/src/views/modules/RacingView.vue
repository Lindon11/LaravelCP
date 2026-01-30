<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

interface Vehicle {
  id: number;
  item: {
    name: string;
    stats: {
      speed: number;
    };
  };
}

interface Player {
  id: number;
  username: string;
  cash: number;
  speed?: number;
}

interface RaceParticipant {
  id: number;
  player_id: number;
  player: Player;
  vehicle?: Vehicle;
}

interface Race {
  id: number;
  name: string;
  status: 'waiting' | 'racing' | 'finished';
  entry_fee: number;
  prize_pool: number;
  min_participants: number;
  max_participants: number;
  participants: RaceParticipant[];
  location: {
    name: string;
  };
}

interface RaceHistory {
  id: number;
  position: number;
  finish_time: number;
  winnings: number;
  race: {
    location: {
      name: string;
    };
  };
  created_at: string;
}

const router = useRouter();

const loading = ref(true);
const error = ref('');
const successMessage = ref('');

const player = ref<Player | null>(null);
const availableRaces = ref<Race[]>([]);
const raceHistory = ref<RaceHistory[]>([]);
const vehicles = ref<Vehicle[]>([]);

const showCreateRace = ref(false);
const creating = ref(false);

// Create Race Form
const createRaceForm = ref({
  name: 'Street Race',
  entry_fee: 1000,
  min_participants: 2,
  max_participants: 8,
});

// Join Race Form
const joinRaceForm = ref({
  vehicle_id: null as number | null,
  bet_amount: 0,
});

const formatNumber = (num: number): string => {
  return new Intl.NumberFormat('en-US').format(num);
};

const loadData = async () => {
  try {
    loading.value = true;
    error.value = '';
    
    const response = await api.get('/modules/racing');
    
    player.value = response.data.player;
    availableRaces.value = response.data.availableRaces || [];
    raceHistory.value = response.data.raceHistory || [];
    vehicles.value = response.data.vehicles || [];
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load racing data';
  } finally {
    loading.value = false;
  }
};

const createRace = async () => {
  try {
    creating.value = true;
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post('/modules/racing/create', createRaceForm.value);
    
    successMessage.value = response.data.message || 'Race created successfully!';
    
    // Reset form and reload data
    createRaceForm.value = {
      name: 'Street Race',
      entry_fee: 1000,
      min_participants: 2,
      max_participants: 8,
    };
    showCreateRace.value = false;
    
    await loadData();
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to create race';
  } finally {
    creating.value = false;
  }
};

const joinRace = async (raceId: number) => {
  try {
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post(`/modules/racing/join/${raceId}`, joinRaceForm.value);
    
    successMessage.value = response.data.message || 'Joined race successfully!';
    
    // Reset form and reload data
    joinRaceForm.value = {
      vehicle_id: null,
      bet_amount: 0,
    };
    
    await loadData();
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to join race';
  }
};

const leaveRace = async (raceId: number) => {
  if (!confirm('Leave race? You will receive a 10% penalty on your entry fee.')) {
    return;
  }
  
  try {
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post(`/modules/racing/leave/${raceId}`);
    
    successMessage.value = response.data.message || 'Left race successfully!';
    
    await loadData();
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to leave race';
  }
};

const startRace = async (raceId: number) => {
  if (!confirm('Start this race? All participants must be ready.')) {
    return;
  }
  
  try {
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post(`/modules/racing/start/${raceId}`);
    
    successMessage.value = response.data.message || 'Race started!';
    
    await loadData();
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to start race';
  }
};

const isInRace = (race: Race): boolean => {
  return race.participants.some(p => p.player_id === player.value?.id);
};

const getStatusColor = (status: string): string => {
  const colors: Record<string, string> = {
    'waiting': 'status-waiting',
    'racing': 'status-racing',
    'finished': 'status-finished'
  };
  return colors[status] || 'status-finished';
};

const getPositionMedal = (position: number): string | number => {
  const medals: Record<number, string> = {
    1: '🥇',
    2: '🥈',
    3: '🥉'
  };
  return medals[position] || position;
};

const goToDashboard = () => {
  router.push('/');
};

onMounted(() => {
  loadData();
});
</script>

<template>
  <div class="racing-view">
    <div class="header">
      <h1>🏁 Street Racing</h1>
      <div class="header-buttons">
        <button @click="showCreateRace = !showCreateRace" class="create-button">
          + Create Race
        </button>
        <button @click="goToDashboard" class="back-button">
          ← Dashboard
        </button>
      </div>
    </div>

    <div class="content">
      <!-- Loading State -->
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Loading racing data...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error && !player" class="error-message">
        <strong>Error:</strong> {{ error }}
      </div>

      <!-- Main Content -->
      <div v-else-if="player">
        <!-- Flash Messages -->
        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>
        <div v-if="error" class="error-message">
          <strong>Error:</strong> {{ error }}
        </div>

        <!-- Player Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <p class="stat-label">Cash</p>
            <p class="stat-value cash">${{ formatNumber(player.cash) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Speed Stat</p>
            <p class="stat-value speed">{{ player.speed || 10 }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Your Vehicles</p>
            <p class="stat-value">{{ vehicles.length }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Races Today</p>
            <p class="stat-value">{{ raceHistory.length }}</p>
          </div>
        </div>

        <!-- Create Race Form -->
        <div v-if="showCreateRace" class="create-race-form">
          <h2>Create New Race</h2>
          <form @submit.prevent="createRace">
            <div class="form-group">
              <label>Race Name</label>
              <input v-model="createRaceForm.name" type="text" required>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Entry Fee</label>
                <input v-model.number="createRaceForm.entry_fee" type="number" min="100" required>
              </div>
              <div class="form-group">
                <label>Min Players</label>
                <input v-model.number="createRaceForm.min_participants" type="number" min="2" max="8" required>
              </div>
              <div class="form-group">
                <label>Max Players</label>
                <input v-model.number="createRaceForm.max_participants" type="number" min="2" max="8" required>
              </div>
            </div>
            <div class="form-buttons">
              <button type="submit" :disabled="creating" class="submit-button">
                {{ creating ? 'Creating...' : 'Create Race' }}
              </button>
              <button type="button" @click="showCreateRace = false" class="cancel-button">
                Cancel
              </button>
            </div>
          </form>
        </div>

        <!-- Available Races -->
        <div class="section">
          <h2>Open Races</h2>
          <div v-if="availableRaces.length > 0" class="races-grid">
            <div v-for="race in availableRaces" :key="race.id" class="race-card">
              <div class="race-header">
                <div class="race-info">
                  <h3>{{ race.name }}</h3>
                  <p class="race-location">{{ race.location.name }}</p>
                </div>
                <span :class="['status-badge', getStatusColor(race.status)]">
                  {{ race.status }}
                </span>
              </div>

              <div class="race-body">
                <div class="prize-grid">
                  <div class="prize-box entry">
                    <p class="prize-label">Entry Fee</p>
                    <p class="prize-value">${{ formatNumber(race.entry_fee) }}</p>
                  </div>
                  <div class="prize-box pool">
                    <p class="prize-label">Prize Pool</p>
                    <p class="prize-value">${{ formatNumber(race.prize_pool) }}</p>
                  </div>
                </div>

                <div class="participants-section">
                  <p class="participants-count">
                    Racers: {{ race.participants.length }}/{{ race.max_participants }}
                  </p>
                  <div class="participants-list">
                    <span v-for="participant in race.participants" :key="participant.id" class="participant-tag">
                      {{ participant.player.username }}
                      <span v-if="participant.vehicle">🚗</span>
                    </span>
                  </div>
                </div>

                <div v-if="!isInRace(race)" class="join-form">
                  <div v-if="vehicles.length > 0" class="form-group">
                    <label>Select Vehicle (Optional)</label>
                    <select v-model="joinRaceForm.vehicle_id">
                      <option :value="null">No Vehicle</option>
                      <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                        {{ vehicle.item.name }} (+{{ vehicle.item.stats.speed }} speed)
                      </option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Additional Bet</label>
                    <input v-model.number="joinRaceForm.bet_amount" type="number" min="0" step="100">
                  </div>
                  <button
                    @click="joinRace(race.id)"
                    :disabled="player.cash < race.entry_fee"
                    class="join-button"
                  >
                    Join Race (${{ formatNumber(race.entry_fee) }})
                  </button>
                </div>

                <div v-else class="race-actions">
                  <button @click="leaveRace(race.id)" class="leave-button">
                    Leave (90% refund)
                  </button>
                  <button 
                    v-if="race.participants.length >= race.min_participants"
                    @click="startRace(race.id)"
                    class="start-button"
                  >
                    Start Race
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">
            <p>No races available</p>
            <button @click="showCreateRace = true" class="create-button">
              Create a Race
            </button>
          </div>
        </div>

        <!-- Race History -->
        <div v-if="raceHistory.length > 0" class="section">
          <h2>Recent Races</h2>
          <div class="history-table">
            <table>
              <thead>
                <tr>
                  <th>Race</th>
                  <th>Position</th>
                  <th>Time</th>
                  <th>Winnings</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="history in raceHistory" :key="history.id">
                  <td>
                    <div class="race-name">{{ history.race.location.name }}</div>
                    <div class="race-date">{{ new Date(history.created_at).toLocaleDateString() }}</div>
                  </td>
                  <td class="position">{{ getPositionMedal(history.position) }}</td>
                  <td>{{ (history.finish_time / 1000).toFixed(2) }}s</td>
                  <td>
                    <span :class="['winnings', history.winnings > 0 ? 'positive' : 'negative']">
                      {{ history.winnings > 0 ? '+' : '' }}${{ formatNumber(history.winnings) }}
                    </span>
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

<style scoped>
.racing-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #1a1a1a 0%, #2d2410 100%);
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
  color: #fbbf24;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
  margin: 0;
}

.header-buttons {
  display: flex;
  gap: 10px;
}

.create-button {
  padding: 10px 20px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.create-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
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
  border: 4px solid rgba(251, 191, 36, 0.2);
  border-top-color: #fbbf24;
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: linear-gradient(145deg, #2a2a2a, #1f1f1f);
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(251, 191, 36, 0.2);
}

.stat-label {
  color: #888;
  font-size: 0.9rem;
  margin-bottom: 8px;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: bold;
  color: #fbbf24;
}

.stat-value.cash {
  color: #10b981;
}

.stat-value.speed {
  color: #3b82f6;
}

.create-race-form {
  background: linear-gradient(145deg, #2a2a2a, #1f1f1f);
  border-radius: 16px;
  padding: 30px;
  margin-bottom: 30px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(251, 191, 36, 0.2);
}

.create-race-form h2 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #fbbf24;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #aaa;
  font-weight: 600;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 12px;
  background-color: #1a1a1a;
  border: 2px solid rgba(251, 191, 36, 0.3);
  border-radius: 8px;
  color: #fff;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #fbbf24;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
}

.form-buttons {
  display: flex;
  gap: 15px;
}

.submit-button {
  padding: 12px 30px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submit-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.submit-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.cancel-button {
  padding: 12px 30px;
  background-color: #444;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cancel-button:hover {
  background-color: #555;
}

.section {
  margin-bottom: 40px;
}

.section h2 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #fbbf24;
  font-size: 1.5rem;
}

.races-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 25px;
}

.race-card {
  background: linear-gradient(145deg, #2a2a2a, #1f1f1f);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
  border: 2px solid rgba(251, 191, 36, 0.3);
  transition: all 0.3s ease;
}

.race-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(251, 191, 36, 0.4);
}

.race-header {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.race-info h3 {
  margin: 0 0 5px 0;
  color: #1a1a1a;
  font-weight: bold;
  font-size: 1.3rem;
}

.race-location {
  margin: 0;
  color: #4a2808;
  font-size: 0.9rem;
}

.status-badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
}

.status-waiting {
  background-color: #fbbf24;
  color: #1a1a1a;
}

.status-racing {
  background-color: #3b82f6;
  color: #ffffff;
}

.status-finished {
  background-color: #6b7280;
  color: #ffffff;
}

.race-body {
  padding: 25px;
}

.prize-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-bottom: 20px;
}

.prize-box {
  padding: 15px;
  border-radius: 8px;
  text-align: center;
}

.prize-box.entry {
  background-color: rgba(16, 185, 129, 0.2);
}

.prize-box.pool {
  background-color: rgba(168, 85, 247, 0.2);
}

.prize-label {
  margin: 0 0 5px 0;
  color: #aaa;
  font-size: 0.8rem;
}

.prize-value {
  margin: 0;
  font-size: 1.3rem;
  font-weight: bold;
  color: #fbbf24;
}

.participants-section {
  margin-bottom: 20px;
}

.participants-count {
  color: #aaa;
  margin-bottom: 10px;
  font-size: 0.9rem;
}

.participants-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.participant-tag {
  padding: 6px 12px;
  background-color: rgba(59, 130, 246, 0.3);
  color: #93c5fd;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 6px;
}

.join-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.join-button {
  width: 100%;
  padding: 15px;
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: #1a1a1a;
  border: none;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.join-button:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(251, 191, 36, 0.5);
}

.join-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.race-actions {
  display: flex;
  gap: 10px;
}

.leave-button {
  flex: 1;
  padding: 12px;
  background-color: #dc2626;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.leave-button:hover {
  background-color: #b91c1c;
}

.start-button {
  flex: 1;
  padding: 12px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.start-button:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: linear-gradient(145deg, #2a2a2a, #1f1f1f);
  border-radius: 12px;
  border: 2px dashed rgba(251, 191, 36, 0.3);
}

.empty-state p {
  color: #aaa;
  font-size: 1.2rem;
  margin-bottom: 20px;
}

.history-table {
  background: linear-gradient(145deg, #2a2a2a, #1f1f1f);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
}

.history-table table {
  width: 100%;
  border-collapse: collapse;
}

.history-table thead {
  background-color: rgba(251, 191, 36, 0.2);
}

.history-table th {
  padding: 15px;
  text-align: left;
  color: #fbbf24;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.8rem;
}

.history-table td {
  padding: 15px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.race-name {
  font-weight: 600;
  color: #fff;
  margin-bottom: 4px;
}

.race-date {
  color: #888;
  font-size: 0.85rem;
}

.position {
  font-size: 1.5rem;
}

.winnings {
  font-weight: 700;
  font-size: 1.1rem;
}

.winnings.positive {
  color: #10b981;
}

.winnings.negative {
  color: #dc2626;
}

@media (max-width: 768px) {
  .header h1 {
    font-size: 1.5rem;
  }
  
  .header-buttons {
    width: 100%;
    justify-content: space-between;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .races-grid {
    grid-template-columns: 1fr;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .history-table {
    overflow-x: auto;
  }
}
</style>

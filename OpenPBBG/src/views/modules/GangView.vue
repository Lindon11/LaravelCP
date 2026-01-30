<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const router = useRouter();

const player = ref(null);
const myGang = ref(null);
const gangs = ref([]);
const creationCost = ref(1000000);
const loading = ref(true);
const processing = ref(false);
const error = ref('');
const successMessage = ref('');

const showCreateForm = ref(false);
const gangName = ref('');
const gangTag = ref('');
const depositAmount = ref(0);
const withdrawAmount = ref(0);

const formatMoney = (amount) => {
  return new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'USD', 
    minimumFractionDigits: 0 
  }).format(amount);
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num);
};

const isLeader = computed(() => {
  return myGang.value && myGang.value.leader_id === player.value?.id;
});

const canCreateGang = computed(() => {
  return player.value && player.value.cash >= creationCost.value;
});

const fetchData = async () => {
  try {
    loading.value = true;
    error.value = '';
    const response = await api.get('/gangs');
    player.value = response.data.player;
    myGang.value = response.data.myGang || null;
    gangs.value = response.data.gangs || [];
    creationCost.value = response.data.creationCost || 1000000;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load gang data';
  } finally {
    loading.value = false;
  }
};

const createGang = async () => {
  if (processing.value || !gangName.value || !gangTag.value) return;
  
  try {
    processing.value = true;
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post('/gangs/create', { 
      name: gangName.value, 
      tag: gangTag.value 
    });
    
    successMessage.value = response.data.message || 'Gang created successfully!';
    showCreateForm.value = false;
    gangName.value = '';
    gangTag.value = '';
    await fetchData();
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create gang';
  } finally {
    processing.value = false;
  }
};

const leaveGang = async () => {
  if (processing.value || !confirm('Are you sure you want to leave your gang?')) return;
  
  try {
    processing.value = true;
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post('/gangs/leave');
    
    successMessage.value = response.data.message || 'Left gang successfully';
    await fetchData();
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to leave gang';
  } finally {
    processing.value = false;
  }
};

const kickMember = async (playerId) => {
  if (processing.value || !confirm('Kick this member from the gang?')) return;
  
  try {
    processing.value = true;
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post(`/gangs/kick/${playerId}`);
    
    successMessage.value = response.data.message || 'Member kicked successfully';
    await fetchData();
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to kick member';
  } finally {
    processing.value = false;
  }
};

const deposit = async () => {
  if (processing.value || depositAmount.value <= 0) return;
  
  try {
    processing.value = true;
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post('/gangs/deposit', { 
      amount: depositAmount.value 
    });
    
    successMessage.value = response.data.message || 'Deposit successful!';
    depositAmount.value = 0;
    await fetchData();
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to deposit';
  } finally {
    processing.value = false;
  }
};

const withdraw = async () => {
  if (processing.value || withdrawAmount.value <= 0) return;
  
  try {
    processing.value = true;
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post('/gangs/withdraw', { 
      amount: withdrawAmount.value 
    });
    
    successMessage.value = response.data.message || 'Withdrawal successful!';
    withdrawAmount.value = 0;
    await fetchData();
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to withdraw';
  } finally {
    processing.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<template>
  <div class="gang-view">
    <div class="header">
      <div class="header-content">
        <h1>👥 Gangs</h1>
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

        <!-- My Gang -->
        <div v-if="myGang" class="my-gang-section">
          <div class="gang-header">
            <div class="gang-info">
              <h2>{{ myGang.name }} <span class="gang-tag">[{{ myGang.tag }}]</span></h2>
              <p class="gang-description">{{ myGang.description || 'No description' }}</p>
            </div>
            <button 
              @click="leaveGang" 
              :disabled="processing"
              class="leave-btn"
            >
              Leave Gang
            </button>
          </div>

          <div class="gang-stats">
            <div class="stat-card">
              <p class="stat-label">Members</p>
              <p class="stat-value">{{ formatNumber(myGang.member_count || 0) }}/{{ formatNumber(myGang.max_members) }}</p>
            </div>
            <div class="stat-card">
              <p class="stat-label">Respect</p>
              <p class="stat-value">{{ formatNumber(myGang.respect) }}</p>
            </div>
            <div class="stat-card">
              <p class="stat-label">Gang Bank</p>
              <p class="stat-value">{{ formatMoney(myGang.bank) }}</p>
            </div>
          </div>

          <!-- Gang Bank Actions -->
          <div class="bank-actions">
            <div class="action-card">
              <label class="action-label">Deposit to Gang</label>
              <div class="action-form">
                <input 
                  v-model.number="depositAmount" 
                  type="number" 
                  min="1" 
                  placeholder="Amount"
                  class="amount-input"
                >
                <button 
                  @click="deposit" 
                  :disabled="processing || depositAmount <= 0"
                  class="action-btn deposit-btn"
                >
                  Deposit
                </button>
              </div>
            </div>

            <div v-if="isLeader" class="action-card leader-only">
              <label class="action-label">Withdraw (Leader Only)</label>
              <div class="action-form">
                <input 
                  v-model.number="withdrawAmount" 
                  type="number" 
                  min="1" 
                  placeholder="Amount"
                  class="amount-input"
                >
                <button 
                  @click="withdraw" 
                  :disabled="processing || withdrawAmount <= 0"
                  class="action-btn withdraw-btn"
                >
                  Withdraw
                </button>
              </div>
            </div>
          </div>

          <!-- Gang Members (if available) -->
          <div v-if="myGang.members && myGang.members.length > 0" class="members-section">
            <h3>Gang Members</h3>
            <div class="members-list">
              <div 
                v-for="member in myGang.members" 
                :key="member.id" 
                class="member-card"
              >
                <div class="member-info">
                  <h4>{{ member.name }}</h4>
                  <p class="member-role">{{ member.role || 'Member' }}</p>
                </div>
                <button 
                  v-if="isLeader && member.id !== player.id"
                  @click="kickMember(member.id)"
                  :disabled="processing"
                  class="kick-btn"
                >
                  Kick
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Create Gang -->
        <div v-else class="create-section">
          <button 
            v-if="!showCreateForm" 
            @click="showCreateForm = true"
            :disabled="!canCreateGang"
            class="create-gang-btn"
          >
            Create Gang ({{ formatMoney(creationCost) }})
          </button>
          <p v-if="!canCreateGang" class="insufficient-funds">
            You need {{ formatMoney(creationCost) }} to create a gang
          </p>

          <div v-if="showCreateForm" class="create-form">
            <h3>Create Your Gang</h3>
            <div class="form-fields">
              <div class="form-field">
                <label>Gang Name</label>
                <input 
                  v-model="gangName" 
                  type="text" 
                  maxlength="50" 
                  placeholder="The Syndicate"
                >
              </div>
              <div class="form-field">
                <label>Gang Tag (Max 5 chars)</label>
                <input 
                  v-model="gangTag" 
                  type="text" 
                  maxlength="5" 
                  placeholder="SYN"
                  class="uppercase"
                >
              </div>
              <div class="form-buttons">
                <button 
                  @click="createGang" 
                  :disabled="processing || !gangName || !gangTag"
                  class="submit-btn"
                >
                  {{ processing ? 'Creating...' : 'Create' }}
                </button>
                <button 
                  @click="showCreateForm = false"
                  class="cancel-btn"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- All Gangs -->
        <div class="all-gangs-section">
          <h3>All Gangs ({{ gangs.length }})</h3>
          
          <div v-if="gangs.length === 0" class="empty-state">
            <p>No gangs have been created yet</p>
          </div>

          <div v-else class="gangs-list">
            <div 
              v-for="gang in gangs" 
              :key="gang.id" 
              class="gang-card"
              :class="{ 'my-gang-highlight': myGang && gang.id === myGang.id }"
            >
              <div class="gang-card-header">
                <div class="gang-card-info">
                  <h4>{{ gang.name }} <span class="gang-tag">[{{ gang.tag }}]</span></h4>
                  <p class="gang-details">
                    Leader: {{ gang.leader }} | 
                    Members: {{ formatNumber(gang.members) }}/{{ formatNumber(gang.max_members) }} | 
                    Respect: {{ formatNumber(gang.respect) }}
                  </p>
                </div>
                <div class="gang-card-bank">
                  <p class="bank-amount">{{ formatMoney(gang.bank) }}</p>
                  <p class="bank-label">Gang Bank</p>
                </div>
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
.gang-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
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

.my-gang-section {
  background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  margin-bottom: 2rem;
  color: white;
}

.gang-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  gap: 1rem;
}

.gang-info h2 {
  font-size: 2rem;
  font-weight: bold;
  margin: 0 0 0.5rem 0;
}

.gang-tag {
  color: #ddd6fe;
}

.gang-description {
  color: #ddd6fe;
  margin: 0;
  font-size: 0.875rem;
}

.leave-btn {
  padding: 0.75rem 1.5rem;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.leave-btn:hover:not(:disabled) {
  background: #b91c1c;
  transform: translateY(-2px);
}

.leave-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.gang-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  padding: 1.5rem;
  border-radius: 0.75rem;
  text-align: center;
}

.stat-label {
  font-size: 0.875rem;
  color: #ddd6fe;
  margin: 0 0 0.5rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 1.875rem;
  font-weight: bold;
  margin: 0;
}

.bank-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.action-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  padding: 1.5rem;
  border-radius: 0.75rem;
}

.action-card.leader-only {
  background: rgba(251, 191, 36, 0.2);
  border: 2px solid rgba(251, 191, 36, 0.3);
}

.action-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.action-form {
  display: flex;
  gap: 0.75rem;
}

.amount-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  outline: none;
}

.action-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.deposit-btn {
  background: #10b981;
  color: white;
}

.deposit-btn:hover:not(:disabled) {
  background: #059669;
}

.withdraw-btn {
  background: #fbbf24;
  color: #1f2937;
}

.withdraw-btn:hover:not(:disabled) {
  background: #f59e0b;
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.members-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.members-section h3 {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0 0 1rem 0;
}

.members-list {
  display: grid;
  gap: 0.75rem;
}

.member-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  padding: 1rem;
  border-radius: 0.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.member-info h4 {
  font-size: 1.125rem;
  font-weight: 600;
  margin: 0 0 0.25rem 0;
}

.member-role {
  font-size: 0.875rem;
  color: #ddd6fe;
  margin: 0;
}

.kick-btn {
  padding: 0.5rem 1rem;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.kick-btn:hover:not(:disabled) {
  background: #b91c1c;
}

.kick-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.create-section {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  margin-bottom: 2rem;
}

.create-gang-btn {
  width: 100%;
  padding: 1.5rem;
  background: #7c3aed;
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: bold;
  font-size: 1.25rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.create-gang-btn:hover:not(:disabled) {
  background: #6d28d9;
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
}

.create-gang-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.insufficient-funds {
  text-align: center;
  color: #dc2626;
  font-weight: 600;
  margin-top: 0.5rem;
}

.create-form {
  padding-top: 1rem;
}

.create-form h3 {
  font-size: 1.75rem;
  font-weight: bold;
  margin: 0 0 1.5rem 0;
  color: #1f2937;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-field label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #374151;
}

.form-field input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 1rem;
  outline: none;
  transition: border-color 0.3s ease;
}

.form-field input:focus {
  border-color: #7c3aed;
}

.form-field input.uppercase {
  text-transform: uppercase;
}

.form-buttons {
  display: flex;
  gap: 1rem;
}

.submit-btn {
  flex: 1;
  padding: 1rem;
  background: #7c3aed;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: bold;
  font-size: 1.125rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submit-btn:hover:not(:disabled) {
  background: #6d28d9;
}

.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.cancel-btn {
  padding: 1rem 2rem;
  background: #e5e7eb;
  color: #374151;
  border: none;
  border-radius: 0.5rem;
  font-weight: bold;
  font-size: 1.125rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cancel-btn:hover {
  background: #d1d5db;
}

.all-gangs-section {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.all-gangs-section h3 {
  font-size: 1.75rem;
  font-weight: bold;
  margin: 0 0 1.5rem 0;
  color: #1f2937;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #6b7280;
  font-size: 1.125rem;
}

.gangs-list {
  display: grid;
  gap: 1rem;
}

.gang-card {
  border: 2px solid #e5e7eb;
  border-radius: 0.75rem;
  padding: 1.5rem;
  transition: all 0.3s ease;
}

.gang-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.gang-card.my-gang-highlight {
  border-color: #7c3aed;
  background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
}

.gang-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.gang-card-info {
  flex: 1;
}

.gang-card-info h4 {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0 0 0.5rem 0;
  color: #1f2937;
}

.gang-card-info .gang-tag {
  color: #7c3aed;
}

.gang-details {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.gang-card-bank {
  text-align: right;
}

.bank-amount {
  font-size: 1.5rem;
  font-weight: bold;
  color: #10b981;
  margin: 0 0 0.25rem 0;
}

.bank-label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
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
  color: #8b5cf6;
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

  .gang-header {
    flex-direction: column;
  }

  .gang-info h2 {
    font-size: 1.5rem;
  }

  .gang-stats {
    grid-template-columns: 1fr;
  }

  .bank-actions {
    grid-template-columns: 1fr;
  }

  .action-form {
    flex-direction: column;
  }

  .action-btn {
    width: 100%;
  }

  .gang-card-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .gang-card-bank {
    text-align: left;
  }

  .form-buttons {
    flex-direction: column;
  }
}
</style>

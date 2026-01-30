<template>
  <div class="bounty-page">
    <header class="bounty-header">
      <h1>💰 Bounties</h1>
      <router-link to="/dashboard" class="back-link">← Dashboard</router-link>
    </header>

    <div class="container">
      <!-- Flash Messages -->
      <div v-if="successMessage" class="flash flash-success">{{ successMessage }}</div>
      <div v-if="errorMessage" class="flash flash-error">{{ errorMessage }}</div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading bounties...</p>
      </div>

      <!-- Main Content -->
      <div v-else class="bounty-content">
        <!-- Warning Banner -->
        <div v-if="myBounties.on_me > 0" class="warning-banner">
          <div class="warning-content">
            <div class="warning-info">
              <h3>⚠️ Warning: Bounty on Your Head</h3>
              <p>There's a bounty placed on you. Watch your back!</p>
            </div>
            <div class="warning-amount">
              <p class="bounty-value">{{ formatMoney(myBounties.on_me) }}</p>
              <p class="bounty-label">Total Bounty</p>
            </div>
          </div>
        </div>

        <!-- Place Bounty Section -->
        <div class="place-bounty-section">
          <button 
            v-if="!showPlaceForm" 
            @click="showPlaceForm = true"
            class="btn btn-place-bounty"
          >
            Place a Bounty
          </button>
          
          <div v-else class="place-bounty-form">
            <h3>Place Bounty on Player</h3>
            <div class="form-group">
              <label>Target Player ID</label>
              <input 
                v-model="targetId" 
                type="number"
                placeholder="Enter player ID"
                class="form-input"
              />
            </div>
            
            <div class="form-group">
              <label>Bounty Amount (Min: {{ formatMoney(minAmount) }})</label>
              <input 
                v-model.number="amount" 
                type="number"
                :min="minAmount"
                class="form-input"
              />
              <p class="form-help">
                Fee: {{ formatMoney(calculatedFee) }} ({{ feePercentage }}%) | 
                Total Cost: {{ formatMoney(totalCost) }}
              </p>
            </div>
            
            <div class="form-group">
              <label>Reason (Optional)</label>
              <input 
                v-model="reason" 
                type="text"
                maxlength="255"
                placeholder="They disrespected me..."
                class="form-input"
              />
            </div>
            
            <div class="form-actions">
              <button 
                @click="placeBounty"
                :disabled="processing || !targetId || amount < minAmount"
                class="btn btn-submit"
              >
                Place Bounty
              </button>
              <button 
                @click="cancelForm"
                class="btn btn-cancel"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>

        <!-- Active Bounties -->
        <div class="active-bounties-section">
          <h3>Active Bounties ({{ bounties.length }})</h3>
          
          <div v-if="bounties.length === 0" class="empty-state">
            <p>No active bounties</p>
          </div>
          
          <div v-else class="bounties-list">
            <div 
              v-for="bounty in bounties" 
              :key="bounty.id"
              class="bounty-card"
            >
              <div class="bounty-content">
                <div class="bounty-info">
                  <h4>
                    {{ bounty.target }}
                    <span class="target-id">#{{ bounty.target_id }}</span>
                  </h4>
                  <p v-if="bounty.reason" class="bounty-reason">"{{ bounty.reason }}"</p>
                  <p class="bounty-time">Placed {{ bounty.placed_at }}</p>
                </div>
                <div class="bounty-reward">
                  <p class="reward-amount">{{ formatMoney(bounty.amount) }}</p>
                  <p class="reward-label">Reward</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- My Placed Bounties -->
        <div v-if="myBounties.placed && myBounties.placed.length > 0" class="my-bounties-section">
          <h3>My Placed Bounties</h3>
          <div class="my-bounties-list">
            <div 
              v-for="(bounty, index) in myBounties.placed" 
              :key="index"
              class="my-bounty-card"
            >
              <div class="my-bounty-content">
                <div class="my-bounty-info">
                  <p class="my-bounty-target">{{ bounty.target }}</p>
                  <p class="my-bounty-time">{{ bounty.placed_at }}</p>
                </div>
                <div class="my-bounty-details">
                  <p class="my-bounty-amount">{{ formatMoney(bounty.amount) }}</p>
                  <p 
                    class="my-bounty-status"
                    :class="getStatusClass(bounty.status)"
                  >
                    {{ bounty.status.toUpperCase() }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
          <h3>💡 How Bounties Work</h3>
          <ul>
            <li>• Place a bounty on any player to mark them for assassination</li>
            <li>• Other players can claim the bounty by killing your target</li>
            <li>• The {{ feePercentage }}% fee goes to the game for processing</li>
            <li>• Multiple bounties can be stacked on the same player</li>
            <li>• Bounties remain active until the target is killed or expires</li>
            <li>• You cannot place a bounty on yourself</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';

const player = ref(null);
const bounties = ref([]);
const myBounties = ref({ on_me: 0, placed: [] });
const minAmount = ref(10000);
const feePercentage = ref(10);

const showPlaceForm = ref(false);
const targetId = ref('');
const amount = ref(10000);
const reason = ref('');

const loading = ref(true);
const processing = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const formatMoney = (val) => {
  return new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'USD', 
    minimumFractionDigits: 0 
  }).format(val);
};

const calculatedFee = computed(() => {
  return (amount.value || 0) * (feePercentage.value / 100);
});

const totalCost = computed(() => {
  return (amount.value || 0) + calculatedFee.value;
});

const getStatusClass = (status) => {
  if (status === 'active') return 'status-active';
  if (status === 'claimed') return 'status-claimed';
  return 'status-expired';
};

const loadBounties = async () => {
  try {
    loading.value = true;
    const response = await api.get('/bounties');
    player.value = response.data.player;
    bounties.value = response.data.bounties || [];
    myBounties.value = response.data.myBounties || { on_me: 0, placed: [] };
    minAmount.value = response.data.minAmount || 10000;
    feePercentage.value = response.data.feePercentage || 10;
    amount.value = minAmount.value;
  } catch (error) {
    console.error('Failed to load bounties:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to load bounties';
  } finally {
    loading.value = false;
  }
};

const placeBounty = async () => {
  if (processing.value) return;
  
  try {
    processing.value = true;
    const response = await api.post('/bounties/place', {
      target_id: targetId.value,
      amount: amount.value,
      reason: reason.value
    });
    
    successMessage.value = response.data.message || 'Bounty placed successfully!';
    errorMessage.value = '';
    showPlaceForm.value = false;
    
    // Reset form
    targetId.value = '';
    amount.value = minAmount.value;
    reason.value = '';
    
    await loadBounties();
  } catch (error) {
    console.error('Failed to place bounty:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to place bounty';
    successMessage.value = '';
  } finally {
    processing.value = false;
    setTimeout(() => {
      successMessage.value = '';
      errorMessage.value = '';
    }, 5000);
  }
};

const cancelForm = () => {
  showPlaceForm.value = false;
  targetId.value = '';
  amount.value = minAmount.value;
  reason.value = '';
};

onMounted(() => {
  loadBounties();
});
</script>

<style scoped>
.bounty-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  padding: 20px;
}

.bounty-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto 30px;
}

.bounty-header h1 {
  font-size: 2rem;
  font-weight: bold;
  color: #92400e;
  margin: 0;
}

.back-link {
  padding: 10px 20px;
  background: #f59e0b;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  transition: background 0.3s;
}

.back-link:hover {
  background: #d97706;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}

.flash {
  padding: 16px;
  margin-bottom: 20px;
  border-radius: 8px;
  font-weight: 500;
}

.flash-success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #10b981;
}

.flash-error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #ef4444;
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
  border-top: 4px solid #f59e0b;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.warning-banner {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 32px;
  margin-bottom: 24px;
}

.warning-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.warning-info h3 {
  font-size: 1.75rem;
  font-weight: bold;
  margin: 0 0 8px 0;
}

.warning-info p {
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
}

.warning-amount {
  text-align: right;
}

.bounty-value {
  font-size: 2.5rem;
  font-weight: bold;
  margin: 0;
}

.bounty-label {
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
}

.place-bounty-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 24px;
  margin-bottom: 24px;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-place-bounty {
  width: 100%;
  padding: 16px;
  background: #dc2626;
  color: white;
  font-size: 1.125rem;
}

.btn-place-bounty:hover {
  background: #b91c1c;
}

.place-bounty-form h3 {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.form-input {
  width: 100%;
  padding: 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

.form-input:focus {
  outline: none;
  border-color: #f59e0b;
}

.form-help {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 8px 0 0 0;
}

.form-actions {
  display: flex;
  gap: 12px;
}

.btn-submit {
  flex: 1;
  background: #dc2626;
  color: white;
}

.btn-submit:hover:not(:disabled) {
  background: #b91c1c;
}

.btn-cancel {
  padding: 12px 24px;
  background: #e5e7eb;
  color: #1f2937;
}

.btn-cancel:hover {
  background: #d1d5db;
}

.active-bounties-section,
.my-bounties-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 24px;
  margin-bottom: 24px;
}

.active-bounties-section h3,
.my-bounties-section h3 {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
  background: #f9fafb;
  border-radius: 8px;
}

.bounties-list,
.my-bounties-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.bounty-card {
  background: white;
  border: 2px solid #fed7aa;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s;
}

.bounty-card:hover {
  border-color: #f59e0b;
  background: #fffbeb;
}

.bounty-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.bounty-info {
  flex: 1;
}

.bounty-info h4 {
  font-size: 1.25rem;
  font-weight: bold;
  color: #dc2626;
  margin: 0 0 8px 0;
}

.target-id {
  color: #6b7280;
  font-size: 0.875rem;
}

.bounty-reason {
  font-size: 0.875rem;
  color: #6b7280;
  font-style: italic;
  margin: 4px 0;
}

.bounty-time {
  font-size: 0.75rem;
  color: #9ca3af;
  margin: 4px 0 0 0;
}

.bounty-reward {
  text-align: right;
}

.reward-amount {
  font-size: 1.875rem;
  font-weight: bold;
  color: #10b981;
  margin: 0;
}

.reward-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.my-bounty-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
}

.my-bounty-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.my-bounty-info {
  flex: 1;
}

.my-bounty-target {
  font-weight: bold;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.my-bounty-time {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.my-bounty-details {
  text-align: right;
}

.my-bounty-amount {
  font-weight: bold;
  color: #10b981;
  margin: 0 0 4px 0;
}

.my-bounty-status {
  font-size: 0.875rem;
  font-weight: 600;
  margin: 0;
}

.status-active {
  color: #f59e0b;
}

.status-claimed {
  color: #dc2626;
}

.status-expired {
  color: #6b7280;
}

.info-section {
  background: #dbeafe;
  border-left: 4px solid #3b82f6;
  border-radius: 8px;
  padding: 20px;
}

.info-section h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e40af;
  margin: 0 0 12px 0;
}

.info-section ul {
  margin: 0;
  padding: 0;
  list-style: none;
  color: #1e40af;
}

.info-section li {
  font-size: 0.875rem;
  margin-bottom: 6px;
}

@media (max-width: 768px) {
  .bounty-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }

  .warning-content {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }

  .warning-amount {
    text-align: center;
  }

  .form-actions {
    flex-direction: column;
  }

  .bounty-content {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }

  .bounty-reward {
    text-align: center;
  }

  .my-bounty-content {
    flex-direction: column;
    gap: 12px;
    text-align: center;
  }

  .my-bounty-details {
    text-align: center;
  }
}
</style>

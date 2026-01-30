<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import api from '@/services/api';

// Data
const loading = ref(true);
const error = ref('');
const drugPrices = ref([]);
const playerDrugs = ref([]);
const totalValue = ref(0);
const player = ref({
  cash: 0,
  location: { name: 'Unknown' }
});

// Form state
const buyQuantity = ref<{ [key: number]: number }>({});
const sellQuantity = ref<{ [key: number]: number }>({});
const buyProcessing = ref<{ [key: number]: boolean }>({});
const sellProcessing = ref<{ [key: number]: boolean }>({});

// Fetch data on mount
const fetchData = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    const response = await api.get('/drugs');
    drugPrices.value = response.data.drugPrices || [];
    playerDrugs.value = response.data.playerDrugs || [];
    totalValue.value = response.data.totalValue || 0;
    player.value = response.data.player || player.value;
    
    // Initialize quantities
    drugPrices.value.forEach((drug: any) => {
      buyQuantity.value[drug.id] = 1;
    });
    playerDrugs.value.forEach((playerDrug: any) => {
      sellQuantity.value[playerDrug.id] = 1;
    });
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load drug data';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});

// Buy drug
const buy = async (drug: any) => {
  if (buyProcessing.value[drug.id]) return;
  
  const quantity = buyQuantity.value[drug.id] || 1;
  const cost = drug.price * quantity;
  
  if (player.value.cash < cost) {
    alert('Not enough cash!');
    return;
  }
  
  buyProcessing.value[drug.id] = true;
  error.value = '';
  
  try {
    const response = await api.post('/drugs/buy', {
      drug_id: drug.id,
      quantity: quantity
    });
    
    // Update data
    if (response.data.success) {
      await fetchData();
      buyQuantity.value[drug.id] = 1;
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to buy drugs';
  } finally {
    buyProcessing.value[drug.id] = false;
  }
};

// Sell drug
const sell = async (playerDrug: any) => {
  if (sellProcessing.value[playerDrug.id]) return;
  
  const quantity = sellQuantity.value[playerDrug.id] || 1;
  
  if (quantity > playerDrug.quantity) {
    alert('Not enough drugs to sell!');
    return;
  }
  
  sellProcessing.value[playerDrug.id] = true;
  error.value = '';
  
  try {
    const response = await api.post('/drugs/sell', {
      drug_id: playerDrug.drug.id,
      quantity: quantity
    });
    
    // Update data
    if (response.data.success) {
      await fetchData();
      sellQuantity.value[playerDrug.id] = 1;
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to sell drugs';
  } finally {
    sellProcessing.value[playerDrug.id] = false;
  }
};

// Helper functions
const getRiskColor = (chance: number) => {
  if (chance < 10) return 'risk-low';
  if (chance < 20) return 'risk-medium';
  if (chance < 30) return 'risk-high';
  return 'risk-very-high';
};

const getRiskLabel = (chance: number) => {
  if (chance < 10) return 'Low Risk';
  if (chance < 20) return 'Medium Risk';
  if (chance < 30) return 'High Risk';
  return 'Very High Risk';
};

const formatNumber = (num: number) => {
  return num?.toLocaleString() || '0';
};

const getDrugPrice = (drugId: number) => {
  const drug = drugPrices.value.find((d: any) => d.id === drugId);
  return drug?.price || 0;
};

const calculateBuyCost = (drugId: number) => {
  const drug = drugPrices.value.find((d: any) => d.id === drugId);
  const quantity = buyQuantity.value[drugId] || 1;
  return (drug?.price || 0) * quantity;
};

const calculateSellRevenue = (playerDrugId: number) => {
  const playerDrug = playerDrugs.value.find((pd: any) => pd.id === playerDrugId);
  if (!playerDrug) return 0;
  const price = getDrugPrice(playerDrug.drug.id);
  const quantity = sellQuantity.value[playerDrugId] || 1;
  return price * quantity;
};

const calculateTotalValue = (playerDrugId: number) => {
  const playerDrug = playerDrugs.value.find((pd: any) => pd.id === playerDrugId);
  if (!playerDrug) return 0;
  const price = getDrugPrice(playerDrug.drug.id);
  return price * playerDrug.quantity;
};
</script>

<template>
  <div class="drugs-container">
    <!-- Header -->
    <div class="header">
      <h1 class="title">💊 Drug Dealing</h1>
      <router-link to="/dashboard" class="back-button">← Dashboard</router-link>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading drug market...</p>
    </div>

    <!-- Error State -->
    <div v-if="error && !loading" class="error-banner">
      <strong>Error:</strong> {{ error }}
      <button @click="fetchData" class="retry-button">Retry</button>
    </div>

    <!-- Content -->
    <div v-if="!loading" class="content">
      <!-- Warning Banner -->
      <div class="warning-banner">
        <div class="warning-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
            </path>
          </svg>
        </div>
        <div class="warning-content">
          <p class="warning-title">⚠️ Warning: Risk of Police Bust!</p>
          <p class="warning-text">Higher risk drugs have higher bust chances. If caught, you'll lose your drugs/money and go to jail!</p>
        </div>
      </div>

      <!-- Player Stats -->
      <div class="stats-card">
        <div class="stat">
          <p class="stat-label">Cash</p>
          <p class="stat-value stat-cash">${{ formatNumber(player.cash) }}</p>
        </div>
        <div class="stat">
          <p class="stat-label">Drug Value</p>
          <p class="stat-value stat-drugs">${{ formatNumber(totalValue) }}</p>
        </div>
        <div class="stat">
          <p class="stat-label">Current Location</p>
          <p class="stat-value stat-location">{{ player.location?.name || 'Unknown' }}</p>
        </div>
        <div class="stat">
          <p class="stat-label">Drug Types Owned</p>
          <p class="stat-value">{{ playerDrugs.length }}</p>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="main-grid">
        <!-- Buy Section -->
        <div class="section">
          <h2 class="section-title">💵 Buy Drugs</h2>
          <div class="drug-list">
            <div v-for="drug in drugPrices" :key="drug.id" class="drug-card buy-card">
              <div class="drug-header">
                <div class="drug-info">
                  <h3 class="drug-name">{{ drug.name }}</h3>
                  <p class="drug-description">{{ drug.description }}</p>
                </div>
                <span :class="['risk-badge', getRiskColor(drug.bust_chance)]">
                  {{ getRiskLabel(drug.bust_chance) }}
                </span>
              </div>

              <div class="drug-pricing">
                <div>
                  <p class="price">${{ formatNumber(drug.price) }} <span class="price-unit">/ unit</span></p>
                  <p class="bust-chance">Bust chance: {{ drug.bust_chance }}%</p>
                </div>
              </div>

              <div class="drug-actions">
                <input 
                  v-model.number="buyQuantity[drug.id]" 
                  type="number" 
                  min="1" 
                  max="1000" 
                  placeholder="Quantity"
                  class="quantity-input">
                <button 
                  @click="buy(drug)" 
                  :disabled="player.cash < calculateBuyCost(drug.id) || buyProcessing[drug.id]"
                  class="action-button buy-button">
                  <span v-if="buyProcessing[drug.id]">Buying...</span>
                  <span v-else>Buy ${{ formatNumber(calculateBuyCost(drug.id)) }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Sell Section -->
        <div class="section">
          <h2 class="section-title">💰 Your Stash</h2>
          <div v-if="playerDrugs.length > 0" class="drug-list">
            <div v-for="playerDrug in playerDrugs" :key="playerDrug.id" class="drug-card sell-card">
              <div class="drug-header">
                <div class="drug-info">
                  <h3 class="drug-name">{{ playerDrug.drug.name }}</h3>
                  <p class="drug-description">Quantity: {{ playerDrug.quantity }} units</p>
                </div>
                <span :class="['risk-badge', getRiskColor(playerDrug.drug.bust_chance * 0.5)]">
                  {{ getRiskLabel(playerDrug.drug.bust_chance * 0.5) }}
                </span>
              </div>

              <div class="drug-pricing">
                <div>
                  <p class="price-label">Current Price</p>
                  <p class="price">${{ formatNumber(getDrugPrice(playerDrug.drug.id)) }} <span class="price-unit">/ unit</span></p>
                  <p class="total-value">Total Value: ${{ formatNumber(calculateTotalValue(playerDrug.id)) }}</p>
                </div>
              </div>

              <div class="drug-actions">
                <input 
                  v-model.number="sellQuantity[playerDrug.id]" 
                  type="number" 
                  min="1" 
                  :max="playerDrug.quantity" 
                  placeholder="Quantity"
                  class="quantity-input">
                <button 
                  @click="sell(playerDrug)" 
                  :disabled="sellQuantity[playerDrug.id] > playerDrug.quantity || sellProcessing[playerDrug.id]"
                  class="action-button sell-button">
                  <span v-if="sellProcessing[playerDrug.id]">Selling...</span>
                  <span v-else>Sell ${{ formatNumber(calculateSellRevenue(playerDrug.id)) }}</span>
                </button>
              </div>
            </div>
          </div>
          <div v-else class="empty-stash">
            <p class="empty-title">You have no drugs</p>
            <p class="empty-text">Buy some drugs on the left to start dealing!</p>
          </div>

          <!-- Travel Tip -->
          <div class="tip-banner">
            <div class="tip-icon">
              <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
              </svg>
            </div>
            <div>
              <p class="tip-title">💡 Pro Tip</p>
              <p class="tip-text">
                Prices vary by location! Travel to different cities to find the best deals.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Container */
.drugs-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  padding: 2rem;
  color: #fff;
}

/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  max-width: 1400px;
  margin-left: auto;
  margin-right: auto;
}

.title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #fff;
  margin: 0;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.back-button {
  padding: 0.75rem 1.5rem;
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.back-button:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

/* Loading */
.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  color: #fff;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(147, 51, 234, 0.2);
  border-top-color: #9333ea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error */
.error-banner {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-left: 4px solid #ef4444;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1400px;
  margin-left: auto;
  margin-right: auto;
  color: #fca5a5;
}

.retry-button {
  padding: 0.5rem 1rem;
  background: #ef4444;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.retry-button:hover {
  background: #dc2626;
}

/* Content */
.content {
  max-width: 1400px;
  margin: 0 auto;
}

/* Warning Banner */
.warning-banner {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-left: 4px solid #ef4444;
  padding: 1.5rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: flex-start;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.warning-icon {
  color: #ef4444;
  margin-right: 1rem;
  flex-shrink: 0;
}

.warning-content {
  flex: 1;
}

.warning-title {
  font-weight: 700;
  color: #fca5a5;
  margin: 0 0 0.5rem 0;
  font-size: 1rem;
}

.warning-text {
  color: #fecaca;
  margin: 0;
  font-size: 0.875rem;
  line-height: 1.5;
}

/* Stats Card */
.stats-card {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.stat {
  text-align: center;
}

.stat-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
  margin: 0 0 0.5rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  color: #fff;
}

.stat-cash {
  color: #22c55e;
  text-shadow: 0 0 20px rgba(34, 197, 94, 0.5);
}

.stat-drugs {
  color: #9333ea;
  text-shadow: 0 0 20px rgba(147, 51, 234, 0.5);
}

.stat-location {
  font-size: 1.5rem;
  color: #fff;
}

/* Main Grid */
.main-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 2rem;
}

@media (max-width: 1100px) {
  .main-grid {
    grid-template-columns: 1fr;
  }
}

/* Section */
.section {
  display: flex;
  flex-direction: column;
}

.section-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0 0 1.5rem 0;
  color: #fff;
}

/* Drug List */
.drug-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Drug Card */
.drug-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.drug-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.buy-card {
  border-left: 4px solid #22c55e;
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.05) 0%, rgba(34, 197, 94, 0.02) 100%);
}

.sell-card {
  border-left: 4px solid #9333ea;
  background: linear-gradient(135deg, rgba(147, 51, 234, 0.05) 0%, rgba(147, 51, 234, 0.02) 100%);
}

/* Drug Header */
.drug-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.drug-info {
  flex: 1;
}

.drug-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #fff;
  margin: 0 0 0.5rem 0;
}

.drug-description {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
  margin: 0;
}

/* Risk Badge */
.risk-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
}

.risk-low {
  background: rgba(34, 197, 94, 0.2);
  color: #4ade80;
  border: 1px solid rgba(34, 197, 94, 0.4);
}

.risk-medium {
  background: rgba(234, 179, 8, 0.2);
  color: #facc15;
  border: 1px solid rgba(234, 179, 8, 0.4);
}

.risk-high {
  background: rgba(249, 115, 22, 0.2);
  color: #fb923c;
  border: 1px solid rgba(249, 115, 22, 0.4);
}

.risk-very-high {
  background: rgba(239, 68, 68, 0.2);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.4);
}

/* Drug Pricing */
.drug-pricing {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.price-label {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.6);
  margin: 0 0 0.25rem 0;
}

.price {
  font-size: 1.75rem;
  font-weight: 700;
  color: #22c55e;
  margin: 0;
  text-shadow: 0 0 15px rgba(34, 197, 94, 0.4);
}

.sell-card .price {
  color: #9333ea;
  text-shadow: 0 0 15px rgba(147, 51, 234, 0.4);
}

.price-unit {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.5);
  font-weight: 400;
}

.bust-chance {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
  margin: 0.25rem 0 0 0;
}

.total-value {
  font-size: 0.875rem;
  color: #22c55e;
  font-weight: 600;
  margin: 0.25rem 0 0 0;
}

/* Drug Actions */
.drug-actions {
  display: flex;
  gap: 0.75rem;
}

.quantity-input {
  flex: 1;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  color: #fff;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.quantity-input:focus {
  outline: none;
  border-color: rgba(255, 255, 255, 0.4);
  background: rgba(255, 255, 255, 0.15);
}

.quantity-input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.action-button {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
  color: #fff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.buy-button {
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
}

.buy-button:hover:not(:disabled) {
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
}

.sell-button {
  background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%);
}

.sell-button:hover:not(:disabled) {
  background: linear-gradient(135deg, #7e22ce 0%, #6b21a8 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(147, 51, 234, 0.4);
}

.action-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

/* Empty Stash */
.empty-stash {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 4rem;
  text-align: center;
}

.empty-title {
  font-size: 1.25rem;
  color: rgba(255, 255, 255, 0.6);
  margin: 0 0 1rem 0;
}

.empty-text {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.4);
  margin: 0;
}

/* Tip Banner */
.tip-banner {
  margin-top: 1.5rem;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(37, 99, 235, 0.15) 100%);
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-left: 4px solid #3b82f6;
  padding: 1rem;
  border-radius: 12px;
  display: flex;
  align-items: flex-start;
}

.tip-icon {
  color: #3b82f6;
  margin-right: 0.75rem;
  flex-shrink: 0;
}

.tip-title {
  font-weight: 600;
  color: #93c5fd;
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
}

.tip-text {
  color: #bfdbfe;
  margin: 0;
  font-size: 0.75rem;
  line-height: 1.5;
}

/* Responsive */
@media (max-width: 768px) {
  .drugs-container {
    padding: 1rem;
  }

  .header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .title {
    font-size: 1.75rem;
    text-align: center;
  }

  .stats-card {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    padding: 1rem;
  }

  .stat-value {
    font-size: 1.5rem;
  }

  .main-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .drug-actions {
    flex-direction: column;
  }

  .action-button {
    width: 100%;
  }
}
</style>

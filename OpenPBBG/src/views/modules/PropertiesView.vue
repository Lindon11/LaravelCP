<template>
  <div class="properties-page">
    <header class="properties-header">
      <h1>🏠 Properties</h1>
      <router-link to="/dashboard" class="back-link">← Dashboard</router-link>
    </header>

    <div class="container">
      <!-- Flash Messages -->
      <div v-if="successMessage" class="flash flash-success">{{ successMessage }}</div>
      <div v-if="errorMessage" class="flash flash-error">{{ errorMessage }}</div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading properties...</p>
      </div>

      <!-- Main Content -->
      <div v-else-if="player" class="properties-content">
        <!-- Tabs -->
        <div class="tabs-container">
          <div class="tabs-header">
            <button 
              @click="activeTab = 'available'" 
              :class="{ active: activeTab === 'available' }"
              class="tab-button"
            >
              Available Properties
            </button>
            <button 
              @click="activeTab = 'mine'" 
              :class="{ active: activeTab === 'mine' }"
              class="tab-button"
            >
              My Properties ({{ myProperties.length }})
            </button>
          </div>

          <!-- Available Properties Tab -->
          <div v-if="activeTab === 'available'" class="tab-content">
            <div v-for="type in ['house', 'business', 'warehouse']" :key="type" class="property-section">
              <h3 class="section-title">{{ type }}s</h3>
              <div class="properties-grid">
                <div 
                  v-for="property in groupedAvailable(type)" 
                  :key="property.id" 
                  class="property-card"
                >
                  <div class="property-header">
                    <div class="property-info">
                      <h4 class="property-name">{{ property.name }}</h4>
                      <p class="property-description">{{ property.description }}</p>
                    </div>
                    <div class="property-price">
                      <p class="price-amount">{{ formatMoney(property.price) }}</p>
                      <p class="level-requirement">Lvl {{ property.required_level }}+</p>
                    </div>
                  </div>
                  <div class="property-footer">
                    <div class="income-info">
                      <span class="income-amount">{{ formatMoney(property.income_per_day) }}/day</span>
                    </div>
                    <button 
                      @click="buyProperty(property.id)" 
                      :disabled="processing || player.level < property.required_level || player.cash < property.price"
                      class="btn btn-buy"
                    >
                      Buy
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- My Properties Tab -->
          <div v-else class="tab-content">
            <div v-if="myProperties.length === 0" class="empty-state">
              <p class="empty-title">You don't own any properties</p>
              <p class="empty-subtitle">Start investing to earn passive income!</p>
            </div>
            <div v-else>
              <!-- Income Summary -->
              <div class="income-banner">
                <div class="income-details">
                  <p class="income-label">Total Daily Income</p>
                  <p class="income-total">{{ formatMoney(totalIncome) }}</p>
                </div>
                <button 
                  @click="collectIncome" 
                  :disabled="processing"
                  class="btn btn-collect"
                >
                  Collect Income
                </button>
              </div>

              <!-- Owned Properties List -->
              <div class="owned-properties">
                <div 
                  v-for="property in myProperties" 
                  :key="property.id" 
                  class="owned-property-card"
                >
                  <div class="owned-property-content">
                    <div class="owned-property-info">
                      <h4 class="owned-property-name">{{ property.name }}</h4>
                      <p class="owned-property-description">{{ property.description }}</p>
                      <p class="owned-income">{{ formatMoney(property.income_per_day) }}/day</p>
                    </div>
                    <div class="owned-property-actions">
                      <p class="purchase-price">Purchased: {{ formatMoney(property.price) }}</p>
                      <button 
                        @click="sellProperty(property.id)" 
                        :disabled="processing"
                        class="btn btn-sell"
                      >
                        Sell ({{ formatMoney(property.price * 0.7) }})
                      </button>
                    </div>
                  </div>
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
import { useRouter } from 'vue-router';

const router = useRouter();

const player = ref(null);
const available = ref([]);
const myProperties = ref([]);
const loading = ref(true);
const processing = ref(false);
const activeTab = ref('available');
const successMessage = ref('');
const errorMessage = ref('');

const formatMoney = (val) => {
  return new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'USD', 
    minimumFractionDigits: 0 
  }).format(val);
};

const groupedAvailable = (type) => {
  return available.value.filter(p => p.type === type);
};

const totalIncome = computed(() => {
  return myProperties.value.reduce((sum, p) => sum + parseFloat(p.income_per_day || 0), 0);
});

const loadProperties = async () => {
  try {
    loading.value = true;
    const response = await api.get('/properties');
    player.value = response.data.player;
    available.value = response.data.available || [];
    myProperties.value = response.data.myProperties || [];
  } catch (error) {
    console.error('Failed to load properties:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to load properties';
  } finally {
    loading.value = false;
  }
};

const buyProperty = async (propertyId) => {
  if (processing.value) return;
  
  try {
    processing.value = true;
    const response = await api.post(`/properties/${propertyId}/buy`);
    successMessage.value = response.data.message || 'Property purchased successfully!';
    errorMessage.value = '';
    await loadProperties();
  } catch (error) {
    console.error('Failed to buy property:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to purchase property';
    successMessage.value = '';
  } finally {
    processing.value = false;
    setTimeout(() => {
      successMessage.value = '';
      errorMessage.value = '';
    }, 5000);
  }
};

const sellProperty = async (propertyId) => {
  if (processing.value || !confirm('Sell this property for 70% of purchase price?')) return;
  
  try {
    processing.value = true;
    const response = await api.post(`/properties/${propertyId}/sell`);
    successMessage.value = response.data.message || 'Property sold successfully!';
    errorMessage.value = '';
    await loadProperties();
  } catch (error) {
    console.error('Failed to sell property:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to sell property';
    successMessage.value = '';
  } finally {
    processing.value = false;
    setTimeout(() => {
      successMessage.value = '';
      errorMessage.value = '';
    }, 5000);
  }
};

const collectIncome = async () => {
  if (processing.value) return;
  
  try {
    processing.value = true;
    const response = await api.post('/properties/collect');
    successMessage.value = response.data.message || 'Income collected successfully!';
    errorMessage.value = '';
    await loadProperties();
  } catch (error) {
    console.error('Failed to collect income:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to collect income';
    successMessage.value = '';
  } finally {
    processing.value = false;
    setTimeout(() => {
      successMessage.value = '';
      errorMessage.value = '';
    }, 5000);
  }
};

onMounted(() => {
  loadProperties();
});
</script>

<style scoped>
.properties-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #e0f2e9 0%, #c8e6d5 100%);
  padding: 20px;
}

.properties-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto 30px;
}

.properties-header h1 {
  font-size: 2rem;
  font-weight: bold;
  color: #047857;
  margin: 0;
}

.back-link {
  padding: 10px 20px;
  background: #10b981;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  transition: background 0.3s;
}

.back-link:hover {
  background: #059669;
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
  border-top: 4px solid #10b981;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.tabs-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.tabs-header {
  display: flex;
  border-bottom: 2px solid #e5e7eb;
}

.tab-button {
  flex: 1;
  padding: 16px;
  background: none;
  border: none;
  font-size: 1rem;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.3s;
  border-bottom: 3px solid transparent;
}

.tab-button:hover {
  background: #f9fafb;
}

.tab-button.active {
  color: #10b981;
  border-bottom-color: #10b981;
}

.tab-content {
  padding: 24px;
}

.property-section {
  margin-bottom: 40px;
}

.section-title {
  font-size: 1.75rem;
  font-weight: bold;
  color: #047857;
  margin-bottom: 16px;
  text-transform: capitalize;
}

.properties-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.property-card {
  background: white;
  border: 2px solid #d1fae5;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s;
}

.property-card:hover {
  border-color: #10b981;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

.property-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.property-info {
  flex: 1;
  margin-right: 16px;
}

.property-name {
  font-size: 1.25rem;
  font-weight: bold;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.property-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.property-price {
  text-align: right;
}

.price-amount {
  font-size: 1.5rem;
  font-weight: bold;
  color: #10b981;
  margin: 0;
}

.level-requirement {
  font-size: 0.75rem;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.property-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.income-info {
  font-size: 0.875rem;
}

.income-amount {
  font-weight: 600;
  color: #2563eb;
}

.btn {
  padding: 10px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-buy {
  background: #10b981;
  color: white;
}

.btn-buy:hover:not(:disabled) {
  background: #059669;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-title {
  font-size: 1.5rem;
  color: #6b7280;
  margin: 0 0 8px 0;
}

.empty-subtitle {
  font-size: 0.875rem;
  color: #9ca3af;
  margin: 0;
}

.income-banner {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border-radius: 12px;
  padding: 32px;
  margin-bottom: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.income-details {
  flex: 1;
}

.income-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.9);
  margin: 0 0 8px 0;
}

.income-total {
  font-size: 2.5rem;
  font-weight: bold;
  margin: 0;
}

.btn-collect {
  background: #fbbf24;
  color: #1f2937;
  font-size: 1.125rem;
  padding: 14px 28px;
}

.btn-collect:hover:not(:disabled) {
  background: #f59e0b;
}

.owned-properties {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.owned-property-card {
  background: white;
  border: 2px solid #d1fae5;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s;
}

.owned-property-card:hover {
  border-color: #10b981;
  background: #f0fdf4;
}

.owned-property-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.owned-property-info {
  flex: 1;
}

.owned-property-name {
  font-size: 1.25rem;
  font-weight: bold;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.owned-property-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0 0 8px 0;
}

.owned-income {
  font-size: 1rem;
  font-weight: 600;
  color: #2563eb;
  margin: 0;
}

.owned-property-actions {
  text-align: right;
}

.purchase-price {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0 0 12px 0;
}

.btn-sell {
  background: #ef4444;
  color: white;
}

.btn-sell:hover:not(:disabled) {
  background: #dc2626;
}

@media (max-width: 768px) {
  .properties-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }

  .properties-grid {
    grid-template-columns: 1fr;
  }

  .income-banner {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }

  .owned-property-content {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }

  .owned-property-actions {
    text-align: center;
    width: 100%;
  }
}
</style>

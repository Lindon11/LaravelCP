<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const router = useRouter();

const player = ref(null);
const costPerBullet = ref(50);
const quantity = ref(100);
const loading = ref(true);
const processing = ref(false);
const error = ref('');
const successMessage = ref('');

const formatMoney = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
  }).format(amount);
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num);
};

const totalCost = computed(() => {
  return quantity.value * costPerBullet.value;
});

const canAfford = computed(() => {
  return player.value && player.value.cash >= totalCost.value;
});

const cashAfterPurchase = computed(() => {
  return player.value ? player.value.cash - totalCost.value : 0;
});

const fetchData = async () => {
  try {
    loading.value = true;
    error.value = '';
    const response = await api.get('/bullets');
    player.value = response.data.player;
    costPerBullet.value = response.data.costPerBullet || 50;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load bullet shop data';
  } finally {
    loading.value = false;
  }
};

const buyBullets = async () => {
  if (processing.value || !canAfford.value || quantity.value <= 0) return;

  try {
    processing.value = true;
    error.value = '';
    successMessage.value = '';
    
    const response = await api.post('/bullets/buy', { 
      quantity: quantity.value 
    });
    
    successMessage.value = response.data.message || `Successfully purchased ${formatNumber(quantity.value)} bullets!`;
    await fetchData();
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to purchase bullets';
  } finally {
    processing.value = false;
  }
};

const setQuickQuantity = (amount) => {
  quantity.value = amount;
};

onMounted(() => {
  fetchData();
});
</script>

<template>
  <div class="bullets-view">
    <div class="header">
      <div class="header-content">
        <h1>🔫 Bullet Shop</h1>
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

        <!-- Current Status -->
        <div class="status-section">
          <h2>Your Arsenal</h2>
          
          <div class="stats-grid">
            <div class="stat-card bullets">
              <p class="stat-label">Current Bullets</p>
              <p class="stat-value">{{ formatNumber(player.bullets) }}</p>
            </div>
            <div class="stat-card cash">
              <p class="stat-label">Cash Available</p>
              <p class="stat-value">{{ formatMoney(player.cash) }}</p>
            </div>
          </div>
        </div>

        <!-- Buy Bullets -->
        <div class="buy-section">
          <div class="buy-header">
            <h2>💥 Buy Ammunition</h2>
            <p class="price-tag">{{ formatMoney(costPerBullet) }} per bullet</p>
          </div>

          <div class="buy-form">
            <label class="form-label">Quantity</label>
            <input
              v-model.number="quantity"
              type="number"
              min="1"
              step="1"
              class="quantity-input"
            >

            <!-- Quick Select -->
            <div class="quick-select">
              <button 
                @click="setQuickQuantity(50)"
                class="quick-btn"
              >
                50
              </button>
              <button 
                @click="setQuickQuantity(100)"
                class="quick-btn"
              >
                100
              </button>
              <button 
                @click="setQuickQuantity(250)"
                class="quick-btn"
              >
                250
              </button>
              <button 
                @click="setQuickQuantity(500)"
                class="quick-btn"
              >
                500
              </button>
              <button 
                @click="setQuickQuantity(1000)"
                class="quick-btn"
              >
                1000
              </button>
            </div>

            <!-- Cost Display -->
            <div class="cost-display">
              <div class="cost-row">
                <span class="cost-label">Total Cost:</span>
                <span class="cost-value">{{ formatMoney(totalCost) }}</span>
              </div>
              <div class="cost-row cash-after">
                <span class="cost-label">Cash After Purchase:</span>
                <span class="cost-value" :class="{ negative: cashAfterPurchase < 0 }">
                  {{ formatMoney(cashAfterPurchase) }}
                </span>
              </div>
            </div>

            <!-- Buy Button -->
            <button
              @click="buyBullets"
              :disabled="processing || !canAfford || quantity <= 0"
              class="buy-btn"
            >
              <template v-if="processing">
                Processing...
              </template>
              <template v-else-if="!canAfford">
                Not Enough Cash
              </template>
              <template v-else>
                Buy {{ formatNumber(quantity) }} Bullets for {{ formatMoney(totalCost) }}
              </template>
            </button>
          </div>
        </div>

        <!-- Info Box -->
        <div class="info-section">
          <h3>🎯 Bullet Information</h3>
          <ul class="info-list">
            <li>Bullets are required to attack other players</li>
            <li>Each attack attempt uses 1 bullet</li>
            <li>Price: {{ formatMoney(costPerBullet) }} per bullet</li>
            <li>Stock up before going hunting!</li>
            <li>You can't attack without bullets</li>
            <li>Bullets don't expire - buy in bulk to save time</li>
          </ul>
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
.bullets-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #475569 0%, #1e293b 100%);
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
  max-width: 1000px;
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

.status-section {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  margin-bottom: 2rem;
}

.status-section h2 {
  font-size: 1.75rem;
  font-weight: bold;
  color: #1f2937;
  margin: 0 0 1.5rem 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  padding: 2rem;
  border-radius: 0.75rem;
  text-align: center;
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.stat-card.bullets {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.stat-card.cash {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
}

.stat-label {
  font-size: 0.875rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-card.bullets .stat-label {
  color: #92400e;
}

.stat-card.cash .stat-label {
  color: #065f46;
}

.stat-value {
  font-size: 3rem;
  font-weight: bold;
  margin: 0;
}

.stat-card.bullets .stat-value {
  color: #b45309;
}

.stat-card.cash .stat-value {
  color: #047857;
}

.buy-section {
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  margin-bottom: 2rem;
}

.buy-header {
  margin-bottom: 1.5rem;
}

.buy-header h2 {
  font-size: 2rem;
  font-weight: bold;
  color: white;
  margin: 0 0 0.25rem 0;
}

.price-tag {
  font-size: 1.125rem;
  color: #fed7aa;
  margin: 0;
}

.buy-form {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 0.75rem;
  padding: 1.5rem;
}

.form-label {
  display: block;
  color: white;
  font-weight: 600;
  font-size: 1.125rem;
  margin-bottom: 0.75rem;
}

.quantity-input {
  width: 100%;
  padding: 1rem 1.5rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 1.5rem;
  font-weight: bold;
  text-align: center;
  outline: none;
  margin-bottom: 1rem;
}

.quick-select {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.quick-btn {
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: bold;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.quick-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.cost-display {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.cost-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.cost-row + .cost-row {
  margin-top: 0.5rem;
}

.cost-label {
  font-size: 1rem;
}

.cost-value {
  font-size: 1.875rem;
  font-weight: bold;
}

.cost-row.cash-after {
  padding-top: 0.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.cost-row.cash-after .cost-label {
  font-size: 0.875rem;
}

.cost-row.cash-after .cost-value {
  font-size: 1.125rem;
}

.cost-value.negative {
  color: #fee2e2;
}

.buy-btn {
  width: 100%;
  padding: 1.25rem 2rem;
  background: white;
  color: #ea580c;
  border: none;
  border-radius: 0.5rem;
  font-weight: bold;
  font-size: 1.25rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.buy-btn:hover:not(:disabled) {
  background: #fed7aa;
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.buy-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.info-section {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 1rem;
  padding: 1.5rem;
  color: white;
}

.info-section h3 {
  font-size: 1.25rem;
  font-weight: bold;
  margin: 0 0 1rem 0;
}

.info-list {
  margin: 0;
  padding-left: 1.5rem;
  list-style: disc;
}

.info-list li {
  font-size: 0.875rem;
  line-height: 1.75;
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
  color: #475569;
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

  .buy-header h2 {
    font-size: 1.5rem;
  }

  .quick-select {
    grid-template-columns: repeat(3, 1fr);
  }

  .stat-value {
    font-size: 2rem;
  }

  .cost-value {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .quick-select {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const router = useRouter();

const loading = ref(true);
const error = ref(null);
const processing = ref(false);

const inventory = ref([]);
const player = ref(null);
const selectedFilter = ref('all');
const flashMessage = ref(null);
const flashType = ref('success');

// Filter inventory by type
const filteredInventory = computed(() => {
  if (selectedFilter.value === 'all') {
    return inventory.value;
  }
  return inventory.value.filter(item => item.item.type === selectedFilter.value);
});

// Get equipped items
const equippedItems = computed(() => {
  return inventory.value.filter(item => item.equipped);
});

// Format numbers with commas
const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num);
};

// Get rarity color class
const getRarityColor = (rarity) => {
  const colors = {
    common: 'rarity-common',
    uncommon: 'rarity-uncommon',
    rare: 'rarity-rare',
    epic: 'rarity-epic',
    legendary: 'rarity-legendary'
  };
  return colors[rarity] || 'rarity-common';
};

// Load inventory data
const loadInventory = async () => {
  try {
    loading.value = true;
    error.value = null;
    const response = await api.get('/modules/inventory');
    
    inventory.value = response.data.inventory || [];
    player.value = response.data.player || null;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load inventory';
    console.error('Error loading inventory:', err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadInventory();
});

// Show flash message
const showFlash = (message, type = 'success') => {
  flashMessage.value = message;
  flashType.value = type;
  setTimeout(() => {
    flashMessage.value = null;
  }, 5000);
};

// Equip item
const equip = async (inventoryId) => {
  if (processing.value) return;
  
  processing.value = true;
  
  try {
    const response = await api.post(`/modules/inventory/equip/${inventoryId}`);
    showFlash(response.data.message || 'Item equipped successfully', 'success');
    await loadInventory();
  } catch (err) {
    const message = err.response?.data?.message || 'Failed to equip item';
    showFlash(message, 'error');
  } finally {
    processing.value = false;
  }
};

// Unequip item
const unequip = async (inventoryId) => {
  if (processing.value) return;
  
  processing.value = true;
  
  try {
    const response = await api.post(`/modules/inventory/unequip/${inventoryId}`);
    showFlash(response.data.message || 'Item unequipped successfully', 'success');
    await loadInventory();
  } catch (err) {
    const message = err.response?.data?.message || 'Failed to unequip item';
    showFlash(message, 'error');
  } finally {
    processing.value = false;
  }
};

// Use item
const useItem = async (inventoryId) => {
  if (processing.value) return;
  
  if (!confirm('Are you sure you want to use this item?')) {
    return;
  }
  
  processing.value = true;
  
  try {
    const response = await api.post(`/modules/inventory/use/${inventoryId}`);
    showFlash(response.data.message || 'Item used successfully', 'success');
    await loadInventory();
  } catch (err) {
    const message = err.response?.data?.message || 'Failed to use item';
    showFlash(message, 'error');
  } finally {
    processing.value = false;
  }
};

// Sell item
const sell = async (inventoryId, maxQuantity) => {
  if (processing.value) return;
  
  const quantity = 1; // Default to 1 for now
  
  if (!confirm(`Are you sure you want to sell ${quantity} of this item?`)) {
    return;
  }
  
  processing.value = true;
  
  try {
    const response = await api.post(`/modules/inventory/sell/${inventoryId}`, { quantity });
    showFlash(response.data.message || 'Item sold successfully', 'success');
    await loadInventory();
  } catch (err) {
    const message = err.response?.data?.message || 'Failed to sell item';
    showFlash(message, 'error');
  } finally {
    processing.value = false;
  }
};

const goToDashboard = () => {
  router.push('/dashboard');
};

const goToShop = () => {
  router.push('/shop');
};
</script>

<template>
  <div class="inventory-view">
    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">💼 Inventory</h1>
      <div class="header-actions">
        <button @click="goToShop" class="shop-button">
          🛒 Shop
        </button>
        <button @click="goToDashboard" class="back-button">
          ← Dashboard
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading inventory...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <div class="error-icon">⚠️</div>
      <p>{{ error }}</p>
      <button @click="loadInventory" class="retry-button">Retry</button>
    </div>

    <!-- Main Content -->
    <div v-else class="content">
      <!-- Flash Messages -->
      <div v-if="flashMessage" class="flash-message" :class="flashType">
        {{ flashMessage }}
      </div>

      <!-- Player Stats -->
      <div class="player-stats">
        <div class="stat-card">
          <p class="stat-label">Cash</p>
          <p class="stat-value cash">${{ formatNumber(player?.cash || 0) }}</p>
        </div>
        <div class="stat-card">
          <p class="stat-label">Total Items</p>
          <p class="stat-value">{{ formatNumber(inventory.length) }}</p>
        </div>
        <div class="stat-card">
          <p class="stat-label">Equipped</p>
          <p class="stat-value">{{ formatNumber(equippedItems.length) }}</p>
        </div>
      </div>

      <!-- Equipped Items -->
      <div v-if="equippedItems.length > 0" class="equipped-section">
        <h2 class="equipped-title">⚔️ Equipped Items</h2>
        <div class="equipped-grid">
          <div v-for="item in equippedItems" :key="item.id" class="equipped-card">
            <div class="equipped-info">
              <p class="equipped-slot">{{ item.slot }}</p>
              <p class="equipped-name">{{ item.item.name }}</p>
              <div class="equipped-stats">
                <span v-for="(value, stat) in item.item.stats" :key="stat" class="stat-badge">
                  {{ stat }}: +{{ value }}
                </span>
              </div>
            </div>
            <button @click="unequip(item.id)" class="unequip-button" :disabled="processing">
              Unequip
            </button>
          </div>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="filter-tabs">
        <button 
          @click="selectedFilter = 'all'" 
          :class="{ active: selectedFilter === 'all' }"
          class="filter-tab"
        >
          All Items
        </button>
        <button 
          @click="selectedFilter = 'weapon'" 
          :class="{ active: selectedFilter === 'weapon' }"
          class="filter-tab"
        >
          ⚔️ Weapons
        </button>
        <button 
          @click="selectedFilter = 'armor'" 
          :class="{ active: selectedFilter === 'armor' }"
          class="filter-tab"
        >
          🛡️ Armor
        </button>
        <button 
          @click="selectedFilter = 'vehicle'" 
          :class="{ active: selectedFilter === 'vehicle' }"
          class="filter-tab"
        >
          🚗 Vehicles
        </button>
        <button 
          @click="selectedFilter = 'consumable'" 
          :class="{ active: selectedFilter === 'consumable' }"
          class="filter-tab"
        >
          💊 Consumables
        </button>
      </div>

      <!-- Inventory Grid -->
      <div v-if="filteredInventory.length > 0" class="inventory-grid">
        <div v-for="item in filteredInventory" :key="item.id" class="item-card">
          <div class="rarity-bar" :class="getRarityColor(item.item.rarity)"></div>
          <div class="item-content">
            <div class="item-header">
              <div>
                <h3 class="item-name">{{ item.item.name }}</h3>
                <p class="item-type">{{ item.item.type }}</p>
              </div>
              <span class="rarity-badge" :class="getRarityColor(item.item.rarity)">
                {{ item.item.rarity }}
              </span>
            </div>
            
            <p class="item-description">{{ item.item.description }}</p>
            
            <!-- Stats -->
            <div v-if="item.item.stats && Object.keys(item.item.stats).length > 0" class="item-stats">
              <span v-for="(value, stat) in item.item.stats" :key="stat" class="stat-tag">
                {{ stat }}: +{{ value }}
              </span>
            </div>
            
            <!-- Quantity and Price -->
            <div class="item-footer">
              <p class="item-quantity">
                Quantity: <span class="quantity-value">{{ formatNumber(item.quantity) }}</span>
              </p>
              <p class="item-price">
                Sell: ${{ formatNumber(item.item.sell_price * item.quantity) }}
              </p>
            </div>
            
            <!-- Actions -->
            <div class="item-actions">
              <button 
                v-if="!item.equipped && item.item.type !== 'consumable' && item.item.type !== 'misc'" 
                @click="equip(item.id)"
                :disabled="processing"
                class="action-button equip-button"
              >
                Equip
              </button>
              <button 
                v-if="item.item.type === 'consumable'" 
                @click="useItem(item.id)"
                :disabled="processing"
                class="action-button use-button"
              >
                Use
              </button>
              <button 
                v-if="item.item.tradeable && !item.equipped" 
                @click="sell(item.id, item.quantity)"
                :disabled="processing"
                class="action-button sell-button"
              >
                Sell
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-inventory">
        <div class="empty-icon">📦</div>
        <p class="empty-message">No items in your inventory</p>
        <button @click="goToShop" class="shop-link-button">
          Visit Shop
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.inventory-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #22d3ee 100%);
  padding: 2rem 1rem;
}

.page-header {
  max-width: 1400px;
  margin: 0 auto 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-title {
  font-size: 2.5rem;
  font-weight: bold;
  color: white;
  margin: 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.shop-button,
.back-button {
  padding: 0.75rem 1.5rem;
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.shop-button {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.shop-button:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.back-button {
  background: rgba(255, 255, 255, 0.2);
}

.back-button:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.content {
  max-width: 1400px;
  margin: 0 auto;
}

.loading-state,
.error-state {
  background: white;
  border-radius: 12px;
  padding: 3rem;
  text-align: center;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f4f6;
  border-top-color: #0891b2;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state {
  color: #dc2626;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.retry-button {
  margin-top: 1rem;
  padding: 0.75rem 2rem;
  background: #0891b2;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
}

.retry-button:hover {
  background: #0e7490;
}

.flash-message {
  padding: 1rem 1.5rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-weight: 600;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.flash-message.success {
  background: #d1fae5;
  color: #065f46;
  border: 2px solid #10b981;
}

.flash-message.error {
  background: #fee2e2;
  color: #991b1b;
  border: 2px solid #ef4444;
}

.player-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0 0 0.5rem 0;
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
  color: #111827;
  margin: 0;
}

.stat-value.cash {
  color: #10b981;
}

.equipped-section {
  background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 40px rgba(168, 85, 247, 0.3);
}

.equipped-title {
  font-size: 2rem;
  font-weight: bold;
  color: white;
  margin: 0 0 1.5rem 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.equipped-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

.equipped-card {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.equipped-info {
  flex: 1;
}

.equipped-slot {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
  text-transform: uppercase;
  margin: 0 0 0.25rem 0;
  font-weight: 600;
}

.equipped-name {
  font-size: 1.25rem;
  font-weight: bold;
  color: white;
  margin: 0 0 0.75rem 0;
}

.equipped-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.stat-badge {
  font-size: 0.75rem;
  background: rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-weight: 600;
}

.unequip-button {
  padding: 0.75rem 1.25rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.unequip-button:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.unequip-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.filter-tabs {
  background: white;
  border-radius: 12px;
  padding: 0;
  margin-bottom: 2rem;
  display: flex;
  overflow-x: auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.filter-tab {
  flex: 1;
  padding: 1rem 1.5rem;
  background: transparent;
  border: none;
  border-bottom: 3px solid transparent;
  color: #6b7280;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.filter-tab:hover {
  color: #0891b2;
  background: #f0fdfa;
}

.filter-tab.active {
  color: #0891b2;
  border-bottom-color: #0891b2;
  background: #f0fdfa;
}

.inventory-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.item-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.item-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.rarity-bar {
  height: 6px;
}

.rarity-common { background: #6b7280; }
.rarity-uncommon { background: #10b981; }
.rarity-rare { background: #3b82f6; }
.rarity-epic { background: #a855f7; }
.rarity-legendary { background: #eab308; }

.item-content {
  padding: 1.5rem;
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  gap: 1rem;
}

.item-name {
  font-size: 1.25rem;
  font-weight: bold;
  color: #111827;
  margin: 0 0 0.25rem 0;
}

.item-type {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
  text-transform: capitalize;
}

.rarity-badge {
  font-size: 0.75rem;
  padding: 0.375rem 0.75rem;
  color: white;
  border-radius: 12px;
  text-transform: uppercase;
  font-weight: bold;
  white-space: nowrap;
}

.item-description {
  color: #4b5563;
  font-size: 0.875rem;
  line-height: 1.5;
  margin: 0 0 1rem 0;
}

.item-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.stat-tag {
  background: #dbeafe;
  color: #1e40af;
  font-size: 0.75rem;
  padding: 0.375rem 0.75rem;
  border-radius: 8px;
  font-weight: 600;
}

.item-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-top: 1rem;
  border-top: 2px solid #f3f4f6;
}

.item-quantity,
.item-price {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.quantity-value {
  font-weight: bold;
  color: #111827;
}

.item-price {
  color: #10b981;
  font-weight: 600;
}

.item-actions {
  display: flex;
  gap: 0.5rem;
}

.action-button {
  flex: 1;
  padding: 0.75rem 1rem;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.action-button:hover:not(:disabled) {
  transform: translateY(-2px);
}

.action-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.equip-button {
  background: #3b82f6;
}

.equip-button:hover:not(:disabled) {
  background: #2563eb;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.use-button {
  background: #10b981;
}

.use-button:hover:not(:disabled) {
  background: #059669;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.sell-button {
  background: #f59e0b;
}

.sell-button:hover:not(:disabled) {
  background: #d97706;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.empty-inventory {
  background: white;
  border-radius: 12px;
  padding: 4rem 2rem;
  text-align: center;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-message {
  font-size: 1.25rem;
  color: #6b7280;
  margin: 0 0 1.5rem 0;
}

.shop-link-button {
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.shop-link-button:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
}

@media (max-width: 768px) {
  .inventory-view {
    padding: 1rem 0.5rem;
  }

  .page-title {
    font-size: 2rem;
  }

  .header-actions {
    width: 100%;
    justify-content: stretch;
  }

  .shop-button,
  .back-button {
    flex: 1;
  }

  .filter-tabs {
    flex-wrap: nowrap;
    overflow-x: auto;
  }

  .filter-tab {
    flex: 0 0 auto;
  }

  .inventory-grid {
    grid-template-columns: 1fr;
  }

  .equipped-card {
    flex-direction: column;
    align-items: stretch;
  }

  .unequip-button {
    width: 100%;
  }
}
</style>

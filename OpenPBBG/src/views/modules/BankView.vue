<template>
  <div class="bank-container">
    <div class="header">
      <div class="header-content">
        <router-link to="/dashboard" class="back-link">← Back</router-link>
        <div class="stats-bar">
          <div class="stat-item"><span class="stat-icon cash">💰</span> Cash: {{ formatMoney(player?.cash || 0) }}</div>
          <div class="stat-item"><span class="stat-icon bank">🏦</span> Bank: {{ formatMoney(player?.bank || 0) }}</div>
        </div>
      </div>
    </div>

    <div class="content-wrapper">
      <div class="bank-banner">
        <div class="banner-content">
          <div>
            <h1 class="banner-title">🏦 City Bank</h1>
            <p class="banner-subtitle">Secure your money and manage your finances</p>
          </div>
          <div class="banner-icon">💵</div>
        </div>
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
      </div>

      <div v-else>
        <!-- Tabs -->
        <div class="tabs">
          <button @click="activeTab = 'deposit'" 
                  class="tab-button"
                  :class="activeTab === 'deposit' ? 'active' : ''">
            Deposit
          </button>
          <button @click="activeTab = 'withdraw'"
                  class="tab-button"
                  :class="activeTab === 'withdraw' ? 'active' : ''">
            Withdraw
          </button>
          <button @click="activeTab = 'transfer'"
                  class="tab-button"
                  :class="activeTab === 'transfer' ? 'active' : ''">
            Transfer
          </button>
        </div>

        <!-- Deposit Tab -->
        <div v-if="activeTab === 'deposit'" class="tab-panel">
          <h3 class="section-title">💰 Deposit Money</h3>
          <p class="fee-notice">Deposit fee: {{ taxRate }}%</p>
          
          <div class="input-group">
            <label class="input-label">Amount</label>
            <input v-model="depositAmount" type="number" min="1" :max="player?.cash"
                   class="amount-input"
                   placeholder="Enter amount">
            <div class="quick-buttons">
              <button @click="setDepositAmount('half')" class="quick-button">Half</button>
              <button @click="setDepositAmount('all')" class="quick-button">All</button>
            </div>
          </div>

          <div v-if="depositAmount > 0" class="transaction-summary">
            <div class="summary-row"><span>Deposit Amount:</span><span>{{ formatMoney(parseInt(depositAmount)) }}</span></div>
            <div class="summary-row tax"><span>Tax ({{ taxRate }}%):</span><span>-{{ formatMoney(depositTax) }}</span></div>
            <div class="summary-row total">
              <span>You'll Receive:</span><span>{{ formatMoney(depositAfterTax) }}</span>
            </div>
          </div>

          <button @click="deposit" :disabled="processing || !depositAmount || depositAmount <= 0 || depositAmount > player?.cash"
                  class="action-button deposit"
                  :class="!processing && depositAmount > 0 && depositAmount <= player?.cash ? 'enabled' : 'disabled'">
            {{ processing ? 'Processing...' : 'Deposit' }}
          </button>
        </div>

        <!-- Withdraw Tab -->
        <div v-if="activeTab === 'withdraw'" class="tab-panel">
          <h3 class="section-title">💸 Withdraw Money</h3>
          
          <div class="input-group">
            <label class="input-label">Amount</label>
            <input v-model="withdrawAmount" type="number" min="1" :max="player?.bank"
                   class="amount-input"
                   placeholder="Enter amount">
            <div class="quick-buttons">
              <button @click="setWithdrawAmount('half')" class="quick-button">Half</button>
              <button @click="setWithdrawAmount('all')" class="quick-button">All</button>
            </div>
          </div>

          <button @click="withdraw" :disabled="processing || !withdrawAmount || withdrawAmount <= 0 || withdrawAmount > player?.bank"
                  class="action-button withdraw"
                  :class="!processing && withdrawAmount > 0 && withdrawAmount <= player?.bank ? 'enabled' : 'disabled'">
            {{ processing ? 'Processing...' : 'Withdraw' }}
          </button>
        </div>

        <!-- Transfer Tab -->
        <div v-if="activeTab === 'transfer'" class="tab-panel">
          <h3 class="section-title">💳 Transfer Money</h3>
          
          <div class="input-group">
            <label class="input-label">Recipient Username</label>
            <input v-model="transferRecipient" type="text"
                   class="amount-input"
                   placeholder="Enter username">
          </div>

          <div class="input-group">
            <label class="input-label">Amount</label>
            <input v-model="transferAmount" type="number" min="1" :max="player?.bank"
                   class="amount-input"
                   placeholder="Enter amount">
          </div>

          <button @click="transfer" :disabled="processing || !transferRecipient || !transferAmount || transferAmount <= 0 || transferAmount > player?.bank"
                  class="action-button transfer"
                  :class="!processing && transferRecipient && transferAmount > 0 && transferAmount <= player?.bank ? 'enabled' : 'disabled'">
            {{ processing ? 'Processing...' : 'Transfer' }}
          </button>
        </div>

        <div v-if="result" class="result-message" :class="result.success ? 'success' : 'error'">
          <p>{{ result.message }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const player = ref(null)
const taxRate = ref(15)
const depositAmount = ref('')
const withdrawAmount = ref('')
const transferRecipient = ref('')
const transferAmount = ref('')
const processing = ref(false)
const loading = ref(true)
const activeTab = ref('deposit')
const result = ref(null)

const formatMoney = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(val)

const depositAfterTax = computed(() => {
  const amount = parseInt(depositAmount.value) || 0
  return Math.floor(amount * ((100 - taxRate.value) / 100))
})

const depositTax = computed(() => {
  const amount = parseInt(depositAmount.value) || 0
  return amount - depositAfterTax.value
})

const setDepositAmount = (type) => {
  if (type === 'all') depositAmount.value = player.value.cash.toString()
  else if (type === 'half') depositAmount.value = Math.floor(player.value.cash / 2).toString()
}

const setWithdrawAmount = (type) => {
  if (type === 'all') withdrawAmount.value = player.value.bank.toString()
  else if (type === 'half') withdrawAmount.value = Math.floor(player.value.bank / 2).toString()
}

const loadData = async () => {
  try {
    const [bankResponse, playerResponse] = await Promise.all([
      api.get('/bank'),
      api.get('/user')
    ])
    taxRate.value = bankResponse.data.taxRate || 15
    player.value = playerResponse.data
  } catch (err) {
    console.error('Error loading bank:', err)
  } finally {
    loading.value = false
  }
}

const deposit = async () => {
  if (processing.value) return
  processing.value = true
  result.value = null
  
  try {
    const response = await api.post('/bank/deposit', { amount: parseInt(depositAmount.value) })
    result.value = { success: true, message: response.data.message }
    player.value = response.data.player
    depositAmount.value = ''
  } catch (err) {
    result.value = { success: false, message: err.response?.data?.message || 'Deposit failed' }
  } finally {
    processing.value = false
  }
}

const withdraw = async () => {
  if (processing.value) return
  processing.value = true
  result.value = null
  
  try {
    const response = await api.post('/bank/withdraw', { amount: parseInt(withdrawAmount.value) })
    result.value = { success: true, message: response.data.message }
    player.value = response.data.player
    withdrawAmount.value = ''
  } catch (err) {
    result.value = { success: false, message: err.response?.data?.message || 'Withdrawal failed' }
  } finally {
    processing.value = false
  }
}

const transfer = async () => {
  if (processing.value) return
  processing.value = true
  result.value = null
  
  try {
    const response = await api.post('/bank/transfer', { 
      recipient: transferRecipient.value,
      amount: parseInt(transferAmount.value)
    })
    result.value = { success: true, message: response.data.message }
    player.value = response.data.player
    transferRecipient.value = ''
    transferAmount.value = ''
  } catch (err) {
    result.value = { success: false, message: err.response?.data?.message || 'Transfer failed' }
  } finally {
    processing.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.bank-container {
  min-height: 100vh;
  background: linear-gradient(to bottom right, #111827, #064e3b, #111827);
}

.header {
  background-color: rgba(31, 41, 55, 0.5);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(34, 197, 94, 0.3);
  padding: 1rem;
}

.header-content {
  max-width: 80rem;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.back-link {
  color: #4ade80;
  text-decoration: none;
}

.back-link:hover {
  color: #86efac;
}

.stats-bar {
  display: flex;
  gap: 1.5rem;
  font-size: 0.875rem;
}

.stat-item {
  color: #d1d5db;
}

.stat-icon.cash {
  color: #4ade80;
}

.stat-icon.bank {
  color: #60a5fa;
}

.content-wrapper {
  max-width: 64rem;
  margin: 0 auto;
  padding: 1.5rem;
}

.bank-banner {
  background: linear-gradient(to right, #059669, #10b981);
  color: white;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  margin-bottom: 1.5rem;
}

.banner-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.banner-title {
  font-size: 2.25rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.banner-subtitle {
  color: #bbf7d0;
}

.banner-icon {
  font-size: 4.5rem;
}

.loading-state {
  text-align: center;
  padding: 3rem 0;
}

.spinner {
  display: inline-block;
  width: 3rem;
  height: 3rem;
  border: 2px solid transparent;
  border-bottom-color: #22c55e;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.tab-button {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: bold;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.tab-button.active {
  background-color: #059669;
  color: white;
}

.tab-button:not(.active) {
  background-color: #374151;
  color: #9ca3af;
}

.tab-button:not(.active):hover {
  background-color: #4b5563;
}

.tab-panel {
  background-color: rgba(31, 41, 55, 0.5);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(34, 197, 94, 0.3);
  border-radius: 0.5rem;
  padding: 1.5rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: bold;
  color: white;
  margin-bottom: 1rem;
}

.fee-notice {
  color: #9ca3af;
  margin-bottom: 1rem;
}

.input-group {
  margin-bottom: 1rem;
}

.input-label {
  display: block;
  color: white;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.amount-input {
  width: 100%;
  padding: 0.75rem 1rem;
  background-color: #374151;
  color: white;
  border: 1px solid #4b5563;
  border-radius: 0.5rem;
  margin-bottom: 0.5rem;
}

.quick-buttons {
  display: flex;
  gap: 0.5rem;
}

.quick-button {
  padding: 0.5rem 1rem;
  background-color: #374151;
  color: white;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.quick-button:hover {
  background-color: #4b5563;
}

.transaction-summary {
  background-color: rgba(55, 65, 81, 0.5);
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  color: white;
}

.summary-row {
  display: flex;
  justify-content: space-between;
}

.summary-row.tax {
  color: #f87171;
}

.summary-row.total {
  font-weight: bold;
  color: #4ade80;
  font-size: 1.125rem;
  border-top: 1px solid #4b5563;
  padding-top: 0.5rem;
}

.action-button {
  width: 100%;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: bold;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.action-button.deposit.enabled {
  background: linear-gradient(to right, #059669, #10b981);
  color: white;
}

.action-button.deposit.enabled:hover {
  background: linear-gradient(to right, #10b981, #34d399);
}

.action-button.withdraw.enabled {
  background: linear-gradient(to right, #2563eb, #0891b2);
  color: white;
}

.action-button.withdraw.enabled:hover {
  background: linear-gradient(to right, #3b82f6, #06b6d4);
}

.action-button.transfer.enabled {
  background: linear-gradient(to right, #9333ea, #ec4899);
  color: white;
}

.action-button.transfer.enabled:hover {
  background: linear-gradient(to right, #a855f7, #f472b6);
}

.action-button.disabled {
  background-color: #374151;
  color: #6b7280;
  cursor: not-allowed;
}

.result-message {
  margin-top: 1.5rem;
  padding: 1rem;
  border-radius: 0.5rem;
}

.result-message.success {
  background-color: rgba(34, 197, 94, 0.2);
  border: 1px solid #22c55e;
}

.result-message.error {
  background-color: rgba(239, 68, 68, 0.2);
  border: 1px solid #ef4444;
}

.result-message p {
  color: white;
  margin: 0;
}
</style>

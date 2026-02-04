<template>
  <div class="user-tools">
    <!-- Header with Search -->
    <div class="view-header">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-100">User Tools</h1>
          <p class="text-slate-400 mt-1">Inspect and manage individual user data, timers, and activity</p>
        </div>
        <div class="search-box">
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by username, email, or ID..."
              class="search-input"
              @keyup.enter="searchUser"
            />
            <button @click="searchUser" class="search-btn">
              🔍 Find User
            </button>
          </div>
          <!-- Search Results Dropdown -->
          <div v-if="searchResults.length > 0" class="search-results">
            <div
              v-for="result in searchResults"
              :key="result.id"
              class="search-result-item"
              @click="selectUser(result)"
            >
              <span class="font-medium">{{ result.username }}</span>
              <span class="text-slate-400 text-sm ml-2">#{{ result.id }}</span>
              <span class="text-slate-500 text-xs ml-2">Lvl {{ result.level }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No User Selected -->
    <div v-if="!selectedUser" class="empty-state">
      <div class="text-6xl mb-4">👤</div>
      <h3 class="text-xl text-slate-300 mb-2">No User Selected</h3>
      <p class="text-slate-500">Search for a user above to view their details</p>
    </div>

    <!-- User Selected View -->
    <div v-else class="user-view">
      <!-- User Header -->
      <div class="user-header">
        <div class="user-info">
          <div class="user-avatar">
            {{ selectedUser.username.charAt(0).toUpperCase() }}
          </div>
          <div>
            <h2 class="text-xl font-bold text-slate-100">
              {{ selectedUser.username }}
              <span class="text-slate-500 text-sm font-normal">#{{ selectedUser.id }}</span>
            </h2>
            <div class="text-sm text-slate-400 mt-1">
              Level {{ selectedUser.level }} ·
              {{ selectedUser.currentRank?.name || selectedUser.current_rank?.name || 'No Rank' }} ·
              {{ selectedUser.location?.name || 'Unknown Location' }}
            </div>
          </div>
        </div>
        <button @click="clearUser" class="btn btn-secondary">
          ✕ Clear
        </button>
      </div>

      <!-- Tabs -->
      <div class="tabs">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          :class="['tab', { active: activeTab === tab.key }]"
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Items Tab -->
        <div v-if="activeTab === 'items'" class="tab-panel">
          <div class="panel-header">
            <h3>Inventory Items</h3>
            <div class="text-sm text-slate-400">
              Total: {{ inventory.total_items || 0 }} items ·
              Value: ${{ (inventory.total_value || 0).toLocaleString() }}
            </div>
          </div>
          <div v-if="loadingInventory" class="loading">Loading inventory...</div>
          <table v-else-if="inventory.inventory?.length" class="data-table">
            <thead>
              <tr>
                <th>Item Name</th>
                <th>Type</th>
                <th>Codename</th>
                <th>Quantity</th>
                <th>Value</th>
                <th>Equipped</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in inventory.inventory" :key="item.id">
                <td>{{ item.item_name }}</td>
                <td><span class="badge">{{ item.item_type }}</span></td>
                <td><code class="codename">{{ item.item_codename || '-' }}</code></td>
                <td>{{ item.quantity }}</td>
                <td>${{ (item.item_value * item.quantity).toLocaleString() }}</td>
                <td>
                  <span v-if="item.equipped" class="text-green-400">✓</span>
                  <span v-else class="text-slate-500">-</span>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="empty">No items in inventory</div>
        </div>

        <!-- Jobs Tab -->
        <div v-if="activeTab === 'jobs'" class="tab-panel">
          <div class="panel-header">
            <h3>Job Statistics</h3>
          </div>
          <div v-if="loadingJobs" class="loading">Loading jobs...</div>
          <table v-else-if="jobs.length" class="data-table">
            <thead>
              <tr>
                <th>Job Name</th>
                <th>Codename</th>
                <th>Type</th>
                <th>Total Completed</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="job in jobs" :key="job.codename">
                <td>{{ job.name }}</td>
                <td><code class="codename">{{ job.codename || '-' }}</code></td>
                <td><span class="badge">{{ job.type }}</span></td>
                <td>{{ job.total_completed }}</td>
              </tr>
            </tbody>
          </table>
          <div v-else class="empty">No job statistics found</div>
        </div>

        <!-- Job History Tab -->
        <div v-if="activeTab === 'history'" class="tab-panel">
          <div class="panel-header">
            <h3>Job History</h3>
            <select v-model="historyFilter" @change="loadJobHistory" class="filter-select">
              <option value="all">All Types</option>
              <option value="crime_attempt">Crimes</option>
              <option value="theft_attempt">Theft</option>
              <option value="gym_train">Gym</option>
              <option value="organized_crime">Organized Crime</option>
            </select>
          </div>
          <div v-if="loadingHistory" class="loading">Loading history...</div>
          <table v-else-if="jobHistory.length" class="data-table">
            <thead>
              <tr>
                <th>Job Name</th>
                <th>Codename</th>
                <th>Type</th>
                <th>Result</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="entry in jobHistory" :key="entry.id">
                <td>{{ entry.name }}</td>
                <td><code class="codename">{{ entry.codename || '-' }}</code></td>
                <td><span class="badge">{{ entry.type }}</span></td>
                <td>
                  <span v-if="entry.success === true" class="text-green-400">Success</span>
                  <span v-else-if="entry.success === false" class="text-red-400">Failed</span>
                  <span v-else class="text-slate-400">-</span>
                </td>
                <td>{{ formatDateTime(entry.time) }}</td>
              </tr>
            </tbody>
          </table>
          <div v-else class="empty">No job history found</div>
        </div>

        <!-- Timers Tab -->
        <div v-if="activeTab === 'timers'" class="tab-panel">
          <div class="panel-header">
            <h3>Active Timers & Cooldowns</h3>
            <button @click="loadTimers" class="btn btn-secondary btn-sm">🔄 Refresh</button>
          </div>
          <div v-if="loadingTimers" class="loading">Loading timers...</div>
          <table v-else-if="allTimers.length" class="data-table">
            <thead>
              <tr>
                <th>Timer Type</th>
                <th>Expires At</th>
                <th>Remaining</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="timer in allTimers" :key="timer.type">
                <td><span class="badge" :class="timer.type">{{ formatTimerType(timer.type) }}</span></td>
                <td>{{ formatDateTime(timer.expires_at) }}</td>
                <td>{{ formatRemaining(timer.expires_at) }}</td>
                <td>
                  <button @click="clearTimer(timer.type)" class="btn btn-danger btn-sm">
                    Clear
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="empty">No active timers</div>
        </div>

        <!-- Flags Tab -->
        <div v-if="activeTab === 'flags'" class="tab-panel">
          <div class="panel-header">
            <h3>User Flags & Tags</h3>
            <button @click="showAddFlag = true" class="btn btn-primary btn-sm">+ Add Flag</button>
          </div>
          <div v-if="loadingFlags" class="loading">Loading flags...</div>
          <div v-else-if="flags.length" class="flags-grid">
            <div
              v-for="flag in flags"
              :key="flag.type"
              :class="['flag-card', flag.severity]"
            >
              <div class="flag-header">
                <span class="flag-label">{{ flag.label }}</span>
                <button
                  v-if="flag.type !== 'role'"
                  @click="removeFlag(flag.type)"
                  class="flag-remove"
                >×</button>
              </div>
              <div v-if="flag.value && flag.value !== true" class="flag-value">
                {{ typeof flag.value === 'string' && flag.value.includes('T') ? formatDateTime(flag.value) : flag.value }}
              </div>
              <div v-if="flag.reason" class="flag-reason">{{ flag.reason }}</div>
            </div>
          </div>
          <div v-else class="empty">No flags assigned</div>

          <!-- Add Flag Modal -->
          <div v-if="showAddFlag" class="modal-overlay" @click.self="showAddFlag = false">
            <div class="modal">
              <h3>Add Flag</h3>
              <div class="form-group">
                <label>Flag Type</label>
                <input v-model="newFlag.type" type="text" placeholder="e.g., suspicious, vip, warning" />
              </div>
              <div class="form-group">
                <label>Label</label>
                <input v-model="newFlag.label" type="text" placeholder="Display label" />
              </div>
              <div class="form-group">
                <label>Reason (optional)</label>
                <textarea v-model="newFlag.reason" placeholder="Reason for flag"></textarea>
              </div>
              <div class="form-group">
                <label>Severity</label>
                <select v-model="newFlag.severity">
                  <option value="info">Info (Blue)</option>
                  <option value="success">Success (Green)</option>
                  <option value="warning">Warning (Yellow)</option>
                  <option value="danger">Danger (Red)</option>
                </select>
              </div>
              <div class="modal-actions">
                <button @click="showAddFlag = false" class="btn btn-secondary">Cancel</button>
                <button @click="addFlag" class="btn btn-primary">Add Flag</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Activity Tab -->
        <div v-if="activeTab === 'activity'" class="tab-panel">
          <div class="panel-header">
            <h3>Recent Activity</h3>
          </div>
          <div v-if="loadingActivity" class="loading">Loading activity...</div>
          <table v-else-if="activity.length" class="data-table">
            <thead>
              <tr>
                <th>Type</th>
                <th>Description</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in activity" :key="log.id">
                <td><span class="badge" :class="log.type">{{ log.type }}</span></td>
                <td>{{ log.description }}</td>
                <td>{{ formatDateTime(log.created_at) }}</td>
              </tr>
            </tbody>
          </table>
          <div v-else class="empty">No activity found</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import api from '@/services/api'

const searchQuery = ref('')
const searchResults = ref([])
const selectedUser = ref(null)
const activeTab = ref('items')

const tabs = [
  { key: 'items', label: 'Items' },
  { key: 'jobs', label: 'Jobs' },
  { key: 'history', label: 'Job History' },
  { key: 'timers', label: 'Timers' },
  { key: 'flags', label: 'Flags' },
  { key: 'activity', label: 'Activity' },
]

// Data states
const inventory = ref({})
const jobs = ref([])
const jobHistory = ref([])
const timers = ref({ timers: [], user_timers: [] })
const flags = ref([])
const activity = ref([])
const historyFilter = ref('all')

// Loading states
const loadingInventory = ref(false)
const loadingJobs = ref(false)
const loadingHistory = ref(false)
const loadingTimers = ref(false)
const loadingFlags = ref(false)
const loadingActivity = ref(false)

// Add flag modal
const showAddFlag = ref(false)
const newFlag = ref({
  type: '',
  label: '',
  reason: '',
  severity: 'info',
})

// Computed
const allTimers = computed(() => {
  const t = [...(timers.value.user_timers || [])]
  for (const timer of (timers.value.timers || [])) {
    t.push({
      type: timer.type,
      expires_at: timer.expires_at,
    })
  }
  return t
})

// Methods
const searchUser = async () => {
  if (!searchQuery.value.trim()) return
  try {
    const response = await api.get('/admin/user-tools/search', {
      params: { q: searchQuery.value }
    })
    searchResults.value = response.data.users || []
  } catch (err) {
    console.error('Search failed:', err)
  }
}

const selectUser = async (user) => {
  searchResults.value = []
  searchQuery.value = ''
  try {
    console.log('Fetching user:', user.id)
    const response = await api.get(`/admin/user-tools/${user.id}`)
    console.log('User response:', response.data)
    selectedUser.value = response.data.user
    console.log('Selected user set:', selectedUser.value)
    loadTabData()
  } catch (err) {
    console.error('Failed to load user:', err)
    alert('Failed to load user: ' + (err.response?.data?.message || err.message))
  }
}

const clearUser = () => {
  selectedUser.value = null
  inventory.value = {}
  jobs.value = []
  jobHistory.value = []
  timers.value = { timers: [], user_timers: [] }
  flags.value = []
  activity.value = []
}

const loadTabData = () => {
  if (!selectedUser.value) return

  switch (activeTab.value) {
    case 'items':
      loadInventory()
      break
    case 'jobs':
      loadJobs()
      break
    case 'history':
      loadJobHistory()
      break
    case 'timers':
      loadTimers()
      break
    case 'flags':
      loadFlags()
      break
    case 'activity':
      loadActivity()
      break
  }
}

const loadInventory = async () => {
  loadingInventory.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/inventory`)
    inventory.value = response.data
  } catch (err) {
    console.error('Failed to load inventory:', err)
  } finally {
    loadingInventory.value = false
  }
}

const loadJobs = async () => {
  loadingJobs.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/jobs`)
    jobs.value = response.data.jobs || []
  } catch (err) {
    console.error('Failed to load jobs:', err)
  } finally {
    loadingJobs.value = false
  }
}

const loadJobHistory = async () => {
  loadingHistory.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/job-history`, {
      params: { type: historyFilter.value }
    })
    jobHistory.value = response.data.history || []
  } catch (err) {
    console.error('Failed to load job history:', err)
  } finally {
    loadingHistory.value = false
  }
}

const loadTimers = async () => {
  loadingTimers.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/timers`)
    timers.value = response.data
  } catch (err) {
    console.error('Failed to load timers:', err)
  } finally {
    loadingTimers.value = false
  }
}

const clearTimer = async (timerType) => {
  if (!confirm(`Clear ${timerType} timer?`)) return
  try {
    await api.delete(`/admin/user-tools/${selectedUser.value.id}/timers/${timerType}`)
    loadTimers()
  } catch (err) {
    alert('Failed to clear timer')
  }
}

const loadFlags = async () => {
  loadingFlags.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/flags`)
    flags.value = response.data.flags || []
  } catch (err) {
    console.error('Failed to load flags:', err)
  } finally {
    loadingFlags.value = false
  }
}

const addFlag = async () => {
  try {
    await api.post(`/admin/user-tools/${selectedUser.value.id}/flags`, newFlag.value)
    showAddFlag.value = false
    newFlag.value = { type: '', label: '', reason: '', severity: 'info' }
    loadFlags()
  } catch (err) {
    alert('Failed to add flag')
  }
}

const removeFlag = async (flagType) => {
  if (!confirm(`Remove ${flagType} flag?`)) return
  try {
    await api.delete(`/admin/user-tools/${selectedUser.value.id}/flags/${flagType}`)
    loadFlags()
  } catch (err) {
    alert('Failed to remove flag')
  }
}

const loadActivity = async () => {
  loadingActivity.value = true
  try {
    const response = await api.get(`/admin/user-tools/${selectedUser.value.id}/activity`)
    activity.value = response.data.activity || []
  } catch (err) {
    console.error('Failed to load activity:', err)
  } finally {
    loadingActivity.value = false
  }
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).replace(',', ' at')
}

const formatRemaining = (expiresAt) => {
  const now = new Date()
  const expires = new Date(expiresAt)
  const diff = expires - now

  if (diff <= 0) return 'Expired'

  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))

  if (hours > 0) return `${hours}h ${minutes}m`
  return `${minutes}m`
}

const formatTimerType = (type) => {
  return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// Watch tab changes
watch(activeTab, loadTabData)
</script>

<style scoped>
.user-tools {
  padding: 1.5rem;
  max-width: 1400px;
  margin: 0 auto;
}

.view-header {
  margin-bottom: 1.5rem;
}

.search-box {
  position: relative;
}

.search-input {
  width: 350px;
  padding: 0.75rem 1rem;
  padding-right: 120px;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.5rem;
  color: #f1f5f9;
  font-size: 0.875rem;
}

.search-btn {
  position: absolute;
  right: 4px;
  top: 4px;
  bottom: 4px;
  padding: 0 1rem;
  background: #0ea5e9;
  border: none;
  border-radius: 0.375rem;
  color: white;
  font-weight: 500;
  cursor: pointer;
}

.search-btn:hover {
  background: #0284c7;
}

.search-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 0.25rem;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.5rem;
  max-height: 300px;
  overflow-y: auto;
  z-index: 50;
}

.search-result-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid #334155;
}

.search-result-item:hover {
  background: #334155;
}

.search-result-item:last-child {
  border-bottom: none;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: #1e293b;
  border-radius: 0.75rem;
  border: 1px dashed #334155;
}

.user-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem;
  background: #1e293b;
  border-radius: 0.75rem;
  margin-bottom: 1rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #0ea5e9, #8b5cf6);
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
}

.tabs {
  display: flex;
  gap: 0.25rem;
  background: #1e293b;
  padding: 0.25rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}

.tab {
  padding: 0.625rem 1.25rem;
  background: transparent;
  border: none;
  border-radius: 0.375rem;
  color: #94a3b8;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.tab:hover {
  color: #f1f5f9;
  background: #334155;
}

.tab.active {
  background: #0ea5e9;
  color: white;
}

.tab-content {
  background: #1e293b;
  border-radius: 0.75rem;
  min-height: 400px;
}

.tab-panel {
  padding: 1.25rem;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #334155;
}

.panel-header h3 {
  color: #f1f5f9;
  font-size: 1.125rem;
  font-weight: 600;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #334155;
}

.data-table th {
  color: #94a3b8;
  font-weight: 500;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.data-table td {
  color: #e2e8f0;
}

.data-table tbody tr:hover {
  background: #334155/50;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  background: #334155;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
  color: #94a3b8;
}

.badge.crime, .badge.crime_attempt { background: #7c3aed20; color: #a78bfa; }
.badge.theft, .badge.theft_attempt { background: #f59e0b20; color: #fbbf24; }
.badge.gym, .badge.gym_train { background: #10b98120; color: #34d399; }
.badge.travel { background: #3b82f620; color: #60a5fa; }
.badge.jail { background: #ef444420; color: #f87171; }
.badge.hospital { background: #f4364420; color: #fb7185; }
.badge.login { background: #10b98120; color: #34d399; }
.badge.logout { background: #64748b20; color: #94a3b8; }
.badge.combat { background: #ef444420; color: #f87171; }
.badge.mission { background: #8b5cf620; color: #a78bfa; }
.badge.organized_crime { background: #f59e0b20; color: #fbbf24; }

.codename {
  background: #0f172a;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  color: #94a3b8;
}

.loading {
  padding: 2rem;
  text-align: center;
  color: #94a3b8;
}

.empty {
  padding: 2rem;
  text-align: center;
  color: #64748b;
}

.flags-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.flag-card {
  padding: 1rem;
  background: #0f172a;
  border-radius: 0.5rem;
  border-left: 3px solid #64748b;
}

.flag-card.info { border-left-color: #3b82f6; }
.flag-card.success { border-left-color: #10b981; }
.flag-card.warning { border-left-color: #f59e0b; }
.flag-card.danger { border-left-color: #ef4444; }

.flag-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.flag-label {
  font-weight: 600;
  color: #f1f5f9;
}

.flag-remove {
  background: none;
  border: none;
  color: #64748b;
  font-size: 1.25rem;
  cursor: pointer;
  line-height: 1;
}

.flag-remove:hover {
  color: #ef4444;
}

.flag-value {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #94a3b8;
}

.flag-reason {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: #64748b;
}

.filter-select {
  padding: 0.5rem 0.75rem;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 0.375rem;
  color: #f1f5f9;
  font-size: 0.875rem;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background: #0ea5e9;
  color: white;
}

.btn-primary:hover {
  background: #0284c7;
}

.btn-secondary {
  background: #334155;
  color: #f1f5f9;
}

.btn-secondary:hover {
  background: #475569;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal {
  background: #1e293b;
  padding: 1.5rem;
  border-radius: 0.75rem;
  width: 400px;
  max-width: 90vw;
}

.modal h3 {
  color: #f1f5f9;
  margin-bottom: 1rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  color: #94a3b8;
  font-size: 0.875rem;
  margin-bottom: 0.375rem;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.625rem;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 0.375rem;
  color: #f1f5f9;
}

.form-group textarea {
  min-height: 80px;
  resize: vertical;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1.5rem;
}
</style>

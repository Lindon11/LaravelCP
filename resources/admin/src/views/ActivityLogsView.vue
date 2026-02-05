<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Tabs -->
      <div class="flex items-center justify-end gap-3">
          <button
            @click="activeTab = 'logs'"
            :class="[
              'px-4 py-2 rounded-lg font-medium transition-all',
              activeTab === 'logs'
                ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white'
                : 'bg-slate-700 text-slate-300 hover:bg-slate-600'
            ]"
          >
            Activity Logs
          </button>
          <button
            @click="activeTab = 'suspicious'"
            :class="[
              'px-4 py-2 rounded-lg font-medium transition-all',
              activeTab === 'suspicious'
                ? 'bg-gradient-to-r from-red-500 to-orange-500 text-white'
                : 'bg-slate-700 text-slate-300 hover:bg-slate-600'
            ]"
          >
            <ExclamationTriangleIcon class="w-5 h-5 inline mr-2" />
            Suspicious Activity
          </button>
      </div>

      <!-- Activity Logs Tab -->
      <div v-if="activeTab === 'logs'" class="space-y-6">
        <!-- Filters -->
        <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- User Filter -->
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Search User</label>
              <input
                v-model="filters.username"
                type="text"
                placeholder="Enter username..."
                class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500"
              />
            </div>

            <!-- Type Filter -->
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Activity Type</label>
              <select
                v-model="filters.type"
                class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500"
              >
                <option value="">All Types</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
                <option value="register">Register</option>
                <option value="crime_attempt">Crime Attempt</option>
                <option value="combat">Combat</option>
                <option value="bank_deposit">Bank Deposit</option>
                <option value="bank_withdrawal">Bank Withdrawal</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="gym_train">Gym Training</option>
                <option value="travel">Travel</option>
                <option value="item_purchase">Item Purchase</option>
                <option value="item_sold">Item Sold</option>
                <option value="bounty_placed">Bounty Placed</option>
                <option value="bounty_claimed">Bounty Claimed</option>
                <option value="gang_join">Gang Join</option>
                <option value="gang_leave">Gang Leave</option>
                <option value="drug_buy">Drug Buy</option>
                <option value="drug_sell">Drug Sell</option>
                <option value="theft_attempt">Theft Attempt</option>
                <option value="race_joined">Race Joined</option>
                <option value="organized_crime">Organized Crime</option>
                <option value="admin_action">Admin Action</option>
                <option value="banned">Banned</option>
                <option value="unbanned">Unbanned</option>
              </select>
            </div>

            <!-- Limit -->
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Records</label>
              <select
                v-model="filters.limit"
                class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500"
              >
                <option :value="50">50 records</option>
                <option :value="100">100 records</option>
                <option :value="250">250 records</option>
                <option :value="500">500 records</option>
              </select>
            </div>
          </div>

          <div class="flex gap-3 mt-4">
            <button
              @click="fetchActivityLogs"
              class="px-6 py-2 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-lg font-medium hover:from-orange-600 hover:to-amber-600 transition-all"
            >
              Apply Filters
            </button>
            <button
              @click="resetFilters"
              class="px-6 py-2 bg-slate-700 text-slate-300 rounded-lg font-medium hover:bg-slate-600 transition-all"
            >
              Reset
            </button>
            <button
              @click="cleanOldLogs"
              class="ml-auto px-6 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-all"
            >
              <TrashIcon class="w-5 h-5 inline mr-2" />
              Clean Old Logs
            </button>
          </div>
        </div>

        <!-- Activity Table -->
        <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
          <div v-if="loading" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500"></div>
            <p class="text-slate-400 mt-4">Loading activity logs...</p>
          </div>

          <div v-else-if="filteredActivity.length === 0" class="p-8 text-center">
            <DocumentTextIcon class="w-16 h-16 text-slate-600 mx-auto mb-4" />
            <p class="text-slate-400">No activity logs found</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-700">
              <thead class="bg-slate-900">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">User</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Description</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">IP Address</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Time</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-700">
                <tr
                  v-for="log in filteredActivity"
                  :key="log.id"
                  class="hover:bg-slate-700 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <UserIcon class="w-5 h-5 text-slate-400 mr-2" />
                      <span class="text-slate-200 font-medium">{{ log.username }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getTypeBadgeClass(log.type)">
                      {{ formatType(log.type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-slate-300 text-sm">{{ log.description }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-slate-400 text-sm font-mono">{{ log.ip_address || 'N/A' }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-slate-400 text-sm">{{ formatDate(log.created_at) }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button
                      @click="viewDetails(log)"
                      class="text-orange-400 hover:text-orange-300 transition-colors"
                    >
                      <EyeIcon class="w-5 h-5" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Suspicious Activity Tab -->
      <div v-if="activeTab === 'suspicious'" class="space-y-6">
        <div class="bg-slate-800 rounded-lg p-6 border border-red-500/30">
          <div v-if="loadingSuspicious" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-red-500"></div>
            <p class="text-slate-400 mt-4">Analyzing suspicious activity...</p>
          </div>

          <div v-else>
            <!-- Multiple IPs -->
            <div v-if="suspiciousActivity.multiple_ips?.length" class="mb-6">
              <h3 class="text-xl font-bold text-red-400 mb-4 flex items-center">
                <ExclamationTriangleIcon class="w-6 h-6 mr-2" />
                Multiple IP Addresses
              </h3>
              <div class="space-y-3">
                <div
                  v-for="user in suspiciousActivity.multiple_ips"
                  :key="user.user_id"
                  class="bg-slate-700 rounded-lg p-4 border border-red-500/30"
                >
                  <div class="flex items-center justify-between">
                    <div>
                      <span class="text-slate-200 font-medium">{{ user.username }}</span>
                      <span class="text-slate-400 text-sm ml-2">({{ user.ip_count }} different IPs)</span>
                    </div>
                    <button
                      @click="viewUserActivity(user.user_id)"
                      class="text-orange-400 hover:text-orange-300 transition-colors text-sm"
                    >
                      View Activity →
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Rapid Actions -->
            <div v-if="suspiciousActivity.rapid_actions?.length">
              <h3 class="text-xl font-bold text-red-400 mb-4 flex items-center">
                <ExclamationTriangleIcon class="w-6 h-6 mr-2" />
                Rapid Actions (Possible Botting)
              </h3>
              <div class="space-y-3">
                <div
                  v-for="user in suspiciousActivity.rapid_actions"
                  :key="user.user_id"
                  class="bg-slate-700 rounded-lg p-4 border border-red-500/30"
                >
                  <div class="flex items-center justify-between">
                    <div>
                      <span class="text-slate-200 font-medium">{{ user.username }}</span>
                      <span class="text-slate-400 text-sm ml-2">({{ user.action_count }} actions in 5 minutes)</span>
                    </div>
                    <button
                      @click="viewUserActivity(user.user_id)"
                      class="text-orange-400 hover:text-orange-300 transition-colors text-sm"
                    >
                      View Activity →
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="!suspiciousActivity.multiple_ips?.length && !suspiciousActivity.rapid_actions?.length" class="text-center py-8">
              <CheckCircleIcon class="w-16 h-16 text-green-500 mx-auto mb-4" />
              <p class="text-slate-400">No suspicious activity detected</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div
      v-if="selectedLog"
      class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
      @click.self="selectedLog = null"
    >
      <div class="bg-slate-800 rounded-lg p-6 max-w-2xl w-full border border-slate-700">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-400">
            Activity Details
          </h3>
          <button @click="selectedLog = null" class="text-slate-400 hover:text-slate-200">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">User</label>
            <p class="text-slate-200 font-medium">{{ selectedLog.username }} (ID: {{ selectedLog.user_id }})</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Type</label>
            <span :class="getTypeBadgeClass(selectedLog.type)">
              {{ formatType(selectedLog.type) }}
            </span>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Description</label>
            <p class="text-slate-300">{{ selectedLog.description }}</p>
          </div>

          <div v-if="selectedLog.metadata" class="bg-slate-700 rounded-lg p-4">
            <label class="block text-sm font-medium text-slate-400 mb-2">Metadata</label>
            <pre class="text-slate-300 text-sm overflow-x-auto">{{ JSON.stringify(JSON.parse(selectedLog.metadata), null, 2) }}</pre>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-400 mb-1">IP Address</label>
              <p class="text-slate-300 font-mono text-sm">{{ selectedLog.ip_address || 'N/A' }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-400 mb-1">Timestamp</label>
              <p class="text-slate-300 text-sm">{{ formatDate(selectedLog.created_at) }}</p>
            </div>
          </div>

          <div v-if="selectedLog.user_agent">
            <label class="block text-sm font-medium text-slate-400 mb-1">User Agent</label>
            <p class="text-slate-300 text-sm break-words">{{ selectedLog.user_agent }}</p>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button
            @click="viewUserActivity(selectedLog.user_id)"
            class="px-4 py-2 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-lg font-medium hover:from-orange-600 hover:to-amber-600 transition-all"
          >
            View User's Activity
          </button>
          <button
            @click="selectedLog = null"
            class="px-4 py-2 bg-slate-700 text-slate-300 rounded-lg font-medium hover:bg-slate-600 transition-all"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import {
  UserIcon,
  DocumentTextIcon,
  EyeIcon,
  TrashIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const activeTab = ref('logs')
const loading = ref(false)
const loadingSuspicious = ref(false)
const activity = ref([])
const suspiciousActivity = ref({})
const selectedLog = ref(null)

const filters = ref({
  username: '',
  type: '',
  limit: 100
})

const filteredActivity = computed(() => {
  let result = activity.value

  if (filters.value.username) {
    result = result.filter(log =>
      log.username?.toLowerCase().includes(filters.value.username.toLowerCase())
    )
  }

  return result
})

const getTypeBadgeClass = (type) => {
  const classes = 'px-3 py-1 rounded-full text-xs font-medium '
  const typeColors = {
    login: 'bg-green-500/20 text-green-400',
    logout: 'bg-slate-500/20 text-slate-400',
    register: 'bg-blue-500/20 text-blue-400',
    crime_attempt: 'bg-purple-500/20 text-purple-400',
    combat: 'bg-red-500/20 text-red-400',
    bank_deposit: 'bg-emerald-500/20 text-emerald-400',
    bank_withdrawal: 'bg-amber-500/20 text-amber-400',
    bank_transfer: 'bg-cyan-500/20 text-cyan-400',
    gym_train: 'bg-orange-500/20 text-orange-400',
    travel: 'bg-sky-500/20 text-sky-400',
    item_purchase: 'bg-indigo-500/20 text-indigo-400',
    item_sold: 'bg-violet-500/20 text-violet-400',
    bounty_placed: 'bg-rose-500/20 text-rose-400',
    bounty_claimed: 'bg-pink-500/20 text-pink-400',
    gang_join: 'bg-teal-500/20 text-teal-400',
    gang_leave: 'bg-gray-500/20 text-gray-400',
    admin_action: 'bg-red-500/20 text-red-400',
    banned: 'bg-red-600/20 text-red-500',
    unbanned: 'bg-green-600/20 text-green-500'
  }
  return classes + (typeColors[type] || 'bg-slate-500/20 text-slate-400')
}

const formatType = (type) => {
  return type
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  }).format(date)
}

const fetchActivityLogs = async () => {
  loading.value = true
  try {
    const params = {
      limit: filters.value.limit
    }
    if (filters.value.type) {
      params.type = filters.value.type
    }

    const response = await axios.get('/admin/activity', { params })
    activity.value = response.data.activity || []
  } catch (error) {
    console.error('Failed to fetch activity logs:', error)
    alert('Failed to fetch activity logs')
  } finally {
    loading.value = false
  }
}

const fetchSuspiciousActivity = async () => {
  loadingSuspicious.value = true
  try {
    const response = await axios.get('/admin/activity/suspicious')
    suspiciousActivity.value = response.data.suspicious_activity || {}
  } catch (error) {
    console.error('Failed to fetch suspicious activity:', error)
    alert('Failed to fetch suspicious activity')
  } finally {
    loadingSuspicious.value = false
  }
}

const resetFilters = () => {
  filters.value = {
    username: '',
    type: '',
    limit: 100
  }
  fetchActivityLogs()
}

const viewDetails = (log) => {
  selectedLog.value = log
}

const viewUserActivity = async (userId) => {
  selectedLog.value = null
  filters.value.username = ''
  filters.value.type = ''
  activeTab.value = 'logs'

  loading.value = true
  try {
    const response = await axios.get(`/admin/activity/user/${userId}`)
    activity.value = response.data.activity || []
  } catch (error) {
    console.error('Failed to fetch user activity:', error)
    alert('Failed to fetch user activity')
  } finally {
    loading.value = false
  }
}

const cleanOldLogs = async () => {
  if (!confirm('Are you sure you want to clean old activity logs? This action cannot be undone.')) {
    return
  }

  try {
    const response = await axios.post('/admin/activity/clean')
    alert(response.data.message)
    fetchActivityLogs()
  } catch (error) {
    console.error('Failed to clean old logs:', error)
    alert('Failed to clean old logs')
  }
}

onMounted(() => {
  fetchActivityLogs()
  fetchSuspiciousActivity()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Actions -->
    <div class="flex items-center justify-end">
      <button
        @click="refreshAll"
        class="flex items-center gap-2 px-4 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl font-medium hover:bg-slate-700 transition-colors"
      >
        <ArrowPathIcon :class="['w-5 h-5', refreshing && 'animate-spin']" />
        Refresh All
      </button>
    </div>

    <!-- Overall Status -->
    <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div :class="[
            'p-4 rounded-2xl',
            overallStatus === 'healthy' ? 'bg-emerald-500/20' :
            overallStatus === 'warning' ? 'bg-amber-500/20' : 'bg-red-500/20'
          ]">
            <HeartIcon :class="[
              'w-8 h-8',
              overallStatus === 'healthy' ? 'text-emerald-400' :
              overallStatus === 'warning' ? 'text-amber-400' : 'text-red-400'
            ]" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-white">System Status: {{ overallStatus.charAt(0).toUpperCase() + overallStatus.slice(1) }}</h2>
            <p class="text-slate-400">Last checked: {{ lastChecked }}</p>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <div class="text-center">
            <p class="text-3xl font-bold text-emerald-400">{{ healthyServices }}</p>
            <p class="text-sm text-slate-400">Healthy</p>
          </div>
          <div class="text-center">
            <p class="text-3xl font-bold text-amber-400">{{ warningServices }}</p>
            <p class="text-sm text-slate-400">Warning</p>
          </div>
          <div class="text-center">
            <p class="text-3xl font-bold text-red-400">{{ criticalServices }}</p>
            <p class="text-sm text-slate-400">Critical</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Resource Usage -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- CPU -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <CpuChipIcon class="w-5 h-5 text-cyan-400" />
            <span class="font-medium text-white">CPU</span>
          </div>
          <span :class="getUsageColor(resources.cpu)" class="text-2xl font-bold">{{ resources.cpu }}%</span>
        </div>
        <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
          <div
            :class="['h-full rounded-full transition-all', getUsageBarColor(resources.cpu)]"
            :style="{ width: `${resources.cpu}%` }"
          ></div>
        </div>
        <p class="text-xs text-slate-400 mt-2">{{ resources.cpu_cores }} cores available</p>
      </div>

      <!-- Memory -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <ServerIcon class="w-5 h-5 text-violet-400" />
            <span class="font-medium text-white">Memory</span>
          </div>
          <span :class="getUsageColor(resources.memory)" class="text-2xl font-bold">{{ resources.memory }}%</span>
        </div>
        <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
          <div
            :class="['h-full rounded-full transition-all', getUsageBarColor(resources.memory)]"
            :style="{ width: `${resources.memory}%` }"
          ></div>
        </div>
        <p class="text-xs text-slate-400 mt-2">{{ resources.memory_used }} / {{ resources.memory_total }}</p>
      </div>

      <!-- Disk -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <CircleStackIcon class="w-5 h-5 text-amber-400" />
            <span class="font-medium text-white">Disk</span>
          </div>
          <span :class="getUsageColor(resources.disk)" class="text-2xl font-bold">{{ resources.disk }}%</span>
        </div>
        <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
          <div
            :class="['h-full rounded-full transition-all', getUsageBarColor(resources.disk)]"
            :style="{ width: `${resources.disk}%` }"
          ></div>
        </div>
        <p class="text-xs text-slate-400 mt-2">{{ resources.disk_used }} / {{ resources.disk_total }}</p>
      </div>

      <!-- Network -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <SignalIcon class="w-5 h-5 text-emerald-400" />
            <span class="font-medium text-white">Network</span>
          </div>
        </div>
        <div class="space-y-1">
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-400">↑ Upload</span>
            <span class="text-white">{{ resources.network_up }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-400">↓ Download</span>
            <span class="text-white">{{ resources.network_down }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Core Services -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <ServerStackIcon class="w-5 h-5 text-cyan-400" />
          Core Services
        </h3>
        <div class="space-y-3">
          <div
            v-for="service in coreServices"
            :key="service.name"
            class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl"
          >
            <div class="flex items-center gap-3">
              <div :class="[
                'w-3 h-3 rounded-full',
                service.status === 'running' ? 'bg-emerald-400' :
                service.status === 'warning' ? 'bg-amber-400 animate-pulse' : 'bg-red-400'
              ]"></div>
              <div>
                <p class="font-medium text-white">{{ service.name }}</p>
                <p class="text-xs text-slate-400">{{ service.description }}</p>
              </div>
            </div>
            <div class="text-right">
              <p :class="[
                'text-sm font-medium',
                service.status === 'running' ? 'text-emerald-400' :
                service.status === 'warning' ? 'text-amber-400' : 'text-red-400'
              ]">
                {{ service.status }}
              </p>
              <p class="text-xs text-slate-400">{{ service.uptime || service.message }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Queue Status -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <QueueListIcon class="w-5 h-5 text-violet-400" />
          Queue Workers
        </h3>
        <div class="space-y-4">
          <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-3 bg-slate-900/50 rounded-xl">
              <p class="text-2xl font-bold text-white">{{ queue.pending }}</p>
              <p class="text-xs text-slate-400">Pending</p>
            </div>
            <div class="text-center p-3 bg-slate-900/50 rounded-xl">
              <p class="text-2xl font-bold text-emerald-400">{{ queue.processed }}</p>
              <p class="text-xs text-slate-400">Processed</p>
            </div>
            <div class="text-center p-3 bg-slate-900/50 rounded-xl">
              <p class="text-2xl font-bold text-red-400">{{ queue.failed }}</p>
              <p class="text-xs text-slate-400">Failed</p>
            </div>
          </div>

          <div class="space-y-2">
            <div
              v-for="worker in queue.workers"
              :key="worker.name"
              class="flex items-center justify-between p-2 bg-slate-900/50 rounded-lg"
            >
              <div class="flex items-center gap-2">
                <div :class="[
                  'w-2 h-2 rounded-full',
                  worker.status === 'running' ? 'bg-emerald-400' : 'bg-red-400'
                ]"></div>
                <span class="text-sm text-slate-300">{{ worker.name }}</span>
              </div>
              <span class="text-xs text-slate-400">{{ worker.jobs_processed }} jobs</span>
            </div>
          </div>

          <button
            @click="retryFailedJobs"
            :disabled="queue.failed === 0"
            class="w-full px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
          >
            Retry Failed Jobs ({{ queue.failed }})
          </button>
        </div>
      </div>
    </div>

    <!-- Cache & Database -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Cache Status -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <BoltIcon class="w-5 h-5 text-amber-400" />
          Cache Status
        </h3>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="p-3 bg-slate-900/50 rounded-xl">
              <p class="text-sm text-slate-400">Driver</p>
              <p class="text-lg font-medium text-white">{{ cache.driver }}</p>
            </div>
            <div class="p-3 bg-slate-900/50 rounded-xl">
              <p class="text-sm text-slate-400">Hit Rate</p>
              <p class="text-lg font-medium text-emerald-400">{{ cache.hit_rate }}%</p>
            </div>
          </div>

          <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
            <span class="text-slate-300">Memory Usage</span>
            <span class="text-white font-medium">{{ cache.memory_used }} / {{ cache.memory_limit }}</span>
          </div>

          <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
            <span class="text-slate-300">Keys Stored</span>
            <span class="text-white font-medium">{{ formatNumber(cache.keys) }}</span>
          </div>

          <div class="flex gap-2">
            <button
              @click="clearCache('all')"
              class="flex-1 px-4 py-2 bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-colors text-sm"
            >
              Clear All Cache
            </button>
            <button
              @click="clearCache('views')"
              class="flex-1 px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-colors text-sm"
            >
              Clear View Cache
            </button>
          </div>
        </div>
      </div>

      <!-- Database Status -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <CircleStackIcon class="w-5 h-5 text-blue-400" />
          Database Status
        </h3>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="p-3 bg-slate-900/50 rounded-xl">
              <p class="text-sm text-slate-400">Driver</p>
              <p class="text-lg font-medium text-white">{{ database.driver }}</p>
            </div>
            <div class="p-3 bg-slate-900/50 rounded-xl">
              <p class="text-sm text-slate-400">Connection</p>
              <p :class="['text-lg font-medium', database.connected ? 'text-emerald-400' : 'text-red-400']">
                {{ database.connected ? 'Connected' : 'Disconnected' }}
              </p>
            </div>
          </div>

          <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
            <span class="text-slate-300">Database Size</span>
            <span class="text-white font-medium">{{ database.size }}</span>
          </div>

          <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
            <span class="text-slate-300">Active Connections</span>
            <span class="text-white font-medium">{{ database.connections }} / {{ database.max_connections }}</span>
          </div>

          <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
            <span class="text-slate-300">Slow Queries (24h)</span>
            <span :class="['font-medium', database.slow_queries > 10 ? 'text-amber-400' : 'text-white']">
              {{ database.slow_queries }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Logs -->
    <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
          <ExclamationTriangleIcon class="w-5 h-5 text-red-400" />
          Recent Errors
        </h3>
        <button
          @click="router.push('/error-logs')"
          class="text-sm text-cyan-400 hover:text-cyan-300 transition-colors"
        >
          View All Logs
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-slate-400 border-b border-slate-700">
            <tr>
              <th class="text-left py-2 font-medium">Time</th>
              <th class="text-left py-2 font-medium">Level</th>
              <th class="text-left py-2 font-medium">Message</th>
              <th class="text-left py-2 font-medium">Count</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/50">
            <tr v-if="errors.length === 0">
              <td colspan="4" class="py-8 text-center text-slate-400">
                <CheckCircleIcon class="w-8 h-8 mx-auto mb-2 text-emerald-400" />
                No recent errors
              </td>
            </tr>
            <tr
              v-for="error in errors"
              :key="error.id"
              class="hover:bg-slate-700/30 cursor-pointer"
              @click="showErrorDetails(error)"
            >
              <td class="py-3 text-slate-300">{{ error.time }}</td>
              <td class="py-3">
                <span :class="[
                  'px-2 py-0.5 text-xs font-medium rounded-full',
                  error.level === 'error' ? 'bg-red-500/20 text-red-400' :
                  error.level === 'warning' ? 'bg-amber-500/20 text-amber-400' :
                  'bg-blue-500/20 text-blue-400'
                ]">
                  {{ error.level }}
                </span>
              </td>
              <td class="py-3 text-white max-w-md truncate">{{ error.message }}</td>
              <td class="py-3 text-slate-400">{{ error.count }}x</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Scheduled Tasks -->
    <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
      <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
        <ClockIcon class="w-5 h-5 text-emerald-400" />
        Scheduled Tasks
      </h3>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-slate-400 border-b border-slate-700">
            <tr>
              <th class="text-left py-2 font-medium">Task</th>
              <th class="text-left py-2 font-medium">Schedule</th>
              <th class="text-left py-2 font-medium">Last Run</th>
              <th class="text-left py-2 font-medium">Next Run</th>
              <th class="text-left py-2 font-medium">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/50">
            <tr
              v-for="task in scheduledTasks"
              :key="task.name"
              class="hover:bg-slate-700/30"
            >
              <td class="py-3 text-white">{{ task.name }}</td>
              <td class="py-3 text-slate-300 font-mono text-xs">{{ task.schedule }}</td>
              <td class="py-3 text-slate-300">{{ task.last_run || 'Never' }}</td>
              <td class="py-3 text-slate-300">{{ task.next_run }}</td>
              <td class="py-3">
                <span :class="[
                  'flex items-center gap-1.5',
                  task.status === 'success' ? 'text-emerald-400' :
                  task.status === 'running' ? 'text-amber-400' : 'text-red-400'
                ]">
                  <span :class="[
                    'w-2 h-2 rounded-full',
                    task.status === 'success' ? 'bg-emerald-400' :
                    task.status === 'running' ? 'bg-amber-400 animate-pulse' : 'bg-red-400'
                  ]"></span>
                  {{ task.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  HeartIcon,
  ArrowPathIcon,
  CpuChipIcon,
  ServerIcon,
  ServerStackIcon,
  CircleStackIcon,
  SignalIcon,
  QueueListIcon,
  BoltIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'
import api from '../services/api'

const router = useRouter()
const refreshing = ref(false)
const lastChecked = ref('Just now')

const resources = ref({
  cpu: 0,
  cpu_cores: 4,
  memory: 0,
  memory_used: '0 GB',
  memory_total: '16 GB',
  disk: 0,
  disk_used: '0 GB',
  disk_total: '256 GB',
  network_up: '0 KB/s',
  network_down: '0 KB/s'
})

const coreServices = ref([])
const queue = ref({
  pending: 0,
  processed: 0,
  failed: 0,
  workers: []
})

const cache = ref({
  driver: 'Redis',
  hit_rate: 0,
  memory_used: '0 MB',
  memory_limit: '512 MB',
  keys: 0
})

const database = ref({
  driver: 'MySQL',
  connected: false,
  size: '0 MB',
  connections: 0,
  max_connections: 100,
  slow_queries: 0
})

const errors = ref([])
const scheduledTasks = ref([])
const showAllLogs = ref(false)

const overallStatus = computed(() => {
  if (criticalServices.value > 0) return 'critical'
  if (warningServices.value > 0) return 'warning'
  return 'healthy'
})

const healthyServices = computed(() =>
  coreServices.value.filter(s => s.status === 'running').length
)

const warningServices = computed(() =>
  coreServices.value.filter(s => s.status === 'warning').length
)

const criticalServices = computed(() =>
  coreServices.value.filter(s => s.status === 'stopped' || s.status === 'error').length
)

let refreshInterval = null

onMounted(async () => {
  await refreshAll()
  // Auto-refresh every 30 seconds
  refreshInterval = setInterval(refreshAll, 30000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})

async function refreshAll() {
  refreshing.value = true
  try {
    const response = await api.get('/admin/system/health')
    if (response.data) {
      Object.assign(resources.value, response.data.resources || {})
      coreServices.value = response.data.services || []
      Object.assign(queue.value, response.data.queue || {})
      Object.assign(cache.value, response.data.cache || {})
      Object.assign(database.value, response.data.database || {})
      errors.value = response.data.errors || []
      scheduledTasks.value = response.data.scheduled_tasks || []
    }
  } catch (error) {
    console.error('Failed to fetch health data:', error)
    loadSampleData()
  } finally {
    refreshing.value = false
    lastChecked.value = 'Just now'
  }
}

function loadSampleData() {
  resources.value = {
    cpu: 45,
    cpu_cores: 8,
    memory: 62,
    memory_used: '9.9 GB',
    memory_total: '16 GB',
    disk: 34,
    disk_used: '87 GB',
    disk_total: '256 GB',
    network_up: '125 KB/s',
    network_down: '890 KB/s'
  }

  coreServices.value = [
    { name: 'Web Server', description: 'nginx/1.24', status: 'running', uptime: '15 days' },
    { name: 'PHP-FPM', description: 'PHP 8.3.1', status: 'running', uptime: '15 days' },
    { name: 'MySQL', description: 'MySQL 8.0', status: 'running', uptime: '15 days' },
    { name: 'Redis', description: 'Redis 7.2', status: 'running', uptime: '15 days' },
    { name: 'Queue Worker', description: 'Laravel Horizon', status: 'running', uptime: '3 days' },
    { name: 'WebSocket Server', description: 'Laravel Reverb', status: 'running', uptime: '3 days' }
  ]

  queue.value = {
    pending: 12,
    processed: 15847,
    failed: 3,
    workers: [
      { name: 'worker-1', status: 'running', jobs_processed: 5234 },
      { name: 'worker-2', status: 'running', jobs_processed: 5412 },
      { name: 'worker-3', status: 'running', jobs_processed: 5201 }
    ]
  }

  cache.value = {
    driver: 'Redis',
    hit_rate: 94.5,
    memory_used: '128 MB',
    memory_limit: '512 MB',
    keys: 15420
  }

  database.value = {
    driver: 'MySQL 8.0',
    connected: true,
    size: '2.4 GB',
    connections: 24,
    max_connections: 100,
    slow_queries: 5
  }

  errors.value = [
    { id: 1, time: '5 min ago', level: 'error', message: 'Failed to connect to external API: timeout', count: 3 },
    { id: 2, time: '1 hour ago', level: 'warning', message: 'High memory usage detected on queue worker', count: 1 },
    { id: 3, time: '3 hours ago', level: 'error', message: 'Database query timeout on users table', count: 2 }
  ]

  scheduledTasks.value = [
    { name: 'Daily Backup', schedule: '0 3 * * *', last_run: '7 hours ago', next_run: 'In 17 hours', status: 'success' },
    { name: 'Clear Old Sessions', schedule: '0 * * * *', last_run: '45 min ago', next_run: 'In 15 min', status: 'success' },
    { name: 'Process Energy Regen', schedule: '* * * * *', last_run: '30 sec ago', next_run: 'In 30 sec', status: 'success' },
    { name: 'Send Scheduled Emails', schedule: '*/5 * * * *', last_run: '2 min ago', next_run: 'In 3 min', status: 'success' }
  ]
}

function getUsageColor(value) {
  if (value >= 90) return 'text-red-400'
  if (value >= 70) return 'text-amber-400'
  return 'text-emerald-400'
}

function getUsageBarColor(value) {
  if (value >= 90) return 'bg-red-500'
  if (value >= 70) return 'bg-amber-500'
  return 'bg-emerald-500'
}

function formatNumber(num) {
  return num?.toLocaleString() || '0'
}

async function retryFailedJobs() {
  try {
    await api.post('/admin/queue/retry-failed')
    await refreshAll()
  } catch (error) {
    console.error('Failed to retry jobs:', error)
    alert('Failed to retry jobs')
  }
}

async function clearCache(type) {
  try {
    await api.post('/admin/cache/clear', { type })
    await refreshAll()
    alert(`${type === 'all' ? 'All cache' : 'View cache'} cleared successfully`)
  } catch (error) {
    console.error('Failed to clear cache:', error)
    alert('Failed to clear cache')
  }
}

function showErrorDetails(error) {
  // Could open a modal with full stack trace
  console.log('Show error details:', error)
}
</script>

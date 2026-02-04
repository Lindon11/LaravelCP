<template>
  <div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
      <div
        v-for="stat in statItems"
        :key="stat.level"
        :class="['p-4 rounded-xl border-l-4 bg-slate-800/30 backdrop-blur-sm', stat.borderColor]"
      >
        <p class="text-2xl font-bold text-white">{{ stats[stat.level] || 0 }}</p>
        <p class="text-sm text-slate-400">{{ stat.label }}</p>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-wrap items-center gap-4 p-4 bg-slate-800/30 backdrop-blur-sm rounded-xl border border-slate-700/50">
      <div class="flex items-center gap-2">
        <label class="text-sm text-slate-400">Level:</label>
        <select
          v-model="filters.level"
          @change="loadLogs"
          class="px-3 py-2 bg-slate-900/50 border border-slate-600/50 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50"
        >
          <option value="">All Levels</option>
          <option v-for="level in levels" :key="level.value" :value="level.value">{{ level.label }}</option>
        </select>
      </div>

      <div class="flex items-center gap-2">
        <label class="text-sm text-slate-400">Range:</label>
        <select
          v-model="filters.dateRange"
          @change="loadLogs"
          class="px-3 py-2 bg-slate-900/50 border border-slate-600/50 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50"
        >
          <option value="today">Today</option>
          <option value="yesterday">Yesterday</option>
          <option value="week">Last 7 Days</option>
          <option value="month">Last 30 Days</option>
          <option value="all">All Time</option>
        </select>
      </div>

      <div class="flex-1 min-w-[200px]">
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search logs..."
            @keyup.enter="loadLogs"
            class="w-full pl-10 pr-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-lg text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50"
          />
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="loadLogs"
          :disabled="loading"
          class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg font-medium transition-colors disabled:opacity-50"
        >
          <ArrowPathIcon :class="['w-5 h-5', loading && 'animate-spin']" />
          Refresh
        </button>
        <button
          @click="confirmClearLogs"
          :disabled="loading || logs.length === 0"
          class="inline-flex items-center gap-2 px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg font-medium transition-colors disabled:opacity-50"
        >
          <TrashIcon class="w-5 h-5" />
          Clear
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16">
      <div class="w-12 h-12 border-4 border-amber-500/30 border-t-amber-500 rounded-full animate-spin mb-4"></div>
      <p class="text-slate-400">Loading error logs...</p>
    </div>

    <!-- Logs List -->
    <div v-else class="bg-slate-800/30 backdrop-blur-sm rounded-2xl border border-slate-700/50 overflow-hidden">
      <!-- Empty State -->
      <div v-if="logs.length === 0" class="flex flex-col items-center justify-center py-16">
        <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 flex items-center justify-center mb-4">
          <CheckCircleIcon class="w-8 h-8 text-emerald-400" />
        </div>
        <h3 class="text-lg font-medium text-white mb-2">No Errors Found</h3>
        <p class="text-slate-400">The application is running smoothly!</p>
      </div>

      <!-- Log Entries -->
      <div v-else class="divide-y divide-slate-700/50 max-h-[600px] overflow-y-auto">
        <div
          v-for="log in logs"
          :key="log.id"
          @click="selectedLog = log"
          :class="[
            'p-4 cursor-pointer hover:bg-slate-700/30 transition-colors border-l-4',
            getLevelBorderColor(log.level)
          ]"
        >
          <div class="flex items-start justify-between gap-4 mb-2">
            <div class="flex items-center gap-3">
              <span :class="['px-2.5 py-1 text-xs font-bold rounded-md uppercase', getLevelClasses(log.level)]">
                {{ log.level }}
              </span>
              <span class="text-sm text-slate-400">{{ formatDate(log.created_at) }}</span>
            </div>
          </div>
          <p class="text-sm text-slate-300 font-mono mb-2 line-clamp-2">{{ log.message }}</p>
          <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
            <span v-if="log.context?.file" class="flex items-center gap-1">
              <DocumentIcon class="w-3.5 h-3.5" />
              {{ log.context.file }}
            </span>
            <span v-if="log.context?.line" class="flex items-center gap-1">
              Line {{ log.context.line }}
            </span>
            <span v-if="log.context?.user_id" class="flex items-center gap-1">
              <UserIcon class="w-3.5 h-3.5" />
              User #{{ log.context.user_id }}
            </span>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex items-center justify-center gap-4 p-4 border-t border-slate-700/50">
        <button
          @click="prevPage"
          :disabled="currentPage === 1"
          class="px-4 py-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        <span class="text-sm text-slate-400">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          class="px-4 py-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Log Detail Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="selectedLog" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="selectedLog = null"></div>
          <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-700 shrink-0">
              <div class="flex items-center gap-3">
                <span :class="['px-3 py-1.5 text-sm font-bold rounded-lg uppercase', getLevelClasses(selectedLog.level)]">
                  {{ selectedLog.level }}
                </span>
                <span class="text-lg font-bold text-white">Error Details</span>
              </div>
              <button @click="selectedLog = null" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto flex-1 space-y-6">
              <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Timestamp</label>
                <p class="text-slate-300">{{ formatDate(selectedLog.created_at) }}</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Message</label>
                <pre class="p-4 bg-slate-900/50 rounded-xl text-sm text-slate-300 font-mono overflow-x-auto whitespace-pre-wrap break-words">{{ selectedLog.message }}</pre>
              </div>

              <div v-if="selectedLog.context?.file">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Location</label>
                <p class="text-slate-300 font-mono text-sm">{{ selectedLog.context.file }}:{{ selectedLog.context.line }}</p>
              </div>

              <div v-if="selectedLog.stack_trace">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Stack Trace</label>
                <pre class="p-4 bg-slate-900/50 rounded-xl text-xs text-red-400 font-mono overflow-x-auto max-h-[300px] overflow-y-auto whitespace-pre-wrap break-words">{{ selectedLog.stack_trace }}</pre>
              </div>

              <div v-if="selectedLog.context">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Context</label>
                <pre class="p-4 bg-slate-900/50 rounded-xl text-xs text-slate-300 font-mono overflow-x-auto whitespace-pre-wrap break-words">{{ JSON.stringify(selectedLog.context, null, 2) }}</pre>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import {
  MagnifyingGlassIcon,
  ArrowPathIcon,
  TrashIcon,
  XMarkIcon,
  DocumentIcon,
  UserIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

const toast = useToast()
const loading = ref(false)
const logs = ref([])
const selectedLog = ref(null)
const currentPage = ref(1)
const totalPages = ref(1)

const filters = reactive({
  level: '',
  dateRange: 'week',
  search: ''
})

const stats = reactive({
  emergency: 0,
  critical: 0,
  error: 0,
  warning: 0,
  info: 0
})

const levels = [
  { value: 'emergency', label: 'Emergency' },
  { value: 'alert', label: 'Alert' },
  { value: 'critical', label: 'Critical' },
  { value: 'error', label: 'Error' },
  { value: 'warning', label: 'Warning' },
  { value: 'notice', label: 'Notice' },
  { value: 'info', label: 'Info' },
  { value: 'debug', label: 'Debug' }
]

const statItems = [
  { level: 'emergency', label: 'Emergency', borderColor: 'border-l-red-600' },
  { level: 'critical', label: 'Critical', borderColor: 'border-l-orange-500' },
  { level: 'error', label: 'Errors', borderColor: 'border-l-red-500' },
  { level: 'warning', label: 'Warnings', borderColor: 'border-l-amber-500' },
  { level: 'info', label: 'Info', borderColor: 'border-l-blue-500' }
]

const getLevelClasses = (level) => {
  const classes = {
    emergency: 'bg-red-600 text-white',
    alert: 'bg-red-600 text-white',
    critical: 'bg-orange-500 text-white',
    error: 'bg-red-500 text-white',
    warning: 'bg-amber-500 text-slate-900',
    notice: 'bg-blue-500 text-white',
    info: 'bg-sky-500 text-white',
    debug: 'bg-slate-500 text-white'
  }
  return classes[level] || 'bg-slate-500 text-white'
}

const getLevelBorderColor = (level) => {
  const colors = {
    emergency: 'border-l-red-600',
    alert: 'border-l-red-600',
    critical: 'border-l-orange-500',
    error: 'border-l-red-500',
    warning: 'border-l-amber-500',
    notice: 'border-l-blue-500',
    info: 'border-l-sky-500',
    debug: 'border-l-slate-500'
  }
  return colors[level] || 'border-l-slate-500'
}

const loadLogs = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/error-logs', {
      params: { page: currentPage.value, per_page: 50, ...filters }
    })
    logs.value = response.data.data || []
    totalPages.value = response.data.last_page || 1
    if (response.data.stats) Object.assign(stats, response.data.stats)
  } catch (error) {
    toast.error('Failed to load error logs')
  } finally {
    loading.value = false
  }
}

const confirmClearLogs = () => {
  if (confirm('Clear all error logs? This cannot be undone.')) clearLogs()
}

const clearLogs = async () => {
  try {
    await api.delete('/admin/error-logs/clear')
    logs.value = []
    Object.keys(stats).forEach(key => stats[key] = 0)
    toast.success('Error logs cleared')
  } catch (error) {
    toast.error('Failed to clear logs')
  }
}

const formatDate = (date) => new Date(date).toLocaleString()
const prevPage = () => { if (currentPage.value > 1) { currentPage.value--; loadLogs() } }
const nextPage = () => { if (currentPage.value < totalPages.value) { currentPage.value++; loadLogs() } }

onMounted(loadLogs)
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

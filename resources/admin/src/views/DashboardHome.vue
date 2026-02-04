<template>
  <div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
      <div v-for="stat in stats" :key="stat.label"
        class="relative overflow-hidden rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6 group hover:border-slate-600/50 transition-all"
      >
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm font-medium text-slate-400">{{ stat.label }}</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ stat.value }}</p>
            <div class="mt-2 flex items-center gap-1.5">
              <span :class="[
                'text-sm font-medium',
                stat.trend === 'up' ? 'text-emerald-400' : stat.trend === 'down' ? 'text-red-400' : 'text-slate-400'
              ]">
                {{ stat.change }}
              </span>
              <span class="text-xs text-slate-500">{{ stat.period }}</span>
            </div>
          </div>
          <div :class="[
            'p-3 rounded-xl',
            stat.color
          ]">
            <component :is="stat.icon" class="w-6 h-6 text-white" />
          </div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-r opacity-0 group-hover:opacity-10 transition-opacity"
          :class="stat.gradient" />
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Activity Chart -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-semibold text-white">Player Activity</h2>
          <select class="px-3 py-1.5 rounded-lg bg-slate-700/50 border border-slate-600/50 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/50">
            <option>Last 7 days</option>
            <option>Last 30 days</option>
            <option>Last 90 days</option>
          </select>
        </div>
        <LineChart :labels="activityChart.labels" :datasets="activityChart.datasets" />
      </div>

      <!-- Crime Distribution -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-semibold text-white">Crime Distribution</h2>
          <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-medium">Live</span>
        </div>
        <DoughnutChart :labels="crimeChart.labels" :data="crimeChart.data" />
      </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- System Health -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h2 class="text-lg font-semibold text-white mb-4">System Health</h2>
        <div class="space-y-4">
          <div v-for="item in systemHealth" :key="item.label"
            class="flex items-center justify-between p-3 rounded-xl bg-slate-700/30"
          >
            <div class="flex items-center gap-3">
              <div :class="[
                'w-2.5 h-2.5 rounded-full',
                item.status === 'online' ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'
              ]" />
              <span class="text-sm text-slate-300">{{ item.label }}</span>
            </div>
            <span :class="[
              'text-xs font-medium px-2 py-1 rounded-lg',
              item.status === 'online'
                ? 'bg-emerald-500/20 text-emerald-400'
                : 'bg-red-500/20 text-red-400'
            ]">
              {{ item.status === 'online' ? 'Online' : 'Offline' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 gap-3">
          <button
            v-for="action in quickActions"
            :key="action.label"
            @click="action.handler"
            class="flex flex-col items-center gap-2 p-4 rounded-xl bg-slate-700/30 hover:bg-slate-700/50 border border-slate-600/30 hover:border-slate-500/50 transition-all group"
          >
            <component :is="action.icon" class="w-6 h-6 text-slate-400 group-hover:text-amber-400 transition-colors" />
            <span class="text-xs font-medium text-slate-300 group-hover:text-white transition-colors">{{ action.label }}</span>
          </button>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Recent Activity</h2>
        <div class="space-y-3">
          <div v-for="activity in recentActivity" :key="activity.id"
            class="flex items-start gap-3 p-3 rounded-xl bg-slate-700/30"
          >
            <div :class="[
              'p-2 rounded-lg flex-shrink-0',
              activity.color
            ]">
              <component :is="activity.icon" class="w-4 h-4 text-white" />
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm text-slate-300 truncate">{{ activity.message }}</p>
              <p class="text-xs text-slate-500 mt-0.5">{{ activity.time }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import LineChart from '@/components/charts/LineChart.vue'
import DoughnutChart from '@/components/charts/DoughnutChart.vue'
import {
  UsersIcon,
  BoltIcon,
  FireIcon,
  CurrencyDollarIcon,
  TrashIcon,
  MegaphoneIcon,
  ServerIcon,
  DocumentTextIcon,
  UserPlusIcon,
  ShieldCheckIcon,
  BanknotesIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const toast = useToast()

const stats = ref([
  {
    label: 'Total Users',
    value: '1,247',
    change: '+23',
    period: 'this week',
    trend: 'up',
    icon: UsersIcon,
    color: 'bg-blue-500',
    gradient: 'from-blue-500 to-cyan-500'
  },
  {
    label: 'Active Players',
    value: '89',
    change: 'Online',
    period: 'now',
    trend: 'neutral',
    icon: BoltIcon,
    color: 'bg-emerald-500',
    gradient: 'from-emerald-500 to-teal-500'
  },
  {
    label: 'Crimes Today',
    value: '3,421',
    change: '+12%',
    period: 'vs yesterday',
    trend: 'up',
    icon: FireIcon,
    color: 'bg-orange-500',
    gradient: 'from-orange-500 to-amber-500'
  },
  {
    label: 'Total Economy',
    value: '$15.4M',
    change: '+$2.1M',
    period: 'this month',
    trend: 'up',
    icon: CurrencyDollarIcon,
    color: 'bg-violet-500',
    gradient: 'from-violet-500 to-purple-500'
  }
])

const systemHealth = ref([
  { label: 'API Server', status: 'online' },
  { label: 'Database', status: 'online' },
  { label: 'Queue Worker', status: 'online' },
  { label: 'Cache (Redis)', status: 'online' }
])

const quickActions = ref([
  { label: 'Clear Cache', icon: TrashIcon, handler: () => clearCache() },
  { label: 'Announcement', icon: MegaphoneIcon, handler: () => router.push('/announcements') },
  { label: 'Backup DB', icon: ServerIcon, handler: () => backupDb() },
  { label: 'View Logs', icon: DocumentTextIcon, handler: () => router.push('/error-logs') }
])

const recentActivity = ref([
  { id: 1, message: 'New user registered: player_123', time: '2 min ago', icon: UserPlusIcon, color: 'bg-blue-500' },
  { id: 2, message: 'Admin updated game settings', time: '15 min ago', icon: ShieldCheckIcon, color: 'bg-amber-500' },
  { id: 3, message: 'Large transaction: $50,000', time: '1 hour ago', icon: BanknotesIcon, color: 'bg-emerald-500' }
])

const activityChart = ref({
  labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
  datasets: [
    {
      label: 'Active Users',
      data: [120, 145, 132, 178, 195, 210, 189],
      borderColor: '#10b981',
      backgroundColor: 'rgba(16, 185, 129, 0.1)'
    },
    {
      label: 'New Signups',
      data: [12, 19, 8, 15, 22, 28, 18],
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59, 130, 246, 0.1)'
    }
  ]
})

const crimeChart = ref({
  labels: ['Petty Theft', 'Grand Theft', 'Assault', 'Drug Deal', 'Robbery', 'Other'],
  data: [35, 25, 15, 12, 8, 5]
})

const clearCache = async () => {
  try {
    await api.post('/admin/cache/clear')
    toast.success('Cache cleared successfully!')
  } catch (error) {
    toast.error('Failed to clear cache')
  }
}

const backupDb = () => {
  toast.info('Database backup initiated...')
  setTimeout(() => {
    toast.success('Database backup completed!')
  }, 2000)
}

onMounted(async () => {
  try {
    const response = await api.get('/admin/stats')
    if (response.data) {
      // Update stat cards
      stats.value[0].value = response.data.totalUsers?.toLocaleString() || '0'
      stats.value[0].change = `+${response.data.newUsers || 0}`
      stats.value[1].value = response.data.activeUsers?.toString() || '0'
      stats.value[2].value = response.data.crimesToday?.toLocaleString() || '0'
      stats.value[2].change = `${response.data.crimesGrowth > 0 ? '+' : ''}${response.data.crimesGrowth}%`
      stats.value[2].trend = response.data.crimesGrowth > 0 ? 'up' : response.data.crimesGrowth < 0 ? 'down' : 'neutral'
      stats.value[3].value = `$${(response.data.totalMoney / 1000000).toFixed(1)}M`

      // Update activity chart
      if (response.data.activityChart) {
        activityChart.value = {
          labels: response.data.activityChart.labels || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [
            {
              label: 'Active Users',
              data: response.data.activityChart.activeUsers || [0, 0, 0, 0, 0, 0, 0],
              borderColor: '#10b981',
              backgroundColor: 'rgba(16, 185, 129, 0.1)'
            },
            {
              label: 'New Signups',
              data: response.data.activityChart.newSignups || [0, 0, 0, 0, 0, 0, 0],
              borderColor: '#3b82f6',
              backgroundColor: 'rgba(59, 130, 246, 0.1)'
            }
          ]
        }
      }

      // Update crime chart
      if (response.data.crimeChart) {
        crimeChart.value = {
          labels: response.data.crimeChart.labels || ['Petty Theft', 'Grand Theft', 'Assault', 'Drug Deal', 'Robbery', 'Other'],
          data: response.data.crimeChart.data || [0, 0, 0, 0, 0, 0]
        }
      }
    }
  } catch (error) {
    console.error('Failed to load dashboard stats:', error)
    // Use default mock data on error
  }
})
</script>

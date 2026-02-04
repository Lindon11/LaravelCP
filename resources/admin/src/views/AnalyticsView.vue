<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white flex items-center gap-3">
          <div class="p-3 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600">
            <ChartBarIcon class="w-6 h-6 text-white" />
          </div>
          Analytics Dashboard
        </h1>
        <p class="text-sm text-slate-400 mt-1">Monitor player activity, engagement metrics, and game economy</p>
      </div>
      <div class="flex items-center gap-3">
        <select
          v-model="dateRange"
          class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50"
        >
          <option value="7">Last 7 days</option>
          <option value="30">Last 30 days</option>
          <option value="90">Last 90 days</option>
          <option value="365">Last year</option>
        </select>
        <button
          @click="refreshData"
          class="p-2 bg-slate-800 border border-slate-700 rounded-xl text-white hover:bg-slate-700 transition-colors"
        >
          <ArrowPathIcon :class="['w-5 h-5', loading && 'animate-spin']" />
        </button>
      </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-400">Total Users</p>
            <p class="text-3xl font-bold text-white mt-1">{{ formatNumber(stats.total_users) }}</p>
            <p :class="[
              'text-sm mt-1 flex items-center gap-1',
              stats.users_change >= 0 ? 'text-emerald-400' : 'text-red-400'
            ]">
              <ArrowTrendingUpIcon v-if="stats.users_change >= 0" class="w-4 h-4" />
              <ArrowTrendingDownIcon v-else class="w-4 h-4" />
              {{ Math.abs(stats.users_change) }}% from last period
            </p>
          </div>
          <div class="p-3 rounded-xl bg-cyan-500/20">
            <UsersIcon class="w-6 h-6 text-cyan-400" />
          </div>
        </div>
      </div>

      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-400">Active Today</p>
            <p class="text-3xl font-bold text-white mt-1">{{ formatNumber(stats.active_today) }}</p>
            <p class="text-sm mt-1 text-slate-400">
              {{ stats.active_percentage }}% of total users
            </p>
          </div>
          <div class="p-3 rounded-xl bg-emerald-500/20">
            <UserGroupIcon class="w-6 h-6 text-emerald-400" />
          </div>
        </div>
      </div>

      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-400">New Registrations</p>
            <p class="text-3xl font-bold text-white mt-1">{{ formatNumber(stats.new_registrations) }}</p>
            <p :class="[
              'text-sm mt-1 flex items-center gap-1',
              stats.registrations_change >= 0 ? 'text-emerald-400' : 'text-red-400'
            ]">
              <ArrowTrendingUpIcon v-if="stats.registrations_change >= 0" class="w-4 h-4" />
              <ArrowTrendingDownIcon v-else class="w-4 h-4" />
              {{ Math.abs(stats.registrations_change) }}% from last period
            </p>
          </div>
          <div class="p-3 rounded-xl bg-violet-500/20">
            <UserPlusIcon class="w-6 h-6 text-violet-400" />
          </div>
        </div>
      </div>

      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-400">Avg. Session Time</p>
            <p class="text-3xl font-bold text-white mt-1">{{ stats.avg_session_time }}</p>
            <p class="text-sm mt-1 text-slate-400">
              minutes per session
            </p>
          </div>
          <div class="p-3 rounded-xl bg-amber-500/20">
            <ClockIcon class="w-6 h-6 text-amber-400" />
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- User Activity Chart -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <ChartBarIcon class="w-5 h-5 text-cyan-400" />
          User Activity
        </h3>
        <div class="h-64 flex items-end justify-between gap-2">
          <div
            v-for="(day, index) in activityData"
            :key="index"
            class="flex-1 flex flex-col items-center gap-2"
          >
            <div
              class="w-full bg-gradient-to-t from-cyan-500 to-blue-500 rounded-t-lg transition-all hover:from-cyan-400 hover:to-blue-400"
              :style="{ height: `${(day.value / maxActivity) * 100}%` }"
              :title="`${day.label}: ${day.value} users`"
            ></div>
            <span class="text-xs text-slate-400">{{ day.label }}</span>
          </div>
        </div>
      </div>

      <!-- Economy Overview -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <CurrencyDollarIcon class="w-5 h-5 text-emerald-400" />
          Economy Overview
        </h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
            <span class="text-slate-300">Total Cash in Circulation</span>
            <span class="text-xl font-bold text-emerald-400">${{ formatNumber(economy.total_cash) }}</span>
          </div>
          <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
            <span class="text-slate-300">Total Bank Deposits</span>
            <span class="text-xl font-bold text-blue-400">${{ formatNumber(economy.total_bank) }}</span>
          </div>
          <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
            <span class="text-slate-300">Cash Generated Today</span>
            <span class="text-xl font-bold text-amber-400">${{ formatNumber(economy.generated_today) }}</span>
          </div>
          <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
            <span class="text-slate-300">Cash Spent Today</span>
            <span class="text-xl font-bold text-red-400">${{ formatNumber(economy.spent_today) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Activity Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Top Activities -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <FireIcon class="w-5 h-5 text-orange-400" />
          Top Activities
        </h3>
        <div class="space-y-3">
          <div
            v-for="activity in topActivities"
            :key="activity.name"
            class="flex items-center justify-between"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-slate-700 flex items-center justify-center text-sm">
                {{ activity.icon }}
              </div>
              <span class="text-slate-300">{{ activity.name }}</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-24 h-2 bg-slate-700 rounded-full overflow-hidden">
                <div
                  class="h-full bg-gradient-to-r from-orange-500 to-amber-500 rounded-full"
                  :style="{ width: `${activity.percentage}%` }"
                ></div>
              </div>
              <span class="text-sm text-slate-400 w-12 text-right">{{ formatNumber(activity.count) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Player Levels Distribution -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <ChartPieIcon class="w-5 h-5 text-violet-400" />
          Level Distribution
        </h3>
        <div class="space-y-3">
          <div
            v-for="level in levelDistribution"
            :key="level.range"
            class="flex items-center justify-between"
          >
            <span class="text-slate-300">{{ level.range }}</span>
            <div class="flex items-center gap-2">
              <div class="w-32 h-2 bg-slate-700 rounded-full overflow-hidden">
                <div
                  :class="['h-full rounded-full', level.color]"
                  :style="{ width: `${level.percentage}%` }"
                ></div>
              </div>
              <span class="text-sm text-slate-400 w-12 text-right">{{ level.percentage }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Events -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <BellIcon class="w-5 h-5 text-cyan-400" />
          Recent Events
        </h3>
        <div class="space-y-3">
          <div
            v-for="event in recentEvents"
            :key="event.id"
            class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-700/30 transition-colors"
          >
            <div :class="['p-1.5 rounded-lg', event.bgColor]">
              <component :is="event.icon" :class="['w-4 h-4', event.iconColor]" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-white truncate">{{ event.message }}</p>
              <p class="text-xs text-slate-400">{{ event.time }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Retention & Engagement -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Retention Cohorts -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <ArrowPathIcon class="w-5 h-5 text-emerald-400" />
          Retention by Cohort
        </h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-slate-400">
                <th class="text-left py-2">Cohort</th>
                <th class="text-center py-2">Day 1</th>
                <th class="text-center py-2">Day 7</th>
                <th class="text-center py-2">Day 30</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="cohort in retentionData" :key="cohort.week" class="border-t border-slate-700/50">
                <td class="py-2 text-slate-300">{{ cohort.week }}</td>
                <td class="py-2 text-center">
                  <span :class="getRetentionColor(cohort.day1)">{{ cohort.day1 }}%</span>
                </td>
                <td class="py-2 text-center">
                  <span :class="getRetentionColor(cohort.day7)">{{ cohort.day7 }}%</span>
                </td>
                <td class="py-2 text-center">
                  <span :class="getRetentionColor(cohort.day30)">{{ cohort.day30 }}%</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Peak Hours -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <ClockIcon class="w-5 h-5 text-amber-400" />
          Peak Activity Hours
        </h3>
        <div class="grid grid-cols-12 gap-1">
          <div
            v-for="hour in hourlyActivity"
            :key="hour.hour"
            class="flex flex-col items-center"
          >
            <div
              class="w-full rounded-lg transition-colors"
              :class="getHeatmapColor(hour.value)"
              :style="{ height: '60px' }"
              :title="`${hour.hour}:00 - ${hour.value} users`"
            ></div>
            <span class="text-xs text-slate-400 mt-1">{{ hour.hour }}</span>
          </div>
        </div>
        <div class="flex items-center justify-center gap-4 mt-4 text-xs text-slate-400">
          <span class="flex items-center gap-1">
            <div class="w-3 h-3 rounded bg-slate-700"></div> Low
          </span>
          <span class="flex items-center gap-1">
            <div class="w-3 h-3 rounded bg-cyan-700"></div> Medium
          </span>
          <span class="flex items-center gap-1">
            <div class="w-3 h-3 rounded bg-cyan-500"></div> High
          </span>
          <span class="flex items-center gap-1">
            <div class="w-3 h-3 rounded bg-cyan-300"></div> Peak
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  ChartBarIcon,
  ChartPieIcon,
  UsersIcon,
  UserGroupIcon,
  UserPlusIcon,
  ClockIcon,
  CurrencyDollarIcon,
  FireIcon,
  BellIcon,
  ArrowPathIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  TrophyIcon,
  ShieldCheckIcon,
  BanknotesIcon
} from '@heroicons/vue/24/outline'
import api from '../services/api'

const loading = ref(false)
const dateRange = ref('30')

const stats = ref({
  total_users: 0,
  users_change: 0,
  active_today: 0,
  active_percentage: 0,
  new_registrations: 0,
  registrations_change: 0,
  avg_session_time: 0
})

const economy = ref({
  total_cash: 0,
  total_bank: 0,
  generated_today: 0,
  spent_today: 0
})

const activityData = ref([])
const maxActivity = computed(() => Math.max(...activityData.value.map(d => d.value), 1))

const topActivities = ref([
  { name: 'Crimes', icon: '🔫', count: 15420, percentage: 100 },
  { name: 'Combat', icon: '⚔️', count: 12350, percentage: 80 },
  { name: 'Travel', icon: '✈️', count: 9840, percentage: 64 },
  { name: 'Jobs', icon: '💼', count: 7620, percentage: 49 },
  { name: 'Casino', icon: '🎰', count: 5430, percentage: 35 }
])

const levelDistribution = ref([
  { range: 'Level 1-10', percentage: 45, color: 'bg-emerald-500' },
  { range: 'Level 11-25', percentage: 30, color: 'bg-blue-500' },
  { range: 'Level 26-50', percentage: 15, color: 'bg-violet-500' },
  { range: 'Level 51-75', percentage: 7, color: 'bg-amber-500' },
  { range: 'Level 76+', percentage: 3, color: 'bg-red-500' }
])

const recentEvents = ref([
  { id: 1, message: 'New user registered: Player123', time: '2 min ago', icon: UserPlusIcon, bgColor: 'bg-emerald-500/20', iconColor: 'text-emerald-400' },
  { id: 2, message: 'Gang war started: Mafia vs Cartel', time: '15 min ago', icon: FireIcon, bgColor: 'bg-red-500/20', iconColor: 'text-red-400' },
  { id: 3, message: 'Achievement unlocked by 5 players', time: '32 min ago', icon: TrophyIcon, bgColor: 'bg-amber-500/20', iconColor: 'text-amber-400' },
  { id: 4, message: 'Large transaction: $1,000,000', time: '1 hour ago', icon: BanknotesIcon, bgColor: 'bg-emerald-500/20', iconColor: 'text-emerald-400' }
])

const retentionData = ref([
  { week: 'This Week', day1: 85, day7: 45, day30: 22 },
  { week: 'Last Week', day1: 82, day7: 42, day30: 20 },
  { week: '2 Weeks Ago', day1: 78, day7: 40, day30: 18 },
  { week: '3 Weeks Ago', day1: 80, day7: 38, day30: 17 }
])

const hourlyActivity = ref([])

onMounted(async () => {
  await refreshData()
})

async function refreshData() {
  loading.value = true
  try {
    const response = await api.get('/admin/stats', {
      params: { days: dateRange.value }
    })
    
    if (response.data) {
      Object.assign(stats.value, response.data.stats || {})
      Object.assign(economy.value, response.data.economy || {})
      
      if (response.data.activity_chart) {
        activityData.value = response.data.activity_chart
      } else {
        // Generate sample data
        generateSampleData()
      }
      
      if (response.data.hourly_activity) {
        hourlyActivity.value = response.data.hourly_activity
      } else {
        generateHourlyData()
      }
    }
  } catch (error) {
    console.error('Failed to load analytics:', error)
    generateSampleData()
    generateHourlyData()
  } finally {
    loading.value = false
  }
}

function generateSampleData() {
  const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
  activityData.value = days.map(day => ({
    label: day,
    value: Math.floor(Math.random() * 500) + 100
  }))
  
  stats.value = {
    total_users: 1250,
    users_change: 12.5,
    active_today: 342,
    active_percentage: 27,
    new_registrations: 45,
    registrations_change: 8.3,
    avg_session_time: 24
  }
  
  economy.value = {
    total_cash: 125000000,
    total_bank: 450000000,
    generated_today: 2500000,
    spent_today: 1800000
  }
}

function generateHourlyData() {
  hourlyActivity.value = Array.from({ length: 24 }, (_, i) => ({
    hour: i,
    value: Math.floor(Math.random() * 100)
  }))
}

function formatNumber(num) {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
  return num?.toLocaleString() || '0'
}

function getRetentionColor(value) {
  if (value >= 60) return 'text-emerald-400'
  if (value >= 40) return 'text-amber-400'
  if (value >= 20) return 'text-orange-400'
  return 'text-red-400'
}

function getHeatmapColor(value) {
  if (value >= 80) return 'bg-cyan-300'
  if (value >= 60) return 'bg-cyan-500'
  if (value >= 40) return 'bg-cyan-700'
  if (value >= 20) return 'bg-slate-600'
  return 'bg-slate-700'
}
</script>

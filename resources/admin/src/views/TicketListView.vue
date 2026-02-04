<template>
  <div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <button
        v-for="stat in statsCards"
        :key="stat.key"
        @click="setFilter('status', stat.filterValue)"
        :class="[
          'relative overflow-hidden rounded-2xl p-5 text-left transition-all group',
          filters.status === stat.filterValue
            ? 'bg-gradient-to-br ring-2 ring-offset-2 ring-offset-slate-900 ' + stat.activeRing + ' ' + stat.activeBg
            : 'bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 hover:border-slate-600/50'
        ]"
      >
        <div class="flex items-center justify-between">
          <div>
            <p :class="['text-3xl font-bold', filters.status === stat.filterValue ? 'text-white' : stat.textColor]">{{ stat.value }}</p>
            <p :class="['text-sm font-medium mt-1', filters.status === stat.filterValue ? 'text-white/80' : 'text-slate-400']">{{ stat.label }}</p>
          </div>
          <div :class="['p-3 rounded-xl', filters.status === stat.filterValue ? 'bg-white/20' : stat.iconBg]">
            <component :is="stat.icon" :class="['w-6 h-6', filters.status === stat.filterValue ? 'text-white' : stat.iconColor]" />
          </div>
        </div>
      </button>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center gap-4">
      <div class="relative flex-1 w-full lg:max-w-md">
        <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search tickets by subject or ID..."
          @input="debouncedSearch"
          class="w-full pl-12 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
        />
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <select
          v-model="filters.priority"
          @change="fetchTickets"
          class="px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all"
        >
          <option value="">All Priorities</option>
          <option value="urgent">🔴 Urgent</option>
          <option value="high">🟠 High</option>
          <option value="medium">🟡 Medium</option>
          <option value="low">🟢 Low</option>
        </select>

        <select
          v-model="filters.assigned"
          @change="handleAssignedFilter"
          class="px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all"
        >
          <option value="">All Tickets</option>
          <option value="unassigned">Unassigned</option>
          <option value="me">Assigned to Me</option>
          <option disabled>──────────</option>
          <option
            v-for="user in staffUsers"
            :key="user.id"
            :value="user.id"
          >
            {{ user.username }}
          </option>
        </select>

        <button
          v-if="hasActiveFilters"
          @click="clearFilters"
          class="inline-flex items-center gap-2 px-4 py-3 text-slate-400 hover:text-white transition-colors"
        >
          <XMarkIcon class="w-4 h-4" />
          Clear
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-12 h-12 border-4 border-amber-500/30 border-t-amber-500 rounded-full animate-spin mb-4"></div>
        <p class="text-slate-400">Loading tickets...</p>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="tickets.length === 0" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-700/30 flex items-center justify-center mb-4">
          <TicketIcon class="w-8 h-8 text-slate-500" />
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">No tickets found</h3>
        <p class="text-slate-400 text-center max-w-sm">{{ hasActiveFilters ? 'Try adjusting your filters' : 'All support tickets will appear here' }}</p>
      </div>
    </div>

    <!-- Tickets List -->
    <div v-else class="space-y-3">
      <TransitionGroup name="list">
        <div
          v-for="ticket in tickets"
          :key="ticket.id"
          @click="openTicket(ticket.id)"
          class="group relative bg-slate-800/50 hover:bg-slate-800 backdrop-blur-sm rounded-2xl border border-slate-700/50 hover:border-slate-600/50 p-5 cursor-pointer transition-all"
        >
          <!-- Priority Indicator -->
          <div
            :class="[
              'absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl',
              priorityColors[ticket.priority]?.bar || 'bg-slate-600'
            ]"
          />

          <div class="flex items-start gap-4 pl-3">
            <!-- Ticket Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-2">
                <span class="text-xs font-mono text-slate-500">#{{ ticket.id }}</span>
                <span :class="[
                  'inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium',
                  statusColors[ticket.status]?.badge || 'bg-slate-700 text-slate-300'
                ]">
                  {{ formatStatus(ticket.status) }}
                </span>
                <span :class="[
                  'inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium',
                  priorityColors[ticket.priority]?.badge || 'bg-slate-700 text-slate-300'
                ]">
                  {{ ticket.priority }}
                </span>
              </div>

              <h3 class="text-white font-medium group-hover:text-amber-400 transition-colors truncate mb-2">
                {{ ticket.subject }}
              </h3>

              <div class="flex flex-wrap items-center gap-4 text-sm text-slate-400">
                <span class="inline-flex items-center gap-1.5">
                  <UserIcon class="w-4 h-4" />
                  {{ ticket.user?.username || 'Unknown' }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                  <CalendarIcon class="w-4 h-4" />
                  {{ formatDate(ticket.updated_at) }}
                </span>
                <span v-if="ticket.messages_count" class="inline-flex items-center gap-1.5">
                  <ChatBubbleLeftRightIcon class="w-4 h-4" />
                  {{ ticket.messages_count }} messages
                </span>
                <span v-if="ticket.category" class="inline-flex items-center gap-1.5">
                  <TagIcon class="w-4 h-4" />
                  {{ ticket.category.name }}
                </span>
              </div>
            </div>

            <!-- Assignee -->
            <div class="flex items-center gap-3">
              <div v-if="ticket.assigned_user" class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-sm font-semibold">
                  {{ ticket.assigned_user.username?.charAt(0)?.toUpperCase() }}
                </div>
              </div>
              <div v-else class="flex items-center gap-2 text-slate-500">
                <div class="w-9 h-9 rounded-full bg-slate-700/50 border-2 border-dashed border-slate-600 flex items-center justify-center">
                  <UserPlusIcon class="w-4 h-4" />
                </div>
              </div>
              <ChevronRightIcon class="w-5 h-5 text-slate-600 group-hover:text-slate-400 transition-colors" />
            </div>
          </div>
        </div>
      </TransitionGroup>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex items-center justify-between">
      <p class="text-sm text-slate-400">
        Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to
        {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of
        {{ pagination.total }} tickets
      </p>
      <div class="flex items-center gap-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="p-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <ChevronLeftIcon class="w-5 h-5" />
        </button>
        <div class="flex items-center gap-1">
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'w-10 h-10 rounded-lg text-sm font-medium transition-colors',
              page === pagination.current_page
                ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white'
                : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700'
            ]"
          >
            {{ page }}
          </button>
        </div>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="p-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <ChevronRightIcon class="w-5 h-5" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import {
  MagnifyingGlassIcon,
  XMarkIcon,
  TicketIcon,
  UserIcon,
  UserPlusIcon,
  CalendarIcon,
  ChatBubbleLeftRightIcon,
  TagIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  InboxIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const tickets = ref([])
const loading = ref(false)
const stats = ref({ open: 0, waiting: 0, urgent: 0, closed: 0 })
const filters = ref({ search: '', status: '', priority: '', assigned: '' })
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
const staffUsers = ref([])

let searchTimeout = null

const statusColors = {
  open: { badge: 'bg-blue-500/20 text-blue-400' },
  waiting_response: { badge: 'bg-amber-500/20 text-amber-400' },
  answered: { badge: 'bg-emerald-500/20 text-emerald-400' },
  closed: { badge: 'bg-slate-600/50 text-slate-400' }
}

const priorityColors = {
  urgent: { bar: 'bg-red-500', badge: 'bg-red-500/20 text-red-400' },
  high: { bar: 'bg-orange-500', badge: 'bg-orange-500/20 text-orange-400' },
  medium: { bar: 'bg-amber-500', badge: 'bg-amber-500/20 text-amber-400' },
  low: { bar: 'bg-emerald-500', badge: 'bg-emerald-500/20 text-emerald-400' }
}

const statsCards = computed(() => [
  {
    key: 'open', label: 'Open', value: stats.value.open, filterValue: 'open',
    icon: InboxIcon, iconBg: 'bg-blue-500/20', iconColor: 'text-blue-400', textColor: 'text-blue-400',
    activeBg: 'from-blue-500 to-blue-600', activeRing: 'ring-blue-500'
  },
  {
    key: 'waiting', label: 'Waiting', value: stats.value.waiting, filterValue: 'waiting_response',
    icon: ClockIcon, iconBg: 'bg-amber-500/20', iconColor: 'text-amber-400', textColor: 'text-amber-400',
    activeBg: 'from-amber-500 to-orange-600', activeRing: 'ring-amber-500'
  },
  {
    key: 'urgent', label: 'Urgent', value: stats.value.urgent, filterValue: 'urgent',
    icon: ExclamationTriangleIcon, iconBg: 'bg-red-500/20', iconColor: 'text-red-400', textColor: 'text-red-400',
    activeBg: 'from-red-500 to-red-600', activeRing: 'ring-red-500'
  },
  {
    key: 'closed', label: 'Closed', value: stats.value.closed, filterValue: 'closed',
    icon: CheckCircleIcon, iconBg: 'bg-emerald-500/20', iconColor: 'text-emerald-400', textColor: 'text-emerald-400',
    activeBg: 'from-emerald-500 to-emerald-600', activeRing: 'ring-emerald-500'
  }
])

const hasActiveFilters = computed(() => filters.value.search || filters.value.status || filters.value.priority || filters.value.assigned)

const visiblePages = computed(() => {
  const pages = []
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  const delta = 2

  for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
    pages.push(i)
  }
  return pages
})

onMounted(() => {
  fetchTickets()
  fetchStaffUsers()
})

const fetchTickets = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.value.current_page,
      search: filters.value.search,
      status: filters.value.status === 'urgent' ? '' : filters.value.status,
      priority: filters.value.status === 'urgent' ? 'urgent' : filters.value.priority,
      assigned: filters.value.assigned
    }

    // If assigned filter is a number (user ID), pass it directly
    if (filters.value.assigned && !['unassigned', 'me'].includes(filters.value.assigned)) {
      params.user_id = filters.value.assigned
      params.assigned = ''
    }

    const response = await api.get('/admin/support/tickets', { params })
    tickets.value = response.data.tickets || response.data.data || response.data

    // Update pagination
    if (response.data.pagination) {
      pagination.value = response.data.pagination
    } else if (response.data.meta || response.data.current_page) {
      pagination.value = {
        current_page: response.data.meta?.current_page || response.data.current_page,
        last_page: response.data.meta?.last_page || response.data.last_page,
        per_page: response.data.meta?.per_page || response.data.per_page || 15,
        total: response.data.meta?.total || response.data.total
      }
    }

    // Update stats if included
    if (response.data.stats) {
      stats.value = response.data.stats
    }
  } catch (err) {
    console.error('Failed to fetch tickets:', err)
  } finally {
    loading.value = false
  }
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchTickets()
  }, 300)
}

const setFilter = (key, value) => {
  if (key === 'status' && value === 'urgent') {
    filters.value.status = 'urgent'
    filters.value.priority = ''
  } else {
    filters.value[key] = filters.value[key] === value ? '' : value
  }
  pagination.value.current_page = 1
  fetchTickets()
}

const handleAssignedFilter = () => {
  pagination.value.current_page = 1
  fetchTickets()
}

const fetchStaffUsers = async () => {
  try {
    const response = await api.get('/admin/support/tickets/staff/users')
    staffUsers.value = response.data.users || []
  } catch (error) {
    console.error('Failed to fetch staff users:', error)
  }
}

const clearFilters = () => {
  filters.value = { search: '', status: '', priority: '', assigned: '' }
  pagination.value.current_page = 1
  fetchTickets()
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page
    fetchTickets()
  }
}

const openTicket = (id) => router.push(`/tickets/${id}`)

const formatStatus = (status) => {
  const map = { open: 'Open', waiting_response: 'Waiting', answered: 'Answered', closed: 'Closed' }
  return map[status] || status
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A'
  const date = new Date(dateStr)
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  if (days < 7) return `${days}d ago`
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}
</script>

<style scoped>
.list-enter-active, .list-leave-active { transition: all 0.3s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(-10px); }
</style>

<template>
  <div class="tickets-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <h1>Support Tickets</h1>
        <p class="subtitle">Manage customer support requests</p>
      </div>
      <div class="header-stats">
        <div class="stat-card" :class="{ active: filters.status === 'open' }" @click="setStatusFilter('open')">
          <span class="stat-value">{{ stats.open }}</span>
          <span class="stat-label">Open</span>
        </div>
        <div class="stat-card warning" :class="{ active: filters.status === 'waiting_response' }" @click="setStatusFilter('waiting_response')">
          <span class="stat-value">{{ stats.waiting }}</span>
          <span class="stat-label">Waiting</span>
        </div>
        <div class="stat-card danger" :class="{ active: filters.priority === 'urgent' }" @click="setPriorityFilter('urgent')">
          <span class="stat-value">{{ stats.urgent }}</span>
          <span class="stat-label">Urgent</span>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
      <div class="search-wrapper">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <path d="m21 21-4.35-4.35"/>
        </svg>
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search tickets..."
          @input="debouncedSearch"
        >
      </div>
      <div class="filter-pills">
        <select v-model="filters.status" @change="fetchTickets" class="filter-select">
          <option value="">All Status</option>
          <option value="open">Open</option>
          <option value="waiting_response">Waiting</option>
          <option value="answered">Answered</option>
          <option value="closed">Closed</option>
        </select>
        <select v-model="filters.priority" @change="fetchTickets" class="filter-select">
          <option value="">All Priority</option>
          <option value="urgent">Urgent</option>
          <option value="high">High</option>
          <option value="medium">Medium</option>
          <option value="low">Low</option>
        </select>
        <select v-model="filters.assigned" @change="fetchTickets" class="filter-select">
          <option value="">All</option>
          <option value="unassigned">Unassigned</option>
          <option value="me">My Tickets</option>
        </select>
        <button v-if="hasActiveFilters" class="clear-btn" @click="clearFilters">
          Clear filters
        </button>
      </div>
    </div>

    <!-- Tickets List -->
    <div class="tickets-container">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <span>Loading tickets...</span>
      </div>

      <div v-else-if="tickets.length === 0" class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3>No tickets found</h3>
        <p>No tickets match your filters</p>
      </div>

      <div v-else class="tickets-list">
        <div
          v-for="ticket in tickets"
          :key="ticket.id"
          class="ticket-card"
          :class="[`priority-${ticket.priority}`, { closed: ticket.status === 'closed' }]"
          @click="openTicket(ticket.id)"
        >
          <div class="ticket-priority" :class="ticket.priority"></div>
          <div class="ticket-main">
            <div class="ticket-header">
              <span class="ticket-id">#{{ ticket.id }}</span>
              <span class="ticket-subject">{{ ticket.subject }}</span>
            </div>
            <div class="ticket-meta">
              <span class="meta-item user">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
                {{ ticket.user?.username || 'Unknown' }}
              </span>
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                {{ formatDate(ticket.updated_at) }}
              </span>
              <span v-if="ticket.messages_count" class="meta-item messages">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                {{ ticket.messages_count }}
              </span>
            </div>
          </div>
          <div class="ticket-status">
            <span class="status-badge" :class="ticket.status">
              {{ formatStatus(ticket.status) }}
            </span>
            <div class="ticket-assignee" v-if="ticket.assigned_user">
              <span class="avatar">{{ ticket.assigned_user.username?.charAt(0)?.toUpperCase() }}</span>
            </div>
            <div class="ticket-assignee unassigned" v-else>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v4M12 16h.01"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="pagination" v-if="pagination.last_page > 1">
        <button
          class="page-btn"
          :disabled="pagination.current_page === 1"
          @click="changePage(pagination.current_page - 1)"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6"/>
          </svg>
        </button>
        <span class="page-info">
          Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </span>
        <button
          class="page-btn"
          :disabled="pagination.current_page === pagination.last_page"
          @click="changePage(pagination.current_page + 1)"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 18l6-6-6-6"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const toast = useToast()

const loading = ref(true)
const tickets = ref([])
const categories = ref([])
const currentUser = ref(null)

const stats = ref({ open: 0, waiting: 0, urgent: 0 })

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

const filters = ref({
  search: '',
  status: '',
  priority: '',
  category: '',
  assigned: ''
})

let searchTimeout = null

const hasActiveFilters = computed(() => {
  return filters.value.search || filters.value.status || filters.value.priority || filters.value.assigned
})

const fetchTickets = async (page = 1) => {
  loading.value = true
  try {
    const params = { page, per_page: pagination.value.per_page, ...filters.value }
    Object.keys(params).forEach(key => { if (params[key] === '') delete params[key] })

    const response = await api.get('/admin/support/tickets', { params })
    tickets.value = response.data.tickets || response.data.data || []

    if (response.data.pagination) {
      pagination.value = response.data.pagination
    }
    if (response.data.stats) {
      stats.value = response.data.stats
    }
  } catch (error) {
    console.error('Failed to fetch tickets:', error)
    toast.error('Failed to load tickets')
  } finally {
    loading.value = false
  }
}

const fetchCurrentUser = async () => {
  try {
    const response = await api.get('/user')
    currentUser.value = response.data
  } catch (error) {
    console.error('Failed to fetch current user:', error)
  }
}

const debouncedSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchTickets(1), 300)
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchTickets(page)
  }
}

const openTicket = (id) => {
  router.push(`/tickets/${id}`)
}

const setStatusFilter = (status) => {
  filters.value.status = filters.value.status === status ? '' : status
  filters.value.priority = ''
  fetchTickets(1)
}

const setPriorityFilter = (priority) => {
  filters.value.priority = filters.value.priority === priority ? '' : priority
  filters.value.status = ''
  fetchTickets(1)
}

const clearFilters = () => {
  filters.value = { search: '', status: '', priority: '', category: '', assigned: '' }
  fetchTickets(1)
}

const formatStatus = (status) => {
  const labels = { open: 'Open', waiting_response: 'Waiting', answered: 'Answered', closed: 'Closed' }
  return labels[status] || status
}

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  const now = new Date()
  const diff = now - d
  if (diff < 60000) return 'Just now'
  if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`
  if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`
  if (diff < 604800000) return `${Math.floor(diff / 86400000)}d ago`
  return d.toLocaleDateString()
}

onMounted(() => {
  fetchTickets()
  fetchCurrentUser()
})
</script>

<style scoped>
.tickets-page {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  gap: 2rem;
}

.header-content h1 {
  font-size: 1.75rem;
  font-weight: 600;
  color: #f8fafc;
  margin: 0;
}

.subtitle {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0.25rem 0 0;
}

.header-stats {
  display: flex;
  gap: 0.75rem;
}

.stat-card {
  background: rgba(51, 65, 85, 0.5);
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
  min-width: 80px;
}

.stat-card:hover, .stat-card.active {
  background: rgba(59, 130, 246, 0.1);
  border-color: #3b82f6;
}

.stat-card.warning:hover, .stat-card.warning.active {
  background: rgba(245, 158, 11, 0.1);
  border-color: #f59e0b;
}

.stat-card.danger:hover, .stat-card.danger.active {
  background: rgba(239, 68, 68, 0.1);
  border-color: #ef4444;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #f8fafc;
}

.stat-label {
  font-size: 0.75rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Filters */
.filters-section {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  align-items: center;
}

.search-wrapper {
  position: relative;
  flex: 1;
  max-width: 320px;
}

.search-wrapper .search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #64748b;
}

.search-wrapper input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid #334155;
  border-radius: 10px;
  color: #f8fafc;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.search-wrapper input:focus {
  outline: none;
  border-color: #3b82f6;
  background: rgba(15, 23, 42, 0.8);
}

.search-wrapper input::placeholder {
  color: #64748b;
}

.filter-pills {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.filter-select {
  padding: 0.75rem 1rem;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid #334155;
  border-radius: 10px;
  color: #f8fafc;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
}

.clear-btn {
  padding: 0.75rem 1rem;
  background: transparent;
  border: 1px solid #475569;
  border-radius: 10px;
  color: #94a3b8;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.clear-btn:hover {
  background: rgba(239, 68, 68, 0.1);
  border-color: #ef4444;
  color: #ef4444;
}

/* Tickets List */
.tickets-container {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid #334155;
  border-radius: 16px;
  overflow: hidden;
}

.loading-state, .empty-state {
  padding: 4rem 2rem;
  text-align: center;
  color: #94a3b8;
}

.loading-state .spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #334155;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state svg {
  width: 48px;
  height: 48px;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #f8fafc;
  margin: 0 0 0.5rem;
}

.empty-state p {
  margin: 0;
}

.tickets-list {
  display: flex;
  flex-direction: column;
}

.ticket-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(51, 65, 85, 0.5);
  cursor: pointer;
  transition: background 0.15s;
}

.ticket-card:last-child {
  border-bottom: none;
}

.ticket-card:hover {
  background: rgba(51, 65, 85, 0.3);
}

.ticket-card.closed {
  opacity: 0.6;
}

.ticket-priority {
  width: 4px;
  height: 48px;
  border-radius: 2px;
  flex-shrink: 0;
}

.ticket-priority.urgent { background: #ef4444; }
.ticket-priority.high { background: #f59e0b; }
.ticket-priority.medium { background: #3b82f6; }
.ticket-priority.low { background: #22c55e; }

.ticket-main {
  flex: 1;
  min-width: 0;
}

.ticket-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.ticket-id {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  background: rgba(51, 65, 85, 0.5);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.ticket-subject {
  font-weight: 500;
  color: #f8fafc;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ticket-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.8rem;
  color: #64748b;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.meta-item svg {
  width: 14px;
  height: 14px;
  opacity: 0.7;
}

.meta-item.messages {
  color: #3b82f6;
}

.ticket-status {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-shrink: 0;
}

.status-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.status-badge.open {
  background: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
}

.status-badge.waiting_response {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
}

.status-badge.answered {
  background: rgba(34, 197, 94, 0.15);
  color: #4ade80;
}

.status-badge.closed {
  background: rgba(100, 116, 139, 0.15);
  color: #94a3b8;
}

.ticket-assignee {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.ticket-assignee .avatar {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.ticket-assignee.unassigned {
  background: rgba(51, 65, 85, 0.5);
  color: #64748b;
}

.ticket-assignee.unassigned svg {
  width: 16px;
  height: 16px;
}

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 1.25rem;
  border-top: 1px solid rgba(51, 65, 85, 0.5);
}

.page-btn {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(51, 65, 85, 0.5);
  border: 1px solid #334155;
  border-radius: 8px;
  color: #f8fafc;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  background: #3b82f6;
  border-color: #3b82f6;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-btn svg {
  width: 18px;
  height: 18px;
}

.page-info {
  font-size: 0.875rem;
  color: #94a3b8;
}

@media (max-width: 768px) {
  .tickets-page {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
  }

  .filters-section {
    flex-direction: column;
    align-items: stretch;
  }

  .search-wrapper {
    max-width: none;
  }

  .filter-pills {
    flex-wrap: wrap;
  }

  .ticket-card {
    padding: 1rem;
  }

  .ticket-meta {
    flex-wrap: wrap;
    gap: 0.5rem;
  }
}
</style>

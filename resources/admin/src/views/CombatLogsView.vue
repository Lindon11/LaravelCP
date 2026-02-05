<template>
  <div class="view-container">
    <div class="filters-row">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search by username..."
        class="search-input"
      >
      <select v-model="filterOutcome" class="filter-select">
        <option value="">All Outcomes</option>
        <option value="attacker_won">Attacker Won</option>
        <option value="defender_won">Defender Won</option>
      </select>
      <button @click="loadLogs" class="btn btn-secondary">
        🔄 Refresh
      </button>
    </div>

    <div v-if="loading" class="loading">Loading combat logs...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else-if="logs.length === 0" class="empty">No combat logs found.</div>

    <table v-else class="data-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Attacker</th>
          <th>Defender</th>
          <th>Outcome</th>
          <th>Damage Dealt</th>
          <th>Cash Stolen</th>
          <th>Respect Gained</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="log in filteredLogs" :key="log.id">
          <td>{{ formatDate(log.created_at) }}</td>
          <td>
            <span class="player-name">{{ log.attacker?.username || 'Unknown' }}</span>
          </td>
          <td>
            <span class="player-name">{{ log.defender?.username || 'Unknown' }}</span>
          </td>
          <td>
            <span class="badge" :class="log.attacker_won ? 'success' : 'danger'">
              {{ log.attacker_won ? 'Attacker Won' : 'Defender Won' }}
            </span>
          </td>
          <td>{{ log.damage_dealt }}</td>
          <td>${{ (log.cash_stolen || 0).toLocaleString() }}</td>
          <td>{{ log.respect_gained || 0 }}</td>
        </tr>
      </tbody>
    </table>

    <div v-if="!loading && totalPages > 1" class="pagination">
      <button @click="prevPage" :disabled="currentPage === 1" class="btn btn-sm">← Prev</button>
      <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="nextPage" :disabled="currentPage === totalPages" class="btn btn-sm">Next →</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const logs = ref([])
const loading = ref(true)
const error = ref(null)
const searchQuery = ref('')
const filterOutcome = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

const filteredLogs = computed(() => {
  return logs.value.filter(log => {
    const matchesSearch = !searchQuery.value ||
      log.attacker?.username?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      log.defender?.username?.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesOutcome = !filterOutcome.value ||
      (filterOutcome.value === 'attacker_won' && log.attacker_won) ||
      (filterOutcome.value === 'defender_won' && !log.attacker_won)
    return matchesSearch && matchesOutcome
  })
})

const loadLogs = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await api.get('/admin/combat-logs', {
      params: { page: currentPage.value }
    })
    logs.value = response.data.data || response.data || []
    totalPages.value = response.data.last_page || 1
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load combat logs'
  } finally {
    loading.value = false
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    loadLogs()
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    loadLogs()
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleString()
}

onMounted(loadLogs)
</script>

<style scoped>
.view-container {
  padding: 1.5rem;
}

.view-header {
  margin-bottom: 1.5rem;
}

.view-header h1 {
  color: #f1f5f9;
  margin: 0 0 0.5rem 0;
}

.subtitle {
  color: #94a3b8;
  margin: 0;
}

.filters-row {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-input,
.filter-select {
  padding: 0.75rem 1rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.5rem;
  background: rgba(15, 23, 42, 0.5);
  color: #f1f5f9;
  font-size: 0.875rem;
}

.search-input {
  flex: 1;
  min-width: 200px;
}

.search-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
}

.btn {
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  background: #475569;
  color: #f1f5f9;
}

.btn:hover:not(:disabled) {
  background: #64748b;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.loading,
.error,
.empty {
  text-align: center;
  padding: 3rem;
  color: #94a3b8;
}

.error {
  color: #f87171;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  background: rgba(15, 23, 42, 0.5);
  border-radius: 0.75rem;
  overflow: hidden;
}

.data-table th,
.data-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.data-table th {
  background: rgba(30, 41, 59, 0.8);
  color: #94a3b8;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
}

.data-table td {
  color: #f1f5f9;
}

.player-name {
  font-weight: 600;
  color: #3b82f6;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge.success {
  background: #10b981;
  color: white;
}

.badge.danger {
  background: #ef4444;
  color: white;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.page-info {
  color: #94a3b8;
}
</style>

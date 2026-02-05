<template>
  <div class="view-container">
    <div class="filters-row">
      <select v-model="filterStatus" class="filter-select">
        <option value="">All Races</option>
        <option value="waiting">Waiting</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
      <button @click="loadRaces" class="btn btn-secondary">
        🔄 Refresh
      </button>
    </div>

    <div v-if="loading" class="loading">Loading races...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else-if="races.length === 0" class="empty">No races found.</div>

    <table v-else class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Created By</th>
          <th>Status</th>
          <th>Buy-in</th>
          <th>Participants</th>
          <th>Winner</th>
          <th>Prize Pool</th>
          <th>Created At</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="race in filteredRaces" :key="race.id">
          <td>#{{ race.id }}</td>
          <td>{{ race.creator?.username || 'Unknown' }}</td>
          <td>
            <span class="badge" :class="race.status">{{ race.status }}</span>
          </td>
          <td>${{ (race.buy_in || 0).toLocaleString() }}</td>
          <td>{{ race.participants?.length || 0 }}/{{ race.max_participants || 4 }}</td>
          <td>{{ race.winner?.username || '-' }}</td>
          <td>${{ (race.prize_pool || 0).toLocaleString() }}</td>
          <td>{{ formatDate(race.created_at) }}</td>
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

const races = ref([])
const loading = ref(true)
const error = ref(null)
const filterStatus = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

const filteredRaces = computed(() => {
  if (!filterStatus.value) return races.value
  return races.value.filter(race => race.status === filterStatus.value)
})

const loadRaces = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await api.get('/admin/races', {
      params: { page: currentPage.value }
    })
    races.value = response.data.data || response.data || []
    totalPages.value = response.data.last_page || 1
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load races'
  } finally {
    loading.value = false
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    loadRaces()
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    loadRaces()
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleString()
}

onMounted(loadRaces)
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

.filter-select {
  padding: 0.75rem 1rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.5rem;
  background: rgba(15, 23, 42, 0.5);
  color: #f1f5f9;
  font-size: 0.875rem;
}

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

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.badge.waiting { background: #f59e0b; color: white; }
.badge.in_progress { background: #3b82f6; color: white; }
.badge.completed { background: #10b981; color: white; }
.badge.cancelled { background: #6b7280; color: white; }

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

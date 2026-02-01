<template>
  <div class="view-container">
    <div class="view-header">
      <h1>User Timers</h1>
      <p class="subtitle">Monitor active user cooldowns and timers</p>
    </div>

    <div class="filters-row">
      <input 
        v-model="searchQuery" 
        type="text" 
        placeholder="Search by username..." 
        class="search-input"
      >
      <select v-model="filterType" class="filter-select">
        <option value="">All Types</option>
        <option value="crime">Crime Cooldown</option>
        <option value="theft">Theft Cooldown</option>
        <option value="gym">Gym Cooldown</option>
        <option value="travel">Travel Cooldown</option>
        <option value="jail">Jail Timer</option>
      </select>
      <button @click="loadTimers" class="btn btn-secondary">
        🔄 Refresh
      </button>
    </div>

    <div v-if="loading" class="loading">Loading timers...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else-if="timers.length === 0" class="empty">No active timers found.</div>
    
    <table v-else class="data-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Timer Type</th>
          <th>Started At</th>
          <th>Expires At</th>
          <th>Remaining</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="timer in filteredTimers" :key="timer.id">
          <td>{{ timer.user?.username || timer.user_id }}</td>
          <td><span class="badge" :class="timer.type">{{ timer.type }}</span></td>
          <td>{{ formatDate(timer.created_at) }}</td>
          <td>{{ formatDate(timer.expires_at) }}</td>
          <td>{{ formatRemaining(timer.expires_at) }}</td>
          <td>
            <button @click="clearTimer(timer.id)" class="btn btn-danger btn-sm">
              Clear
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const timers = ref([])
const loading = ref(true)
const error = ref(null)
const searchQuery = ref('')
const filterType = ref('')

const filteredTimers = computed(() => {
  return timers.value.filter(timer => {
    const matchesSearch = !searchQuery.value || 
      timer.user?.username?.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesType = !filterType.value || timer.type === filterType.value
    return matchesSearch && matchesType
  })
})

const loadTimers = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await api.get('/admin/user-timers')
    timers.value = response.data.data || response.data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load timers'
  } finally {
    loading.value = false
  }
}

const clearTimer = async (id) => {
  if (!confirm('Clear this timer?')) return
  try {
    await api.delete(`/admin/user-timers/${id}`)
    timers.value = timers.value.filter(t => t.id !== id)
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to clear timer')
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleString()
}

const formatRemaining = (expiresAt) => {
  const now = new Date()
  const expires = new Date(expiresAt)
  const diff = expires - now
  
  if (diff <= 0) return 'Expired'
  
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  const seconds = Math.floor((diff % (1000 * 60)) / 1000)
  
  if (hours > 0) return `${hours}h ${minutes}m`
  if (minutes > 0) return `${minutes}m ${seconds}s`
  return `${seconds}s`
}

onMounted(loadTimers)
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
}

.btn-secondary {
  background: #475569;
  color: #f1f5f9;
}

.btn-secondary:hover {
  background: #64748b;
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
  font-size: 0.75rem;
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

.badge.crime { background: #7c3aed; color: white; }
.badge.theft { background: #f59e0b; color: white; }
.badge.gym { background: #10b981; color: white; }
.badge.travel { background: #3b82f6; color: white; }
.badge.jail { background: #ef4444; color: white; }
</style>

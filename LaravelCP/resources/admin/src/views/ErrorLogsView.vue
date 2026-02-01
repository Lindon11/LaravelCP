<template>
  <div class="error-logs-view">
    <div class="page-header">
      <h1>📋 Error Logs</h1>
      <p class="subtitle">Monitor application errors and exceptions</p>
    </div>

    <div class="filters-bar">
      <div class="filter-group">
        <label>Level</label>
        <select v-model="filters.level" @change="loadLogs">
          <option value="">All Levels</option>
          <option value="emergency">🚨 Emergency</option>
          <option value="alert">🔔 Alert</option>
          <option value="critical">💀 Critical</option>
          <option value="error">❌ Error</option>
          <option value="warning">⚠️ Warning</option>
          <option value="notice">📌 Notice</option>
          <option value="info">ℹ️ Info</option>
          <option value="debug">🔍 Debug</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Date Range</label>
        <select v-model="filters.dateRange" @change="loadLogs">
          <option value="today">Today</option>
          <option value="yesterday">Yesterday</option>
          <option value="week">Last 7 Days</option>
          <option value="month">Last 30 Days</option>
          <option value="all">All Time</option>
        </select>
      </div>

      <div class="filter-group search">
        <label>Search</label>
        <input 
          v-model="filters.search" 
          type="text" 
          placeholder="Search in messages..."
          @keyup.enter="loadLogs"
        >
      </div>

      <button class="btn-refresh" @click="loadLogs" :disabled="loading">
        {{ loading ? '🔄' : '🔃' }} Refresh
      </button>

      <button class="btn-clear" @click="confirmClearLogs" :disabled="loading">
        🗑️ Clear Logs
      </button>
    </div>

    <div class="stats-bar">
      <div class="stat-item emergency">
        <span class="count">{{ stats.emergency }}</span>
        <span class="label">Emergency</span>
      </div>
      <div class="stat-item critical">
        <span class="count">{{ stats.critical }}</span>
        <span class="label">Critical</span>
      </div>
      <div class="stat-item error">
        <span class="count">{{ stats.error }}</span>
        <span class="label">Errors</span>
      </div>
      <div class="stat-item warning">
        <span class="count">{{ stats.warning }}</span>
        <span class="label">Warnings</span>
      </div>
      <div class="stat-item info">
        <span class="count">{{ stats.info }}</span>
        <span class="label">Info</span>
      </div>
    </div>

    <div class="logs-container">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading error logs...</p>
      </div>

      <div v-else-if="logs.length === 0" class="empty-state">
        <span class="icon">✨</span>
        <h3>No Errors Found</h3>
        <p>The application is running smoothly!</p>
      </div>

      <div v-else class="logs-list">
        <div 
          v-for="log in logs" 
          :key="log.id" 
          class="log-entry"
          :class="log.level"
          @click="selectedLog = log"
        >
          <div class="log-header">
            <span class="level-badge" :class="log.level">{{ log.level.toUpperCase() }}</span>
            <span class="timestamp">{{ formatDate(log.created_at) }}</span>
          </div>
          <div class="log-message">{{ truncateMessage(log.message) }}</div>
          <div class="log-meta">
            <span v-if="log.context?.file">📁 {{ log.context.file }}</span>
            <span v-if="log.context?.line">Line {{ log.context.line }}</span>
            <span v-if="log.context?.user_id">👤 User #{{ log.context.user_id }}</span>
          </div>
        </div>
      </div>

      <div class="pagination" v-if="totalPages > 1">
        <button @click="prevPage" :disabled="currentPage === 1">« Previous</button>
        <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages">Next »</button>
      </div>
    </div>

    <!-- Log Detail Modal -->
    <div v-if="selectedLog" class="modal-overlay" @click.self="selectedLog = null">
      <div class="modal-content">
        <div class="modal-header">
          <h2>
            <span class="level-badge large" :class="selectedLog.level">{{ selectedLog.level.toUpperCase() }}</span>
            Error Details
          </h2>
          <button class="close-btn" @click="selectedLog = null">✕</button>
        </div>
        <div class="modal-body">
          <div class="detail-section">
            <label>Timestamp</label>
            <p>{{ formatDate(selectedLog.created_at) }}</p>
          </div>
          <div class="detail-section">
            <label>Message</label>
            <pre class="message-block">{{ selectedLog.message }}</pre>
          </div>
          <div class="detail-section" v-if="selectedLog.context?.file">
            <label>Location</label>
            <p>{{ selectedLog.context.file }}:{{ selectedLog.context.line }}</p>
          </div>
          <div class="detail-section" v-if="selectedLog.stack_trace">
            <label>Stack Trace</label>
            <pre class="stack-trace">{{ selectedLog.stack_trace }}</pre>
          </div>
          <div class="detail-section" v-if="selectedLog.context">
            <label>Context</label>
            <pre class="context-block">{{ JSON.stringify(selectedLog.context, null, 2) }}</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'

const loading = ref(false)
const logs = ref([])
const selectedLog = ref(null)
const currentPage = ref(1)
const totalPages = ref(1)
const perPage = 50

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

const loadLogs = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage,
      ...filters
    }
    
    const response = await api.get('/admin/error-logs', { params })
    logs.value = response.data.data || []
    totalPages.value = response.data.last_page || 1
    
    if (response.data.stats) {
      Object.assign(stats, response.data.stats)
    }
  } catch (error) {
    console.error('Error loading logs:', error)
  } finally {
    loading.value = false
  }
}

const confirmClearLogs = () => {
  if (confirm('Are you sure you want to clear all error logs? This action cannot be undone.')) {
    clearLogs()
  }
}

const clearLogs = async () => {
  try {
    await api.delete('/admin/error-logs/clear')
    logs.value = []
    Object.keys(stats).forEach(key => stats[key] = 0)
  } catch (error) {
    console.error('Error clearing logs:', error)
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleString()
}

const truncateMessage = (message, length = 150) => {
  return message?.length > length ? message.substring(0, length) + '...' : message
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

onMounted(() => {
  loadLogs()
})
</script>

<style scoped>
.error-logs-view {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  color: #f1f5f9;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #94a3b8;
}

.filters-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: flex-end;
  margin-bottom: 1.5rem;
  padding: 1.5rem;
  background: rgba(30, 41, 59, 0.5);
  border-radius: 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.1);
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
}

.filter-group select,
.filter-group input {
  padding: 0.75rem 1rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  color: #f1f5f9;
  min-width: 150px;
}

.filter-group.search {
  flex: 1;
  min-width: 200px;
}

.filter-group.search input {
  width: 100%;
}

.btn-refresh,
.btn-clear {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-refresh {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  border: none;
}

.btn-refresh:hover:not(:disabled) {
  transform: translateY(-2px);
}

.btn-clear {
  background: transparent;
  border: 1px solid #ef4444;
  color: #ef4444;
}

.btn-clear:hover:not(:disabled) {
  background: rgba(239, 68, 68, 0.1);
}

.stats-bar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.stat-item {
  flex: 1;
  min-width: 120px;
  padding: 1rem;
  background: rgba(30, 41, 59, 0.5);
  border-radius: 0.5rem;
  border-left: 3px solid;
  text-align: center;
}

.stat-item.emergency { border-color: #dc2626; }
.stat-item.critical { border-color: #ea580c; }
.stat-item.error { border-color: #ef4444; }
.stat-item.warning { border-color: #f59e0b; }
.stat-item.info { border-color: #3b82f6; }

.stat-item .count {
  font-size: 1.5rem;
  font-weight: 700;
  color: #f1f5f9;
  display: block;
}

.stat-item .label {
  font-size: 0.75rem;
  color: #94a3b8;
}

.logs-container {
  background: rgba(30, 41, 59, 0.5);
  border-radius: 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.1);
  overflow: hidden;
}

.loading-state,
.empty-state {
  padding: 4rem;
  text-align: center;
  color: #94a3b8;
}

.empty-state .icon {
  font-size: 4rem;
  display: block;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: #10b981;
  margin-bottom: 0.5rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(59, 130, 246, 0.3);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.logs-list {
  max-height: 600px;
  overflow-y: auto;
}

.log-entry {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  cursor: pointer;
  transition: background 0.2s ease;
}

.log-entry:hover {
  background: rgba(59, 130, 246, 0.05);
}

.log-entry.emergency,
.log-entry.critical {
  border-left: 3px solid #dc2626;
}

.log-entry.error {
  border-left: 3px solid #ef4444;
}

.log-entry.warning {
  border-left: 3px solid #f59e0b;
}

.log-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.level-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 0.25rem;
  font-size: 0.625rem;
  font-weight: 700;
}

.level-badge.emergency,
.level-badge.alert { background: #dc2626; color: white; }
.level-badge.critical { background: #ea580c; color: white; }
.level-badge.error { background: #ef4444; color: white; }
.level-badge.warning { background: #f59e0b; color: #1e293b; }
.level-badge.notice { background: #3b82f6; color: white; }
.level-badge.info { background: #0ea5e9; color: white; }
.level-badge.debug { background: #64748b; color: white; }

.level-badge.large {
  font-size: 0.75rem;
  padding: 0.375rem 1rem;
  margin-right: 0.75rem;
}

.timestamp {
  font-size: 0.75rem;
  color: #64748b;
}

.log-message {
  color: #f1f5f9;
  font-family: 'Monaco', 'Consolas', monospace;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.log-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.75rem;
  color: #64748b;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.pagination button {
  padding: 0.5rem 1rem;
  background: rgba(59, 130, 246, 0.2);
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-radius: 0.375rem;
  color: #3b82f6;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #94a3b8;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
}

.modal-content {
  background: #1e293b;
  border-radius: 1rem;
  width: 100%;
  max-width: 800px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.modal-header h2 {
  color: #f1f5f9;
  display: flex;
  align-items: center;
}

.close-btn {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 1.5rem;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
}

.detail-section {
  margin-bottom: 1.5rem;
}

.detail-section label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  margin-bottom: 0.5rem;
}

.detail-section p {
  color: #f1f5f9;
}

.message-block,
.stack-trace,
.context-block {
  background: rgba(15, 23, 42, 0.5);
  padding: 1rem;
  border-radius: 0.5rem;
  overflow-x: auto;
  font-family: 'Monaco', 'Consolas', monospace;
  font-size: 0.8125rem;
  color: #e2e8f0;
  white-space: pre-wrap;
  word-break: break-word;
}

.stack-trace {
  max-height: 300px;
  overflow-y: auto;
  color: #f87171;
}
</style>

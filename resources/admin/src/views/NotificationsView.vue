<template>
  <div class="notifications-page">
    <div class="page-header">
      <div class="header-left">
        <h1>Notifications</h1>
        <span class="unread-badge" v-if="unreadCount > 0">{{ unreadCount }} unread</span>
      </div>
      <div class="header-actions">
        <button class="btn btn-secondary" @click="sendTestNotification" :disabled="sendingTest">
          {{ sendingTest ? 'Sending...' : '🧪 Send Test' }}
        </button>
        <button class="btn btn-secondary" @click="markAllAsRead" :disabled="unreadCount === 0">
          ✓ Mark All Read
        </button>
        <button class="btn btn-danger" @click="clearReadNotifications" :disabled="!hasReadNotifications">
          🗑️ Clear Read
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="filter-group">
        <label>Status:</label>
        <select v-model="filters.status" @change="fetchNotifications">
          <option value="all">All</option>
          <option value="unread">Unread</option>
          <option value="read">Read</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Type:</label>
        <select v-model="filters.type" @change="fetchNotifications">
          <option value="all">All Types</option>
          <option value="info">Info</option>
          <option value="success">Success</option>
          <option value="warning">Warning</option>
          <option value="error">Error</option>
          <option value="task">Task</option>
          <option value="user">User</option>
          <option value="system">System</option>
          <option value="report">Report</option>
          <option value="ticket">Ticket</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Priority:</label>
        <select v-model="filters.priority" @change="fetchNotifications">
          <option value="all">All Priorities</option>
          <option value="urgent">Urgent</option>
          <option value="high">High</option>
          <option value="normal">Normal</option>
          <option value="low">Low</option>
        </select>
      </div>
    </div>

    <!-- Notifications List -->
    <div class="notifications-list" v-if="!loading">
      <div v-if="notifications.length === 0" class="empty-state">
        <span class="empty-icon">📭</span>
        <h3>No notifications</h3>
        <p>You're all caught up!</p>
      </div>

      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="notification-card"
        :class="{
          unread: !notification.is_read,
          [notification.priority]: true,
          [notification.type]: true
        }"
      >
        <div class="notification-icon">{{ notification.icon }}</div>

        <div class="notification-content">
          <div class="notification-header">
            <h3>{{ notification.title }}</h3>
            <div class="notification-meta">
              <span class="priority-badge" :class="notification.priority">
                {{ notification.priority }}
              </span>
              <span class="type-badge" :class="notification.type">
                {{ notification.type }}
              </span>
              <span class="time">{{ notification.time_ago }}</span>
            </div>
          </div>

          <p class="notification-message">{{ notification.message }}</p>

          <div class="notification-actions">
            <button
              v-if="!notification.is_read"
              class="btn btn-small btn-primary"
              @click="markAsRead(notification.id)"
            >
              Mark as Read
            </button>
            <router-link
              v-if="notification.link"
              :to="notification.link"
              class="btn btn-small btn-secondary"
            >
              View Details →
            </router-link>
            <button
              class="btn btn-small btn-danger"
              @click="deleteNotification(notification.id)"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading notifications...</p>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="pagination.last_page > 1">
      <button
        class="btn btn-secondary"
        @click="changePage(pagination.current_page - 1)"
        :disabled="pagination.current_page === 1"
      >
        ← Previous
      </button>
      <span class="page-info">
        Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </span>
      <button
        class="btn btn-secondary"
        @click="changePage(pagination.current_page + 1)"
        :disabled="pagination.current_page === pagination.last_page"
      >
        Next →
      </button>
    </div>

    <!-- Broadcast Modal -->
    <div class="modal-overlay" v-if="showBroadcastModal" @click.self="showBroadcastModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2>Broadcast Notification</h2>
          <button class="close-btn" @click="showBroadcastModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Title</label>
            <input v-model="broadcastForm.title" type="text" placeholder="Notification title">
          </div>
          <div class="form-group">
            <label>Message</label>
            <textarea v-model="broadcastForm.message" rows="4" placeholder="Notification message"></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Type</label>
              <select v-model="broadcastForm.type">
                <option value="info">Info</option>
                <option value="success">Success</option>
                <option value="warning">Warning</option>
                <option value="error">Error</option>
                <option value="system">System</option>
              </select>
            </div>
            <div class="form-group">
              <label>Priority</label>
              <select v-model="broadcastForm.priority">
                <option value="low">Low</option>
                <option value="normal">Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Link (optional)</label>
            <input v-model="broadcastForm.link" type="text" placeholder="/dashboard">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showBroadcastModal = false">Cancel</button>
          <button class="btn btn-primary" @click="sendBroadcast" :disabled="broadcasting">
            {{ broadcasting ? 'Sending...' : 'Send to All Admins' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const loading = ref(true)
const sendingTest = ref(false)
const broadcasting = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const showBroadcastModal = ref(false)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

const filters = ref({
  status: 'all',
  type: 'all',
  priority: 'all'
})

const broadcastForm = ref({
  title: '',
  message: '',
  type: 'info',
  priority: 'normal',
  link: ''
})

const hasReadNotifications = computed(() => {
  return notifications.value.some(n => n.is_read)
})

const fetchNotifications = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value
    }

    const response = await api.get('/admin/notifications', { params })
    notifications.value = response.data.notifications
    pagination.value = response.data.pagination
    unreadCount.value = response.data.unread_count
  } catch (error) {
    toast.error('Failed to load notifications')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  fetchNotifications(page)
}

const markAsRead = async (id) => {
  try {
    await api.post(`/admin/notifications/${id}/read`)
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
    toast.success('Marked as read')
  } catch (error) {
    toast.error('Failed to mark as read')
  }
}

const markAllAsRead = async () => {
  try {
    const response = await api.post('/admin/notifications/read-all')
    notifications.value.forEach(n => n.is_read = true)
    unreadCount.value = 0
    toast.success(response.data.message)
  } catch (error) {
    toast.error('Failed to mark all as read')
  }
}

const deleteNotification = async (id) => {
  if (!confirm('Delete this notification?')) return

  try {
    await api.delete(`/admin/notifications/${id}`)
    notifications.value = notifications.value.filter(n => n.id !== id)
    toast.success('Notification deleted')
  } catch (error) {
    toast.error('Failed to delete notification')
  }
}

const clearReadNotifications = async () => {
  if (!confirm('Delete all read notifications?')) return

  try {
    const response = await api.delete('/admin/notifications/clear-read')
    notifications.value = notifications.value.filter(n => !n.is_read)
    toast.success(response.data.message)
  } catch (error) {
    toast.error('Failed to clear notifications')
  }
}

const sendTestNotification = async () => {
  sendingTest.value = true
  try {
    await api.post('/admin/notifications/test')
    toast.success('Test notification sent!')
    fetchNotifications()
  } catch (error) {
    toast.error('Failed to send test notification')
  } finally {
    sendingTest.value = false
  }
}

const sendBroadcast = async () => {
  broadcasting.value = true
  try {
    await api.post('/admin/notifications/broadcast', broadcastForm.value)
    toast.success('Broadcast sent to all admins!')
    showBroadcastModal.value = false
    broadcastForm.value = {
      title: '',
      message: '',
      type: 'info',
      priority: 'normal',
      link: ''
    }
    fetchNotifications()
  } catch (error) {
    toast.error('Failed to send broadcast')
  } finally {
    broadcasting.value = false
  }
}

onMounted(() => {
  fetchNotifications()
})
</script>

<style scoped>
.notifications-page {
  padding: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-left h1 {
  margin: 0;
  font-size: 1.5rem;
  color: #f1f5f9;
}

.unread-badge {
  background: #3b82f6;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.filters-bar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: #1e293b;
  border-radius: 8px;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-group label {
  font-size: 0.875rem;
  color: #94a3b8;
}

.filter-group select {
  background: #0f172a;
  border: 1px solid #334155;
  color: #f1f5f9;
  padding: 0.5rem;
  border-radius: 6px;
  font-size: 0.875rem;
}

.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.notification-card {
  display: flex;
  gap: 1rem;
  padding: 1.25rem;
  background: #1e293b;
  border-radius: 10px;
  border-left: 4px solid #334155;
  transition: all 0.2s;
}

.notification-card:hover {
  background: #253243;
}

.notification-card.unread {
  background: rgba(59, 130, 246, 0.08);
  border-left-color: #3b82f6;
}

.notification-card.urgent {
  border-left-color: #ef4444;
}

.notification-card.high {
  border-left-color: #f59e0b;
}

.notification-card.success {
  border-left-color: #10b981;
}

.notification-card.warning {
  border-left-color: #f59e0b;
}

.notification-card.error {
  border-left-color: #ef4444;
}

.notification-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.notification-content {
  flex: 1;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.notification-header h3 {
  margin: 0;
  font-size: 1rem;
  color: #f1f5f9;
}

.notification-meta {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.priority-badge,
.type-badge {
  font-size: 0.65rem;
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  text-transform: uppercase;
  font-weight: 600;
}

.priority-badge.urgent {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.priority-badge.high {
  background: rgba(245, 158, 11, 0.2);
  color: #f59e0b;
}

.priority-badge.normal {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
}

.priority-badge.low {
  background: rgba(100, 116, 139, 0.2);
  color: #94a3b8;
}

.type-badge {
  background: rgba(100, 116, 139, 0.2);
  color: #94a3b8;
}

.time {
  font-size: 0.75rem;
  color: #64748b;
}

.notification-message {
  color: #94a3b8;
  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 0.75rem;
}

.notification-actions {
  display: flex;
  gap: 0.5rem;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.15s;
}

.btn-small {
  padding: 0.35rem 0.75rem;
  font-size: 0.75rem;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #334155;
  color: #f1f5f9;
}

.btn-secondary:hover {
  background: #475569;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  background: #1e293b;
  border-radius: 10px;
}

.empty-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}

.empty-state h3 {
  margin: 0 0 0.5rem;
  color: #f1f5f9;
}

.empty-state p {
  color: #64748b;
  margin: 0;
}

.loading-state {
  text-align: center;
  padding: 3rem;
}

.loading-spinner {
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

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.page-info {
  color: #94a3b8;
  font-size: 0.875rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: #1e293b;
  border-radius: 12px;
  width: 500px;
  max-width: 90vw;
  max-height: 90vh;
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #334155;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #f1f5f9;
}

.close-btn {
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  font-size: 1.25rem;
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  color: #94a3b8;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 6px;
  color: #f1f5f9;
  font-size: 0.875rem;
}

.form-row {
  display: flex;
  gap: 1rem;
}

.form-row .form-group {
  flex: 1;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #334155;
}
</style>

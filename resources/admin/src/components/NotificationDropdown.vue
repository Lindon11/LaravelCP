<template>
  <div class="notification-dropdown" ref="dropdownRef">
    <button 
      class="notification-trigger" 
      @click="toggleDropdown"
      :class="{ 'has-unread': unreadCount > 0 }"
    >
      <span class="bell-icon">🔔</span>
      <span v-if="unreadCount > 0" class="badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
    </button>

    <transition name="dropdown-fade">
      <div v-if="isOpen" class="dropdown-panel">
        <div class="dropdown-header">
          <h3>Notifications</h3>
          <div class="header-actions">
            <button 
              v-if="unreadCount > 0"
              class="mark-all-btn"
              @click="markAllAsRead"
              title="Mark all as read"
            >
              ✓ All
            </button>
            <router-link to="/notifications" class="view-all-link" @click="closeDropdown">
              View All
            </router-link>
          </div>
        </div>

        <div class="dropdown-content" v-if="!loading">
          <div v-if="notifications.length === 0" class="empty-state">
            <span class="empty-icon">📭</span>
            <p>No notifications</p>
          </div>

          <div 
            v-for="notification in notifications" 
            :key="notification.id"
            class="notification-item"
            :class="{ 
              unread: !notification.is_read,
              [notification.priority]: true,
              [notification.type]: true
            }"
            @click="handleNotificationClick(notification)"
          >
            <span class="notification-icon">{{ notification.icon }}</span>
            <div class="notification-body">
              <div class="notification-title">{{ notification.title }}</div>
              <div class="notification-message">{{ truncateMessage(notification.message) }}</div>
              <div class="notification-time">{{ notification.time_ago }}</div>
            </div>
            <button 
              v-if="!notification.is_read"
              class="mark-read-btn"
              @click.stop="markAsRead(notification.id)"
              title="Mark as read"
            >
              ✓
            </button>
          </div>
        </div>

        <div v-else class="dropdown-content loading">
          <div class="loading-spinner"></div>
        </div>

        <div class="dropdown-footer">
          <router-link to="/notifications" class="footer-link" @click="closeDropdown">
            See all notifications →
          </router-link>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const dropdownRef = ref(null)
const isOpen = ref(false)
const loading = ref(false)
const notifications = ref([])
const unreadCount = ref(0)

let pollInterval = null

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    fetchNotifications()
  }
}

const closeDropdown = () => {
  isOpen.value = false
}

const fetchNotifications = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/notifications/recent')
    notifications.value = response.data.notifications
    unreadCount.value = response.data.unread_count
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  } finally {
    loading.value = false
  }
}

const fetchUnreadCount = async () => {
  try {
    const response = await api.get('/admin/notifications/unread-count')
    unreadCount.value = response.data.count
  } catch (error) {
    console.error('Failed to fetch unread count:', error)
  }
}

const markAsRead = async (id) => {
  try {
    await api.post(`/admin/notifications/${id}/read`)
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
  } catch (error) {
    console.error('Failed to mark as read:', error)
  }
}

const markAllAsRead = async () => {
  try {
    await api.post('/admin/notifications/read-all')
    notifications.value.forEach(n => n.is_read = true)
    unreadCount.value = 0
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) {
    await markAsRead(notification.id)
  }
  
  if (notification.link) {
    closeDropdown()
    router.push(notification.link)
  }
}

const truncateMessage = (message) => {
  return message.length > 60 ? message.substring(0, 60) + '...' : message
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  fetchUnreadCount()
  
  // Poll for new notifications every 30 seconds
  pollInterval = setInterval(fetchUnreadCount, 30000)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (pollInterval) {
    clearInterval(pollInterval)
  }
})
</script>

<style scoped>
.notification-dropdown {
  position: relative;
}

.notification-trigger {
  position: relative;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 8px;
  transition: background 0.2s;
}

.notification-trigger:hover {
  background: rgba(255, 255, 255, 0.1);
}

.bell-icon {
  font-size: 1.25rem;
}

.badge {
  position: absolute;
  top: 0;
  right: 0;
  background: #ef4444;
  color: white;
  font-size: 0.65rem;
  font-weight: 600;
  padding: 0.1rem 0.35rem;
  border-radius: 9999px;
  min-width: 1rem;
  text-align: center;
}

.notification-trigger.has-unread .bell-icon {
  animation: ring 0.5s ease-in-out;
}

@keyframes ring {
  0%, 100% { transform: rotate(0); }
  20%, 60% { transform: rotate(15deg); }
  40%, 80% { transform: rotate(-15deg); }
}

.dropdown-panel {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  width: 360px;
  max-height: 480px;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
  overflow: hidden;
  z-index: 1000;
}

.dropdown-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #334155;
  background: #0f172a;
}

.dropdown-header h3 {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #f1f5f9;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.mark-all-btn {
  background: transparent;
  border: none;
  color: #10b981;
  font-size: 0.75rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.mark-all-btn:hover {
  background: rgba(16, 185, 129, 0.1);
}

.view-all-link {
  color: #3b82f6;
  text-decoration: none;
  font-size: 0.75rem;
}

.view-all-link:hover {
  text-decoration: underline;
}

.dropdown-content {
  max-height: 340px;
  overflow-y: auto;
}

.dropdown-content.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem;
}

.loading-spinner {
  width: 24px;
  height: 24px;
  border: 2px solid #334155;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
  padding: 2rem;
  text-align: center;
  color: #64748b;
}

.empty-icon {
  font-size: 2rem;
  display: block;
  margin-bottom: 0.5rem;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  cursor: pointer;
  transition: background 0.15s;
  border-bottom: 1px solid #1e293b;
}

.notification-item:hover {
  background: #334155;
}

.notification-item.unread {
  background: rgba(59, 130, 246, 0.08);
  border-left: 3px solid #3b82f6;
}

.notification-item.urgent {
  border-left-color: #ef4444;
}

.notification-item.high {
  border-left-color: #f59e0b;
}

.notification-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.notification-body {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-weight: 500;
  font-size: 0.875rem;
  color: #f1f5f9;
  margin-bottom: 0.25rem;
}

.notification-message {
  font-size: 0.8rem;
  color: #94a3b8;
  line-height: 1.4;
  margin-bottom: 0.25rem;
}

.notification-time {
  font-size: 0.7rem;
  color: #64748b;
}

.mark-read-btn {
  background: transparent;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 4px;
  opacity: 0;
  transition: opacity 0.15s;
}

.notification-item:hover .mark-read-btn {
  opacity: 1;
}

.mark-read-btn:hover {
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
}

.dropdown-footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid #334155;
  background: #0f172a;
  text-align: center;
}

.footer-link {
  color: #3b82f6;
  text-decoration: none;
  font-size: 0.8rem;
}

.footer-link:hover {
  text-decoration: underline;
}

/* Transitions */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 0.2s, transform 0.2s;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>

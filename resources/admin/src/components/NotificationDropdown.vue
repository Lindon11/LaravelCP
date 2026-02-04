<template>
  <div class="relative" ref="dropdownRef">
    <button
      @click="toggleDropdown"
      :class="[
        'relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all duration-200',
        unreadCount > 0 && 'text-amber-400'
      ]"
    >
      <!-- Bell Icon from Heroicons (outline) -->
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 text-xs font-medium text-white bg-gradient-to-r from-red-500 to-red-600 rounded-full shadow-lg"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div v-if="isOpen" class="absolute right-0 top-full mt-2 w-80 bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl z-50 overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-white">Notifications</h3>
          <div class="flex items-center gap-2">
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              class="text-xs px-3 py-1.5 bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 rounded-lg transition-colors"
              title="Mark all as read"
            >
              Mark All Read
            </button>
            <router-link
              to="/notifications"
              @click="closeDropdown"
              class="text-xs px-3 py-1.5 bg-slate-700 text-slate-300 hover:bg-slate-600 rounded-lg transition-colors"
            >
              View All
            </router-link>
          </div>
        </div>

        <div class="max-h-96 overflow-y-auto" v-if="!loading">
          <div v-if="notifications.length === 0" class="p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-slate-400">No notifications</p>
          </div>

          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="p-4 border-b border-slate-700/50 hover:bg-slate-700/25 transition-colors cursor-pointer last:border-b-0"
            :class="{
              'bg-slate-700/10': !notification.is_read,
            }"
            @click="handleNotificationClick(notification)"
          >
            <div class="flex items-start gap-3">
              <div :class="[
                'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm',
                notification.priority === 'high' ? 'bg-red-500/20 text-red-400' :
                notification.priority === 'medium' ? 'bg-amber-500/20 text-amber-400' :
                'bg-blue-500/20 text-blue-400'
              ]">
                <span>{{ notification.icon }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                  <p class="text-sm font-medium text-white truncate">{{ notification.title }}</p>
                  <span class="text-xs text-slate-400 ml-2">{{ notification.time_ago }}</span>
                </div>
                <p class="text-sm text-slate-400 mt-1 line-clamp-2">{{ truncateMessage(notification.message) }}</p>
              </div>
              <button
                v-if="!notification.is_read"
                @click.stop="markAsRead(notification.id)"
                class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 transition-colors flex items-center justify-center text-xs"
                title="Mark as read"
              >
                ✓
              </button>
            </div>
          </div>
        </div>

        <div v-else class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500 mx-auto"></div>
          <p class="text-slate-400 mt-3">Loading...</p>
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

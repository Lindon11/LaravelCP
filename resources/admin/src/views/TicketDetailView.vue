<template>
  <div class="ticket-detail">
    <!-- Header -->
    <div class="page-header">
      <button class="back-btn" @click="goBack">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back
      </button>
      <span class="ticket-ref" v-if="ticket">Ticket #{{ ticket.id }}</span>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <span>Loading ticket...</span>
    </div>

    <!-- Not Found -->
    <div v-else-if="!ticket" class="error-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 8v4M12 16h.01"/>
      </svg>
      <h3>Ticket not found</h3>
      <p>This ticket may have been deleted</p>
      <button class="btn-primary" @click="goBack">Return to Tickets</button>
    </div>

    <!-- Content -->
    <div v-else class="ticket-layout">
      <!-- Main Content -->
      <div class="main-panel">
        <!-- Ticket Info Card -->
        <div class="info-card">
          <div class="info-header">
            <div class="info-title">
              <h2>{{ ticket.subject }}</h2>
              <span class="priority-tag" :class="ticket.priority">{{ ticket.priority }}</span>
            </div>
            <span class="status-tag" :class="ticket.status">{{ formatStatus(ticket.status) }}</span>
          </div>
          <div class="info-meta">
            <div class="meta-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              <span>{{ ticket.user?.username || 'Unknown' }}</span>
            </div>
            <div class="meta-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <path d="m22 6-10 7L2 6"/>
              </svg>
              <span>{{ ticket.user?.email || 'No email' }}</span>
            </div>
            <div class="meta-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <path d="M16 2v4M8 2v4M3 10h18"/>
              </svg>
              <span>{{ formatDateTime(ticket.created_at) }}</span>
            </div>
            <div class="meta-item" v-if="ticket.category">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
              </svg>
              <span>{{ ticket.category?.name }}</span>
            </div>
          </div>
        </div>

        <!-- Original Message -->
        <div class="message-card original">
          <div class="message-avatar user">
            {{ ticket.user?.username?.charAt(0)?.toUpperCase() || '?' }}
          </div>
          <div class="message-content">
            <div class="message-header">
              <span class="sender-name">{{ ticket.user?.username || 'User' }}</span>
              <span class="message-time">{{ formatDateTime(ticket.created_at) }}</span>
            </div>
            <div class="message-body">{{ ticket.description || ticket.message }}</div>
          </div>
        </div>

        <!-- Replies -->
        <div v-if="ticket.messages && ticket.messages.length > 0" class="replies-section">
          <h4 class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            {{ ticket.messages.length }} {{ ticket.messages.length === 1 ? 'Reply' : 'Replies' }}
          </h4>

          <div
            v-for="msg in ticket.messages"
            :key="msg.id"
            class="message-card"
            :class="{ admin: msg.is_admin || msg.is_staff_reply }"
          >
            <div class="message-avatar" :class="(msg.is_admin || msg.is_staff_reply) ? 'admin' : 'user'">
              {{ msg.user?.username?.charAt(0)?.toUpperCase() || '?' }}
            </div>
            <div class="message-content">
              <div class="message-header">
                <span class="sender-name">
                  {{ msg.user?.username || 'Unknown' }}
                  <span v-if="msg.is_admin || msg.is_staff_reply" class="staff-badge">Staff</span>
                </span>
                <span class="message-time">{{ formatDateTime(msg.created_at) }}</span>
              </div>
              <div class="message-body">{{ msg.message }}</div>
            </div>
          </div>
        </div>

        <!-- Reply Form -->
        <div v-if="ticket.status !== 'closed'" class="reply-form">
          <h4 class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Send Reply
          </h4>

          <!-- Quick Replies -->
          <div class="quick-replies-inline">
            <button v-for="(qr, i) in quickReplies" :key="i" @click="useQuickReply(qr.text)" class="quick-reply-btn">
              {{ qr.label }}
            </button>
          </div>

          <div class="form-group">
            <textarea
              v-model="replyMessage"
              placeholder="Type your response..."
              rows="4"
            ></textarea>
          </div>
          <div class="form-actions">
            <label class="checkbox-wrapper">
              <input type="checkbox" v-model="markAsAnswered">
              <span>Mark as answered</span>
            </label>
            <button class="btn-primary" @click="sendReply" :disabled="!replyMessage.trim() || sending">
              {{ sending ? 'Sending...' : 'Send Reply' }}
            </button>
          </div>
        </div>

        <!-- Closed Notice -->
        <div v-else class="closed-notice">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <p>This ticket is closed</p>
          <button class="btn-secondary" @click="reopenTicket">Reopen Ticket</button>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="side-panel">
        <!-- Status -->
        <div class="panel-card">
          <h5>Status</h5>
          <div class="button-grid">
            <button
              class="status-btn"
              :class="{ active: ticket.status === 'open', 'status-open': ticket.status === 'open' }"
              @click="ticket.status = 'open'; updateStatus()"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
              </svg>
              <span>Open</span>
            </button>
            <button
              class="status-btn"
              :class="{ active: ticket.status === 'waiting_response', 'status-waiting': ticket.status === 'waiting_response' }"
              @click="ticket.status = 'waiting_response'; updateStatus()"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
              </svg>
              <span>Waiting</span>
            </button>
            <button
              class="status-btn"
              :class="{ active: ticket.status === 'answered', 'status-answered': ticket.status === 'answered' }"
              @click="ticket.status = 'answered'; updateStatus()"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M3.505 2.365A41.369 41.369 0 019 2c1.863 0 3.697.124 5.495.365 1.247.167 2.18 1.108 2.435 2.268a4.45 4.45 0 00-.577-.069 43.141 43.141 0 00-4.706 0C9.229 4.696 7.5 6.727 7.5 8.998v2.24c0 1.413.67 2.735 1.76 3.562l-2.98 2.98A.75.75 0 015 17.25v-3.443c-.501-.048-1-.106-1.495-.172C2.033 13.438 1 12.162 1 10.72V5.28c0-1.441 1.033-2.717 2.505-2.914z" />
                <path d="M14 6c-.762 0-1.52.02-2.271.062C10.157 6.148 9 7.472 9 8.998v2.24c0 1.519 1.147 2.839 2.71 2.935.214.013.428.024.642.034.2.009.385.09.518.224l2.35 2.35a.75.75 0 001.28-.531v-2.07c1.453-.195 2.5-1.463 2.5-2.915V8.998c0-1.526-1.157-2.85-2.729-2.936A41.645 41.645 0 0014 6z" />
              </svg>
              <span>Answered</span>
            </button>
            <button
              class="status-btn"
              :class="{ active: ticket.status === 'closed', 'status-closed': ticket.status === 'closed' }"
              @click="ticket.status = 'closed'; updateStatus()"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
              </svg>
              <span>Closed</span>
            </button>
          </div>
        </div>

        <!-- Priority -->
        <div class="panel-card">
          <h5>Priority</h5>
          <div class="button-grid">
            <button
              class="priority-btn"
              :class="{ active: ticket.priority === 'low', 'priority-low': ticket.priority === 'low' }"
              @click="ticket.priority = 'low'; updatePriority()"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.638l3.96-4.158a.75.75 0 111.08 1.04l-5.25 5.5a.75.75 0 01-1.08 0l-5.25-5.5a.75.75 0 111.08-1.04l3.96 4.158V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
              </svg>
              <span>Low</span>
            </button>
            <button
              class="priority-btn"
              :class="{ active: ticket.priority === 'medium', 'priority-medium': ticket.priority === 'medium' }"
              @click="ticket.priority = 'medium'; updatePriority()"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
              </svg>
              <span>Medium</span>
            </button>
            <button
              class="priority-btn"
              :class="{ active: ticket.priority === 'high', 'priority-high': ticket.priority === 'high' }"
              @click="ticket.priority = 'high'; updatePriority()"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0110 17z" clip-rule="evenodd" />
              </svg>
              <span>High</span>
            </button>
            <button
              class="priority-btn"
              :class="{ active: ticket.priority === 'urgent', 'priority-urgent': ticket.priority === 'urgent' }"
              @click="ticket.priority = 'urgent'; updatePriority()"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
              </svg>
              <span>Urgent</span>
            </button>
          </div>
        </div>

        <!-- Assignee -->
        <div class="panel-card">
          <h5>Assigned To</h5>
          <div v-if="ticket.assigned_user" class="assignee-info">
            <div class="assignee-avatar">{{ ticket.assigned_user.username?.charAt(0)?.toUpperCase() }}</div>
            <span class="assignee-name">{{ ticket.assigned_user.username }}</span>
            <button class="btn-icon" @click="unassignTicket" title="Remove">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div v-else class="assignee-actions">
            <button class="btn-outline full" @click="assignToMe">
              Assign to Me
            </button>
            <button class="btn-outline full" @click="showAssignModal = true">
              Assign to Staff
            </button>
          </div>
        </div>

        <!-- Actions -->
        <div class="panel-card danger">
          <h5>Actions</h5>
          <button class="btn-danger full" @click="deleteTicket">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
            </svg>
            Delete Ticket
          </button>
        </div>
      </div>
    </div>

    <!-- Assignment Modal -->
    <div v-if="showAssignModal" class="modal-overlay" @click="showAssignModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Assign Ticket</h3>
          <button class="btn-icon" @click="showAssignModal = false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <path d="m21 21-4.35-4.35"/>
            </svg>
            <input
              v-model="staffSearch"
              @input="searchStaff"
              type="text"
              placeholder="Search staff by name or email..."
              class="search-input"
            />
          </div>
          <div v-if="loadingStaff" class="staff-loading">
            <div class="spinner"></div>
            <span>Searching...</span>
          </div>
          <div v-else-if="staffUsers.length === 0" class="staff-empty">
            <p>No staff members found</p>
          </div>
          <div v-else class="staff-list">
            <button
              v-for="user in staffUsers"
              :key="user.id"
              @click="assignToStaff(user)"
              class="staff-item"
            >
              <div class="staff-avatar">{{ user.username?.charAt(0)?.toUpperCase() }}</div>
              <div class="staff-info">
                <span class="staff-name">{{ user.username }}</span>
                <span class="staff-email">{{ user.email }}</span>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const loading = ref(true)
const ticket = ref(null)
const replyMessage = ref('')
const sending = ref(false)
const markAsAnswered = ref(true)
const currentUser = ref(null)
const showAssignModal = ref(false)
const staffUsers = ref([])
const staffSearch = ref('')
const loadingStaff = ref(false)
let searchTimeout = null

const quickReplies = [
  { label: 'Thanks for contacting', text: 'Thank you for contacting support. We have received your ticket and will assist you shortly.' },
  { label: 'Issue resolved', text: 'We have resolved the issue you reported. Please let us know if you need further assistance.' },
  { label: 'Need more info', text: 'To help investigate further, could you please provide additional details about the issue?' },
  { label: 'Working on it', text: 'Our team is currently investigating this issue. We will update you soon.' },
]

const fetchTicket = async () => {
  loading.value = true
  try {
    const response = await api.get(`/admin/support/tickets/${route.params.id}`)
    ticket.value = response.data.ticket || response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch ticket:', error)
    toast.error('Failed to load ticket')
    ticket.value = null
  } finally {
    loading.value = false
  }
}

const fetchCurrentUser = async () => {
  try {
    const response = await api.get('/user')
    currentUser.value = response.data
  } catch (error) {
    console.error('Failed to fetch user:', error)
  }
}

const goBack = () => router.push('/tickets')

const sendReply = async () => {
  if (!replyMessage.value.trim()) return
  sending.value = true

  try {
    await api.post(`/admin/support/tickets/${ticket.value.id}/reply`, {
      message: replyMessage.value
    })

    ticket.value.messages = ticket.value.messages || []
    ticket.value.messages.push({
      id: Date.now(),
      message: replyMessage.value,
      is_admin: true,
      user: currentUser.value,
      created_at: new Date().toISOString()
    })

    if (markAsAnswered.value && ticket.value.status !== 'answered') {
      ticket.value.status = 'answered'
      await api.patch(`/admin/support/tickets/${ticket.value.id}/status`, { status: 'answered' })
    }

    replyMessage.value = ''
    toast.success('Reply sent')
  } catch (error) {
    toast.error('Failed to send reply')
  } finally {
    sending.value = false
  }
}

const updateStatus = async () => {
  try {
    await api.patch(`/admin/support/tickets/${ticket.value.id}/status`, { status: ticket.value.status })
    toast.success('Status updated')
  } catch (error) {
    toast.error('Failed to update status')
    fetchTicket()
  }
}

const updatePriority = async () => {
  try {
    await api.patch(`/admin/support/tickets/${ticket.value.id}`, { priority: ticket.value.priority })
    toast.success('Priority updated')
  } catch (error) {
    toast.error('Failed to update priority')
    fetchTicket()
  }
}

const assignToMe = async () => {
  try {
    await api.patch(`/admin/support/tickets/${ticket.value.id}/assign`, { assigned_to: currentUser.value?.id })
    ticket.value.assigned_to = currentUser.value?.id
    ticket.value.assigned_user = currentUser.value
    toast.success('Ticket assigned to you')
  } catch (error) {
    toast.error('Failed to assign ticket')
  }
}

const unassignTicket = async () => {
  try {
    await api.patch(`/admin/support/tickets/${ticket.value.id}/assign`, { assigned_to: null })
    ticket.value.assigned_to = null
    ticket.value.assigned_user = null
    toast.success('Ticket unassigned')
  } catch (error) {
    toast.error('Failed to unassign ticket')
  }
}

const fetchStaffUsers = async (search = '') => {
  loadingStaff.value = true
  try {
    const response = await api.get('/admin/support/tickets/staff/users', {
      params: { search }
    })
    staffUsers.value = response.data.users || []
  } catch (error) {
    console.error('Failed to fetch staff:', error)
    toast.error('Failed to load staff members')
  } finally {
    loadingStaff.value = false
  }
}

const searchStaff = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchStaffUsers(staffSearch.value)
  }, 300)
}

const assignToStaff = async (user) => {
  try {
    await api.patch(`/admin/support/tickets/${ticket.value.id}/assign`, { assigned_to: user.id })
    ticket.value.assigned_to = user.id
    ticket.value.assigned_user = user
    showAssignModal.value = false
    toast.success(`Ticket assigned to ${user.username}`)
  } catch (error) {
    toast.error('Failed to assign ticket')
  }
}

const reopenTicket = async () => {
  ticket.value.status = 'open'
  await updateStatus()
}

const deleteTicket = async () => {
  if (!confirm('Delete this ticket permanently?')) return

  try {
    await api.delete(`/admin/support/tickets/${ticket.value.id}`)
    toast.success('Ticket deleted')
    router.push('/tickets')
  } catch (error) {
    toast.error('Failed to delete ticket')
  }
}

const useQuickReply = (text) => {
  replyMessage.value = text
}

const formatStatus = (status) => {
  const labels = { open: 'Open', waiting_response: 'Waiting', answered: 'Answered', closed: 'Closed' }
  return labels[status] || status
}

const formatDateTime = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: 'numeric', minute: '2-digit', hour12: true
  })
}

onMounted(() => {
  fetchTicket()
  fetchCurrentUser()
  fetchStaffUsers()
})
</script>

<style scoped>
.ticket-detail {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

/* Header */
.page-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: transparent;
  border: 1px solid #334155;
  border-radius: 8px;
  color: #94a3b8;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.back-btn svg {
  width: 18px;
  height: 18px;
}

.back-btn:hover {
  background: rgba(51, 65, 85, 0.5);
  color: #f8fafc;
}

.ticket-ref {
  font-size: 0.875rem;
  color: #64748b;
}

/* States */
.loading-state, .error-state {
  text-align: center;
  padding: 4rem;
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

.error-state svg {
  width: 48px;
  height: 48px;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.error-state h3 {
  font-size: 1.25rem;
  color: #f8fafc;
  margin: 0 0 0.5rem;
}

.error-state p {
  margin: 0 0 1.5rem;
}

/* Layout */
.ticket-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 1.5rem;
}

/* Main Panel */
.main-panel {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Info Card */
.info-card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 1.5rem;
}

.info-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.info-title {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.info-title h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #f8fafc;
  margin: 0;
  line-height: 1.4;
}

.priority-tag {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  flex-shrink: 0;
}

.priority-tag.urgent { background: rgba(239, 68, 68, 0.2); color: #f87171; }
.priority-tag.high { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
.priority-tag.medium { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
.priority-tag.low { background: rgba(34, 197, 94, 0.2); color: #4ade80; }

.status-tag {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-tag.open { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
.status-tag.waiting_response { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
.status-tag.answered { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
.status-tag.closed { background: rgba(100, 116, 139, 0.15); color: #94a3b8; }

.info-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.info-meta .meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #94a3b8;
}

.info-meta .meta-item svg {
  width: 16px;
  height: 16px;
  opacity: 0.6;
}

/* Message Cards */
.message-card {
  display: flex;
  gap: 1rem;
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 1.25rem;
}

.message-card.admin {
  background: rgba(59, 130, 246, 0.05);
  border-color: rgba(59, 130, 246, 0.2);
}

.message-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
  color: white;
  flex-shrink: 0;
}

.message-avatar.user {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
}

.message-avatar.admin {
  background: linear-gradient(135deg, #3b82f6, #0ea5e9);
}

.message-content {
  flex: 1;
  min-width: 0;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.sender-name {
  font-weight: 600;
  color: #f8fafc;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.staff-badge {
  font-size: 0.65rem;
  padding: 0.15rem 0.4rem;
  background: #3b82f6;
  border-radius: 4px;
  font-weight: 600;
}

.message-time {
  font-size: 0.75rem;
  color: #64748b;
}

.message-body {
  color: #e2e8f0;
  line-height: 1.6;
  white-space: pre-wrap;
  font-size: 0.9rem;
}

/* Replies Section */
.replies-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #94a3b8;
  margin: 0;
}

.section-title svg {
  width: 18px;
  height: 18px;
  opacity: 0.7;
}

/* Reply Form */
.reply-form {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 1.5rem;
}

.reply-form .section-title {
  margin-bottom: 1rem;
}

.form-group textarea {
  width: 100%;
  padding: 1rem;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid #334155;
  border-radius: 8px;
  color: #f8fafc;
  font-size: 0.9rem;
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
  transition: border-color 0.2s;
}

.form-group textarea:focus {
  outline: none;
  border-color: #3b82f6;
}

.form-group textarea::placeholder {
  color: #64748b;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #94a3b8;
  cursor: pointer;
}

.checkbox-wrapper input {
  accent-color: #3b82f6;
}

/* Closed Notice */
.closed-notice {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
}

.closed-notice svg {
  width: 32px;
  height: 32px;
  color: #64748b;
  margin-bottom: 0.75rem;
}

.closed-notice p {
  color: #94a3b8;
  margin: 0 0 1rem;
}

/* Side Panel */
.side-panel {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.panel-card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 1.25rem;
}

.panel-card.danger {
  border-color: rgba(239, 68, 68, 0.3);
}

.panel-card h5 {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 0 0.75rem;
}

.button-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
}

.status-btn, .priority-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
  padding: 0.875rem 0.5rem;
  background: rgba(30, 41, 59, 0.4);
  border: 2px solid transparent;
  border-radius: 10px;
  color: #64748b;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  min-height: 70px;
}

.status-btn svg, .priority-btn svg {
  width: 18px;
  height: 18px;
  opacity: 0.6;
  transition: all 0.2s;
}

.status-btn:hover, .priority-btn:hover {
  background: rgba(51, 65, 85, 0.6);
  transform: translateY(-1px);
}

.status-btn.active, .priority-btn.active {
  font-weight: 600;
  transform: scale(1.02);
}

.status-btn.active svg, .priority-btn.active svg {
  opacity: 1;
}

/* Status Colors */
.status-btn.status-open.active {
  background: rgba(59, 130, 246, 0.2);
  border-color: #3b82f6;
  color: #60a5fa;
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.15);
}

.status-btn.status-waiting.active {
  background: rgba(245, 158, 11, 0.2);
  border-color: #f59e0b;
  color: #fbbf24;
  box-shadow: 0 0 20px rgba(245, 158, 11, 0.15);
}

.status-btn.status-answered.active {
  background: rgba(34, 197, 94, 0.2);
  border-color: #22c55e;
  color: #4ade80;
  box-shadow: 0 0 20px rgba(34, 197, 94, 0.15);
}

.status-btn.status-closed.active {
  background: rgba(100, 116, 139, 0.25);
  border-color: #64748b;
  color: #94a3b8;
  box-shadow: 0 0 20px rgba(100, 116, 139, 0.15);
}

/* Priority Colors */
.priority-btn.priority-low.active {
  background: rgba(34, 197, 94, 0.2);
  border-color: #22c55e;
  color: #4ade80;
  box-shadow: 0 0 20px rgba(34, 197, 94, 0.15);
}

.priority-btn.priority-medium.active {
  background: rgba(59, 130, 246, 0.2);
  border-color: #3b82f6;
  color: #60a5fa;
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.15);
}

.priority-btn.priority-high.active {
  background: rgba(245, 158, 11, 0.2);
  border-color: #f59e0b;
  color: #fbbf24;
  box-shadow: 0 0 20px rgba(245, 158, 11, 0.15);
}

.priority-btn.priority-urgent.active {
  background: rgba(239, 68, 68, 0.2);
  border-color: #ef4444;
  color: #f87171;
  box-shadow: 0 0 20px rgba(239, 68, 68, 0.15);
}

.assignee-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.assignee-avatar {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.assignee-name {
  flex: 1;
  font-weight: 500;
  color: #f8fafc;
  font-size: 0.9rem;
}

.btn-icon {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  color: #64748b;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.btn-icon svg {
  width: 16px;
  height: 16px;
}

.quick-replies-inline {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.quick-reply-btn {
  padding: 0.5rem 0.875rem;
  background: rgba(51, 65, 85, 0.4);
  border: 1px solid #334155;
  border-radius: 6px;
  color: #94a3b8;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.quick-reply-btn:hover {
  background: rgba(59, 130, 246, 0.15);
  border-color: #3b82f6;
  color: #60a5fa;
  transform: translateY(-1px);
}

/* Buttons */
.btn-primary {
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  border: none;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: transparent;
  border: 1px solid #334155;
  border-radius: 8px;
  color: #f8fafc;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: rgba(51, 65, 85, 0.5);
}

.btn-outline {
  padding: 0.75rem 1rem;
  background: transparent;
  border: 1px solid #334155;
  border-radius: 8px;
  color: #94a3b8;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: rgba(59, 130, 246, 0.1);
  border-color: #3b82f6;
  color: #f8fafc;
}

.btn-danger {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: transparent;
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 8px;
  color: #f87171;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-danger:hover {
  background: rgba(239, 68, 68, 0.1);
  border-color: #ef4444;
}

.btn-danger svg {
  width: 16px;
  height: 16px;
}

.full {
  width: 100%;
}

.assignee-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-content {
  background: #1e293b;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 1rem;
  width: 100%;
  max-width: 500px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.modal-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: white;
  margin: 0;
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.search-box {
  position: relative;
  margin-bottom: 1rem;
}

.search-box svg {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: #94a3b8;
}

.search-input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  background: #0f172a;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.75rem;
  color: white;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.staff-loading, .staff-empty {
  text-align: center;
  padding: 3rem 1rem;
  color: #94a3b8;
}

.staff-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.staff-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-height: 400px;
  overflow-y: auto;
}

.staff-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.75rem;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s;
}

.staff-item:hover {
  background: rgba(15, 23, 42, 0.9);
  border-color: #f59e0b;
  transform: translateY(-1px);
}

.staff-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f59e0b, #ea580c);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  flex-shrink: 0;
}

.staff-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  flex: 1;
  min-width: 0;
}

.staff-name {
  color: white;
  font-weight: 500;
  font-size: 0.9375rem;
}

.staff-email {
  color: #94a3b8;
  font-size: 0.8125rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

@media (max-width: 900px) {
  .ticket-layout {
    grid-template-columns: 1fr;
  }

  .side-panel {
    order: -1;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}

@media (max-width: 600px) {
  .ticket-detail {
    padding: 1rem;
  }

  .info-header {
    flex-direction: column;
    gap: 1rem;
  }

  .info-meta {
    flex-direction: column;
    gap: 0.75rem;
  }

  .form-actions {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
}
</style>

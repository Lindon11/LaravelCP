<template>
  <div class="relative">
    <!-- Chat Button -->
    <button
      @click="toggleChat"
      class="relative p-2.5 rounded-xl bg-slate-800/50 hover:bg-slate-700/50 border border-slate-700/50 text-slate-400 hover:text-white transition-all"
    >
      <ChatBubbleLeftRightIcon class="w-5 h-5" />
      <!-- Unread Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs font-bold flex items-center justify-center shadow-lg shadow-amber-500/30"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
      <!-- Online Indicator -->
      <span class="absolute bottom-0.5 right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-slate-800"></span>
    </button>

    <!-- Chat Panel -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-2 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-2 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 top-full mt-2 w-96 rounded-2xl bg-slate-800 border border-slate-700/50 shadow-2xl shadow-black/30 overflow-hidden z-50"
      >
        <!-- Header -->
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-slate-800 to-slate-700/50 border-b border-slate-700/50">
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-amber-500/20">
              <ChatBubbleLeftRightIcon class="w-5 h-5 text-amber-400" />
            </div>
            <div>
              <h3 class="font-semibold text-white">Staff Chat</h3>
              <p class="text-xs text-slate-400">{{ onlineStaff.length }} staff online</p>
            </div>
          </div>
          <button
            @click="isOpen = false"
            class="p-1.5 rounded-lg hover:bg-slate-700/50 text-slate-400 hover:text-white transition-colors"
          >
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Online Staff -->
        <div class="p-3 border-b border-slate-700/50 bg-slate-800/50">
          <p class="text-xs font-medium text-slate-500 uppercase mb-2">Online Now</p>
          <div class="flex flex-wrap gap-2">
            <div
              v-for="staff in onlineStaff"
              :key="staff.id"
              class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-slate-700/50 hover:bg-slate-700 cursor-pointer transition-colors"
              @click="startDirectMessage(staff)"
            >
              <div class="relative">
                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-xs font-bold">
                  {{ staff.username.charAt(0).toUpperCase() }}
                </div>
                <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-500 border border-slate-700"></span>
              </div>
              <span class="text-sm text-slate-300">{{ staff.username }}</span>
            </div>
            <div v-if="onlineStaff.length === 0" class="text-sm text-slate-500 italic">
              No other staff online
            </div>
          </div>
        </div>

        <!-- Messages -->
        <div ref="messagesContainer" class="h-64 overflow-y-auto p-4 space-y-3">
          <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-center">
            <ChatBubbleLeftRightIcon class="w-12 h-12 text-slate-600 mb-3" />
            <p class="text-slate-500 text-sm">No messages yet</p>
            <p class="text-slate-600 text-xs">Start a conversation with your team</p>
          </div>

          <div
            v-for="message in messages"
            :key="message.id"
            :class="[
              'flex gap-3',
              message.user_id === currentUserId ? 'flex-row-reverse' : ''
            ]"
          >
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
              {{ message.username.charAt(0).toUpperCase() }}
            </div>
            <div :class="[
              'max-w-[70%] rounded-2xl px-4 py-2',
              message.user_id === currentUserId
                ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white'
                : 'bg-slate-700/50 text-slate-200'
            ]">
              <p class="text-xs font-medium mb-1" :class="message.user_id === currentUserId ? 'text-amber-100' : 'text-slate-400'">
                {{ message.username }}
              </p>
              <p class="text-sm">{{ message.content }}</p>
              <p class="text-xs mt-1 opacity-60">{{ formatTime(message.created_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="p-4 border-t border-slate-700/50 bg-slate-800/50">
          <form @submit.prevent="sendMessage" class="flex gap-2">
            <input
              v-model="newMessage"
              type="text"
              placeholder="Type a message..."
              class="flex-1 px-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all text-sm"
            />
            <button
              type="submit"
              :disabled="!newMessage.trim()"
              class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <PaperAirplaneIcon class="w-5 h-5" />
            </button>
          </form>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import api from '@/services/api'
import {
  ChatBubbleLeftRightIcon,
  XMarkIcon,
  PaperAirplaneIcon
} from '@heroicons/vue/24/outline'

const isOpen = ref(false)
const messages = ref([])
const newMessage = ref('')
const onlineStaff = ref([])
const unreadCount = ref(0)
const messagesContainer = ref(null)
const currentUserId = ref(null)
let pollInterval = null

const toggleChat = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    unreadCount.value = 0
    fetchMessages()
    scrollToBottom()
  }
}

const fetchMessages = async () => {
  try {
    const response = await api.get('/admin/staff-chat/messages')
    messages.value = response.data.messages || []
    onlineStaff.value = response.data.online_staff || []
    currentUserId.value = response.data.current_user_id
    scrollToBottom()
  } catch (error) {
    console.error('Failed to fetch staff chat messages:', error)
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim()) return

  const content = newMessage.value.trim()
  newMessage.value = ''

  try {
    await api.post('/admin/staff-chat/messages', { content })
    await fetchMessages()
  } catch (error) {
    console.error('Failed to send message:', error)
    newMessage.value = content // Restore message on error
  }
}

const startDirectMessage = (staff) => {
  newMessage.value = `@${staff.username} `
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diff = now - date

  if (diff < 60000) return 'Just now'
  if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`
  if (diff < 86400000) return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  return date.toLocaleDateString([], { month: 'short', day: 'numeric' })
}

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const checkNewMessages = async () => {
  if (isOpen.value) return // Don't increment if chat is open

  try {
    const response = await api.get('/admin/staff-chat/unread')
    unreadCount.value = response.data.count || 0
  } catch (error) {
    // Silent fail
  }
}

watch(isOpen, (newVal) => {
  if (newVal) {
    scrollToBottom()
  }
})

onMounted(() => {
  fetchMessages()
  // Poll for new messages every 10 seconds
  pollInterval = setInterval(() => {
    if (isOpen.value) {
      fetchMessages()
    } else {
      checkNewMessages()
    }
  }, 10000)
})

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval)
  }
})
</script>

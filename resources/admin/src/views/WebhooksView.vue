<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white flex items-center gap-3">
          <div class="p-3 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600">
            <BoltIcon class="w-6 h-6 text-white" />
          </div>
          Webhooks
        </h1>
        <p class="text-sm text-slate-400 mt-1">Manage outgoing webhook integrations for Discord, Slack, and custom endpoints</p>
      </div>
      <button
        @click="openCreateModal"
        class="px-4 py-2 bg-gradient-to-r from-violet-500 to-purple-600 text-white rounded-xl font-medium hover:from-violet-600 hover:to-purple-700 transition-all shadow-lg shadow-violet-500/25 flex items-center gap-2"
      >
        <PlusIcon class="w-5 h-5" />
        Create Webhook
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-4">
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-lg bg-violet-500/20">
            <BoltIcon class="w-5 h-5 text-violet-400" />
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
            <p class="text-xs text-slate-400">Total Webhooks</p>
          </div>
        </div>
      </div>
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-4">
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-lg bg-green-500/20">
            <CheckCircleIcon class="w-5 h-5 text-green-400" />
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ stats.active }}</p>
            <p class="text-xs text-slate-400">Active</p>
          </div>
        </div>
      </div>
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-4">
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-lg bg-blue-500/20">
            <PaperAirplaneIcon class="w-5 h-5 text-blue-400" />
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ stats.deliveries_today }}</p>
            <p class="text-xs text-slate-400">Deliveries Today</p>
          </div>
        </div>
      </div>
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-4">
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-lg bg-red-500/20">
            <ExclamationCircleIcon class="w-5 h-5 text-red-400" />
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ stats.failed_today }}</p>
            <p class="text-xs text-slate-400">Failed Today</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Webhooks List -->
    <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 overflow-hidden">
      <div class="p-4 border-b border-slate-700/50">
        <div class="flex items-center gap-4">
          <div class="relative flex-1 max-w-md">
            <MagnifyingGlassIcon class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" />
            <input
              v-model="search"
              type="text"
              placeholder="Search webhooks..."
              class="w-full pl-10 pr-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50"
            >
          </div>
        </div>
      </div>

      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-violet-500 mx-auto"></div>
        <p class="text-slate-400 mt-2">Loading webhooks...</p>
      </div>

      <div v-else-if="filteredWebhooks.length === 0" class="p-8 text-center">
        <BoltIcon class="w-12 h-12 text-slate-600 mx-auto mb-3" />
        <p class="text-slate-400">No webhooks found</p>
      </div>

      <div v-else class="divide-y divide-slate-700/50">
        <div
          v-for="webhook in filteredWebhooks"
          :key="webhook.id"
          class="p-4 hover:bg-slate-700/20 transition-colors"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div :class="[
                'p-2 rounded-lg',
                webhook.is_active ? 'bg-green-500/20' : 'bg-slate-600/20'
              ]">
                <BoltIcon :class="[
                  'w-5 h-5',
                  webhook.is_active ? 'text-green-400' : 'text-slate-400'
                ]" />
              </div>
              <div>
                <h3 class="font-medium text-white">{{ webhook.name }}</h3>
                <p class="text-sm text-slate-400 truncate max-w-md">{{ webhook.url }}</p>
                <div class="flex items-center gap-2 mt-1">
                  <span
                    v-for="event in webhook.events.slice(0, 3)"
                    :key="event"
                    class="px-2 py-0.5 text-xs rounded-full bg-violet-500/20 text-violet-300"
                  >
                    {{ event }}
                  </span>
                  <span v-if="webhook.events.length > 3" class="text-xs text-slate-400">
                    +{{ webhook.events.length - 3 }} more
                  </span>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span :class="[
                'px-2 py-1 text-xs rounded-full',
                webhook.is_active 
                  ? 'bg-green-500/20 text-green-400' 
                  : 'bg-slate-600/20 text-slate-400'
              ]">
                {{ webhook.is_active ? 'Active' : 'Inactive' }}
              </span>
              <span v-if="webhook.last_response_code" :class="[
                'px-2 py-1 text-xs rounded-full',
                webhook.last_response_code >= 200 && webhook.last_response_code < 300
                  ? 'bg-green-500/20 text-green-400'
                  : 'bg-red-500/20 text-red-400'
              ]">
                {{ webhook.last_response_code }}
              </span>
              <button
                @click="testWebhook(webhook)"
                class="p-2 text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-lg transition-colors"
                title="Test webhook"
              >
                <PaperAirplaneIcon class="w-5 h-5" />
              </button>
              <button
                @click="toggleWebhook(webhook)"
                class="p-2 text-slate-400 hover:text-amber-400 hover:bg-amber-500/10 rounded-lg transition-colors"
                title="Toggle status"
              >
                <ArrowPathIcon v-if="webhook.toggling" class="w-5 h-5 animate-spin" />
                <PlayIcon v-else-if="!webhook.is_active" class="w-5 h-5" />
                <PauseIcon v-else class="w-5 h-5" />
              </button>
              <button
                @click="viewDeliveries(webhook)"
                class="p-2 text-slate-400 hover:text-violet-400 hover:bg-violet-500/10 rounded-lg transition-colors"
                title="View deliveries"
              >
                <ClockIcon class="w-5 h-5" />
              </button>
              <button
                @click="editWebhook(webhook)"
                class="p-2 text-slate-400 hover:text-white hover:bg-slate-600/50 rounded-lg transition-colors"
              >
                <PencilIcon class="w-5 h-5" />
              </button>
              <button
                @click="confirmDelete(webhook)"
                class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
      <div class="relative w-full max-w-2xl rounded-2xl bg-slate-800 border border-slate-700/50 p-6 shadow-xl">
        <h3 class="text-xl font-semibold text-white mb-4 flex items-center gap-3">
          <BoltIcon class="w-6 h-6 text-violet-400" />
          {{ editingWebhook ? 'Edit Webhook' : 'Create Webhook' }}
        </h3>

                <form @submit.prevent="saveWebhook" class="space-y-4">
                  <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-slate-300">Name</label>
                      <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Discord Notifications"
                        class="w-full px-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50"
                      >
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-slate-300">Retry Count</label>
                      <input
                        v-model.number="form.retry_count"
                        type="number"
                        min="0"
                        max="10"
                        class="w-full px-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50"
                      >
                    </div>
                  </div>

                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Webhook URL</label>
                    <input
                      v-model="form.url"
                      type="url"
                      required
                      placeholder="https://discord.com/api/webhooks/..."
                      class="w-full px-4 py-2 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50"
                    >
                  </div>

                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Events</label>
                    <div class="max-h-48 overflow-y-auto bg-slate-900/50 border border-slate-600/50 rounded-xl p-3 space-y-2">
                      <label class="flex items-center gap-2 cursor-pointer">
                        <input
                          type="checkbox"
                          :checked="form.events.includes('*')"
                          @change="toggleAllEvents"
                          class="rounded border-slate-600 bg-slate-800 text-violet-500 focus:ring-violet-500"
                        >
                        <span class="text-sm text-white font-medium">All Events (*)</span>
                      </label>
                      <div class="border-t border-slate-700/50 my-2"></div>
                      <div class="grid grid-cols-2 gap-2">
                        <label
                          v-for="event in availableEvents"
                          :key="event"
                          class="flex items-center gap-2 cursor-pointer"
                        >
                          <input
                            type="checkbox"
                            :value="event"
                            v-model="form.events"
                            :disabled="form.events.includes('*')"
                            class="rounded border-slate-600 bg-slate-800 text-violet-500 focus:ring-violet-500"
                          >
                          <span class="text-sm text-slate-300">{{ event }}</span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="space-y-2">
                    <label class="flex items-center justify-between">
                      <span class="text-sm font-medium text-slate-300">Active</span>
                      <button
                        type="button"
                        @click="form.is_active = !form.is_active"
                        :class="[
                          'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                          form.is_active ? 'bg-violet-500' : 'bg-slate-600'
                        ]"
                      >
                        <span :class="[
                          'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                          form.is_active ? 'translate-x-6' : 'translate-x-1'
                        ]" />
                      </button>
                    </label>
                  </div>

                  <div class="flex justify-end gap-3 pt-4">
                    <button
                      type="button"
                      @click="closeModal"
                      class="px-4 py-2 text-slate-300 hover:text-white transition-colors"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="saving"
                      class="px-4 py-2 bg-gradient-to-r from-violet-500 to-purple-600 text-white rounded-xl font-medium hover:from-violet-600 hover:to-purple-700 transition-all disabled:opacity-50"
                    >
                      {{ saving ? 'Saving...' : (editingWebhook ? 'Update' : 'Create') }}
                    </button>
                  </div>
                </form>
      </div>
    </div>

    <!-- Deliveries Modal -->
    <div v-if="showDeliveriesModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showDeliveriesModal = false"></div>
      <div class="relative w-full max-w-4xl rounded-2xl bg-slate-800 border border-slate-700/50 p-6 shadow-xl">
        <h3 class="text-xl font-semibold text-white mb-4 flex items-center gap-3">
          <ClockIcon class="w-6 h-6 text-violet-400" />
          Delivery History
        </h3>

                <div v-if="loadingDeliveries" class="p-8 text-center">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-violet-500 mx-auto"></div>
                </div>

                <div v-else-if="deliveries.length === 0" class="p-8 text-center text-slate-400">
                  No deliveries yet
                </div>

                <div v-else class="max-h-96 overflow-y-auto space-y-2">
                  <div
                    v-for="delivery in deliveries"
                    :key="delivery.id"
                    class="p-3 bg-slate-900/50 rounded-xl"
                  >
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-3">
                        <div :class="[
                          'p-1.5 rounded-lg',
                          delivery.response_code >= 200 && delivery.response_code < 300
                            ? 'bg-green-500/20'
                            : 'bg-red-500/20'
                        ]">
                          <CheckCircleIcon v-if="delivery.response_code >= 200 && delivery.response_code < 300" class="w-4 h-4 text-green-400" />
                          <ExclamationCircleIcon v-else class="w-4 h-4 text-red-400" />
                        </div>
                        <div>
                          <p class="text-sm font-medium text-white">{{ delivery.event }}</p>
                          <p class="text-xs text-slate-400">{{ formatDate(delivery.created_at) }}</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">{{ delivery.response_time_ms }}ms</span>
                        <span :class="[
                          'px-2 py-0.5 text-xs rounded-full',
                          delivery.response_code >= 200 && delivery.response_code < 300
                            ? 'bg-green-500/20 text-green-400'
                            : 'bg-red-500/20 text-red-400'
                        ]">
                          {{ delivery.response_code || 'Failed' }}
                        </span>
                        <button
                          v-if="delivery.response_code < 200 || delivery.response_code >= 300"
                          @click="retryDelivery(delivery)"
                          class="p-1 text-slate-400 hover:text-violet-400 transition-colors"
                          title="Retry"
                        >
                          <ArrowPathIcon class="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                    <div v-if="delivery.error" class="mt-2 p-2 bg-red-500/10 rounded text-xs text-red-300">
                      {{ delivery.error }}
                    </div>
                  </div>
                </div>

        <div class="flex justify-end mt-4">
          <button
            @click="showDeliveriesModal = false"
            class="px-4 py-2 text-slate-300 hover:text-white transition-colors"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  BoltIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  PaperAirplaneIcon,
  ClockIcon,
  ArrowPathIcon,
  PlayIcon,
  PauseIcon
} from '@heroicons/vue/24/outline'
import api from '../services/api'

const webhooks = ref([])
const loading = ref(true)
const saving = ref(false)
const search = ref('')
const showModal = ref(false)
const showDeliveriesModal = ref(false)
const editingWebhook = ref(null)
const deliveries = ref([])
const loadingDeliveries = ref(false)
const selectedWebhook = ref(null)
const availableEvents = ref([])

const stats = ref({
  total: 0,
  active: 0,
  deliveries_today: 0,
  failed_today: 0
})

const form = ref({
  name: '',
  url: '',
  events: [],
  is_active: true,
  retry_count: 3
})

const filteredWebhooks = computed(() => {
  if (!search.value) return webhooks.value
  const s = search.value.toLowerCase()
  return webhooks.value.filter(w => 
    w.name.toLowerCase().includes(s) || 
    w.url.toLowerCase().includes(s)
  )
})

onMounted(async () => {
  await Promise.all([loadWebhooks(), loadEvents()])
})

async function loadWebhooks() {
  loading.value = true
  try {
    const response = await api.get('/admin/webhooks')
    webhooks.value = response.data.data || response.data
    calculateStats()
  } catch (error) {
    console.error('Failed to load webhooks:', error)
  } finally {
    loading.value = false
  }
}

async function loadEvents() {
  try {
    const response = await api.get('/admin/webhooks/events')
    availableEvents.value = response.data.events
  } catch (error) {
    console.error('Failed to load events:', error)
  }
}

function calculateStats() {
  stats.value.total = webhooks.value.length
  stats.value.active = webhooks.value.filter(w => w.is_active).length
}

function openCreateModal() {
  editingWebhook.value = null
  form.value = {
    name: '',
    url: '',
    events: [],
    is_active: true,
    retry_count: 3
  }
  showModal.value = true
}

function editWebhook(webhook) {
  editingWebhook.value = webhook
  form.value = {
    name: webhook.name,
    url: webhook.url,
    events: [...webhook.events],
    is_active: webhook.is_active,
    retry_count: webhook.retry_count
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingWebhook.value = null
}

async function saveWebhook() {
  saving.value = true
  try {
    if (editingWebhook.value) {
      await api.patch(`/admin/webhooks/${editingWebhook.value.id}`, form.value)
    } else {
      await api.post('/admin/webhooks', form.value)
    }
    await loadWebhooks()
    closeModal()
  } catch (error) {
    console.error('Failed to save webhook:', error)
    alert(error.response?.data?.message || 'Failed to save webhook')
  } finally {
    saving.value = false
  }
}

async function toggleWebhook(webhook) {
  webhook.toggling = true
  try {
    await api.post(`/admin/webhooks/${webhook.id}/toggle`)
    webhook.is_active = !webhook.is_active
    calculateStats()
  } catch (error) {
    console.error('Failed to toggle webhook:', error)
  } finally {
    webhook.toggling = false
  }
}

async function testWebhook(webhook) {
  try {
    const response = await api.post(`/admin/webhooks/${webhook.id}/test`)
    alert(response.data.message)
  } catch (error) {
    alert('Test failed: ' + (error.response?.data?.message || error.message))
  }
}

async function viewDeliveries(webhook) {
  selectedWebhook.value = webhook
  showDeliveriesModal.value = true
  loadingDeliveries.value = true
  try {
    const response = await api.get(`/admin/webhooks/${webhook.id}/deliveries`)
    deliveries.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to load deliveries:', error)
  } finally {
    loadingDeliveries.value = false
  }
}

async function retryDelivery(delivery) {
  try {
    await api.post(`/admin/webhooks/${selectedWebhook.value.id}/deliveries/${delivery.id}/retry`)
    await viewDeliveries(selectedWebhook.value)
  } catch (error) {
    console.error('Failed to retry delivery:', error)
  }
}

async function confirmDelete(webhook) {
  if (!confirm(`Delete webhook "${webhook.name}"?`)) return
  try {
    await api.delete(`/admin/webhooks/${webhook.id}`)
    await loadWebhooks()
  } catch (error) {
    console.error('Failed to delete webhook:', error)
  }
}

function toggleAllEvents() {
  if (form.value.events.includes('*')) {
    form.value.events = []
  } else {
    form.value.events = ['*']
  }
}

function formatDate(date) {
  return new Date(date).toLocaleString()
}
</script>

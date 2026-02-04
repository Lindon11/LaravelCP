<template>
  <div class="space-y-6">
    <!-- Stats Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-slate-800/50 backdrop-blur-sm rounded-2xl border border-slate-700/50 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-400">Total Bans</p>
            <p class="text-2xl font-bold text-white mt-1">{{ stats.total }}</p>
          </div>
          <div class="p-3 rounded-xl bg-red-500/20">
            <NoSymbolIcon class="w-6 h-6 text-red-400" />
          </div>
        </div>
      </div>
      <div class="bg-slate-800/50 backdrop-blur-sm rounded-2xl border border-slate-700/50 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-400">Permanent Bans</p>
            <p class="text-2xl font-bold text-white mt-1">{{ stats.permanent }}</p>
          </div>
          <div class="p-3 rounded-xl bg-slate-700/50">
            <LockClosedIcon class="w-6 h-6 text-slate-400" />
          </div>
        </div>
      </div>
      <div class="bg-slate-800/50 backdrop-blur-sm rounded-2xl border border-slate-700/50 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-400">Temporary Bans</p>
            <p class="text-2xl font-bold text-white mt-1">{{ stats.temporary }}</p>
          </div>
          <div class="p-3 rounded-xl bg-amber-500/20">
            <ClockIcon class="w-6 h-6 text-amber-400" />
          </div>
        </div>
      </div>
    </div>

    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="relative w-full sm:w-80">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by IP address..."
          @input="debouncedSearch"
          class="w-full pl-10 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
        />
      </div>
      <button
        @click="showCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-xl font-medium shadow-lg shadow-red-500/20 transition-all"
      >
        <PlusIcon class="w-5 h-5" />
        Ban IP Address
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-12 h-12 border-4 border-red-500/30 border-t-red-500 rounded-full animate-spin mb-4"></div>
        <p class="text-slate-400">Loading bans...</p>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="bans.length === 0" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 flex items-center justify-center mb-4">
          <ShieldCheckIcon class="w-8 h-8 text-emerald-400" />
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">No IP bans</h3>
        <p class="text-slate-400 text-center max-w-sm">All clear! No IP addresses are currently banned.</p>
      </div>
    </div>

    <!-- Bans Table -->
    <div v-else class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-700/50 border-b border-slate-600/50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">IP Address</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Reason</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Banned At</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Expires</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/50">
            <tr v-for="ban in bans" :key="ban.id" class="hover:bg-slate-700/25 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-lg bg-red-500/20 flex items-center justify-center">
                    <GlobeAltIcon class="w-5 h-5 text-red-400" />
                  </div>
                  <span class="font-mono text-sm text-white">{{ ban.ip_address }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-slate-300 max-w-xs truncate">{{ ban.reason }}</p>
              </td>
              <td class="px-6 py-4 text-sm text-slate-400">{{ formatDate(ban.created_at) }}</td>
              <td class="px-6 py-4">
                <span :class="[
                  'inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium',
                  ban.expires_at ? 'bg-amber-500/20 text-amber-400' : 'bg-red-500/20 text-red-400'
                ]">
                  {{ ban.expires_at ? formatDate(ban.expires_at) : 'Permanent' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button @click="editBan(ban)" class="p-2 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-700/50 transition-colors">
                    <PencilIcon class="w-4 h-4" />
                  </button>
                  <button @click="deleteBan(ban)" class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
          <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between p-6 border-b border-slate-700">
              <h2 class="text-lg font-bold text-white">{{ editingItem ? 'Edit IP Ban' : 'Ban IP Address' }}</h2>
              <button @click="closeModal" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

            <div class="p-6 space-y-5">
              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">IP Address *</label>
                <input
                  v-model="formData.ip_address"
                  type="text"
                  placeholder="192.168.1.1 or 192.168.1.*"
                  class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500/50 transition-all font-mono"
                />
                <p class="text-xs text-slate-500">Use * for wildcard matching</p>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Reason *</label>
                <textarea
                  v-model="formData.reason"
                  rows="3"
                  placeholder="Reason for banning this IP..."
                  class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500/50 transition-all resize-none"
                />
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Expires At</label>
                <input
                  v-model="formData.expires_at"
                  type="datetime-local"
                  class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500/50 transition-all"
                />
                <p class="text-xs text-slate-500">Leave empty for permanent ban</p>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 p-6 border-t border-slate-700 bg-slate-800/50">
              <button @click="closeModal" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl font-medium transition-colors">
                Cancel
              </button>
              <button
                @click="saveBan"
                :disabled="saving"
                class="px-4 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-xl font-medium shadow-lg shadow-red-500/20 transition-all disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : (editingItem ? 'Update Ban' : 'Ban IP') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import {
  MagnifyingGlassIcon, PlusIcon, XMarkIcon, PencilIcon, TrashIcon,
  NoSymbolIcon, LockClosedIcon, ClockIcon, ShieldCheckIcon, GlobeAltIcon
} from '@heroicons/vue/24/outline'

const toast = useToast()
const bans = ref([])
const loading = ref(false)
const searchQuery = ref('')
const showModal = ref(false)
const editingItem = ref(null)
const saving = ref(false)
let searchTimeout = null

const formData = ref({ ip_address: '', reason: '', expires_at: null })

const stats = computed(() => ({
  total: bans.value.length,
  permanent: bans.value.filter(b => !b.expires_at).length,
  temporary: bans.value.filter(b => b.expires_at).length
}))

onMounted(() => fetchBans())

const fetchBans = async () => {
  loading.value = true
  try {
    const params = { search: searchQuery.value }
    const response = await api.get('/admin/ip-bans', { params })
    bans.value = response.data.data || response.data
  } catch (err) {
    toast.error('Failed to load IP bans')
  } finally {
    loading.value = false
  }
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchBans, 300)
}

const showCreateModal = () => { editingItem.value = null; formData.value = { ip_address: '', reason: '', expires_at: null }; showModal.value = true }
const editBan = (item) => { editingItem.value = item; formData.value = { ...item }; showModal.value = true }
const closeModal = () => { showModal.value = false; editingItem.value = null }

const saveBan = async () => {
  if (!formData.value.ip_address || !formData.value.reason) { toast.error('IP address and reason are required'); return }
  saving.value = true
  try {
    if (editingItem.value) {
      await api.patch(`/admin/ip-bans/${editingItem.value.id}`, formData.value)
      toast.success('IP ban updated')
    } else {
      await api.post('/admin/ip-bans', formData.value)
      toast.success('IP address banned')
    }
    closeModal(); fetchBans()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save ban')
  } finally {
    saving.value = false
  }
}

const deleteBan = async (item) => {
  if (!confirm(`Remove ban for ${item.ip_address}?`)) return
  try {
    await api.delete(`/admin/ip-bans/${item.id}`)
    toast.success('IP ban removed')
    fetchBans()
  } catch (err) {
    toast.error('Failed to remove ban')
  }
}

const formatDate = (dateStr) => dateStr ? new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A'
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>

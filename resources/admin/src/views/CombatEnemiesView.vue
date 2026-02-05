<template>
  <div class="space-y-6">
    <!-- Location & Area Selectors -->
    <div class="p-6 bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-2xl">
      <div class="grid grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Combat Location:</label>
          <select v-model="selectedLocationId" @change="loadAreas" class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            <option :value="null">-- Select Location --</option>
            <option v-for="location in locations" :key="location.id" :value="location.id">
              {{ location.icon }} {{ location.name }}
            </option>
          </select>
        </div>
        <div v-if="selectedLocationId">
          <label class="block text-sm font-medium text-gray-300 mb-2">Combat Area:</label>
          <select v-model="selectedAreaId" @change="onAreaChange" class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            <option :value="null">-- Select Area --</option>
            <option v-for="area in areas" :key="area.id" :value="area.id">
              {{ area.name }} ({{ '💀'.repeat(area.difficulty_level || area.difficulty || 1) }})
            </option>
          </select>
        </div>
      </div>
    </div>

    <template v-if="selectedAreaId">
      <!-- Search & Actions -->
      <div class="flex items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <input v-model="searchQuery" type="text" placeholder="Search enemies..." @input="debouncedSearch" class="w-full pl-10 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all" />
        </div>
        <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg shadow-amber-500/25">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Create Enemy
        </button>
      </div>

      <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <TableSkeleton :rows="5" :columns="10" />
      </div>

      <div v-else-if="error" class="rounded-2xl bg-red-500/10 border border-red-500/30 p-6">
        <div class="flex items-center gap-3">
          <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
          <p class="text-red-400 font-medium">{{ error }}</p>
        </div>
      </div>

      <div v-else class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-700/50 border-b border-slate-600/50">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">👤</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Name</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Level</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">HP</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Strength</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">XP</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Cash</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Spawn %</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Status</th>
                <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
              <tr v-for="item in items" :key="item.id" class="hover:bg-slate-700/25 transition-colors">
                <td class="px-6 py-4 text-sm text-slate-300">{{ item.icon }}</td>
                <td class="px-6 py-4 text-sm text-slate-300">{{ item.name }}</td>
                <td class="px-6 py-4 text-sm text-slate-300">{{ item.level }}</td>
                <td class="px-6 py-4 text-sm text-slate-300">{{ item.health }}</td>
                <td class="px-6 py-4 text-sm text-slate-300">{{ item.strength }}</td>
                <td class="px-6 py-4 text-sm text-slate-300">{{ item.experience_reward }}</td>
                <td class="px-6 py-4 text-sm text-slate-300">${{ item.cash_reward_min }}-${{ item.cash_reward_max }}</td>
                <td class="px-6 py-4 text-sm text-slate-300">{{ ((item.spawn_rate || 0) * 100).toFixed(0) }}%</td>
                <td class="px-6 py-4 text-sm text-slate-300">{{ item.active ? '🟢' : '🔴' }}</td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <button @click="openEdit(item)" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 transition-colors">Edit</button>
                    <button @click="deleteItem(item)" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors">Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="pagination" class="flex items-center justify-between">
        <p class="text-sm text-slate-400">Showing {{ pagination.from || 1 }} to {{ pagination.to || pagination.total }} of {{ pagination.total }} results</p>
        <div class="flex items-center gap-2">
          <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" :class="['px-3 py-1.5 rounded-lg text-sm font-medium transition-colors', pagination.current_page === 1 ? 'bg-slate-800 text-slate-600 cursor-not-allowed' : 'bg-slate-700 text-slate-300 hover:bg-slate-600']">Previous</button>
          <span class="text-sm text-slate-400 px-3">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
          <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" :class="['px-3 py-1.5 rounded-lg text-sm font-medium transition-colors', pagination.current_page === pagination.last_page ? 'bg-slate-800 text-slate-600 cursor-not-allowed' : 'bg-slate-700 text-slate-300 hover:bg-slate-600']">Next</button>
        </div>
      </div>
    </template>

    <div v-else class="rounded-2xl bg-slate-800/30 border-2 border-dashed border-slate-700/50 p-12 text-center">
      <p class="text-slate-400 text-lg">👆 Please select a combat location and area to manage enemies</p>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
        <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <div class="sticky top-0 bg-slate-800 border-b border-slate-700 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">{{ editingItem ? 'Edit' : 'Create' }} Combat Enemy</h2>
            <button @click="closeModal" class="text-slate-400 hover:text-white transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
                <input v-model="formData.name" type="text" required placeholder="e.g. Street Thug" class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Icon (Emoji)</label>
                <input v-model="formData.icon" type="text" placeholder="👤" maxlength="10" class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                <textarea v-model="formData.description" rows="2" class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Level *</label>
                <input v-model.number="formData.level" type="number" min="1" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Health *</label>
                <input v-model.number="formData.health" type="number" min="1" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Max Health *</label>
                <input v-model.number="formData.max_health" type="number" min="1" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Strength *</label>
                <input v-model.number="formData.strength" type="number" min="0" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Defense *</label>
                <input v-model.number="formData.defense" type="number" min="0" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Speed *</label>
                <input v-model.number="formData.speed" type="number" min="0" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Agility *</label>
                <input v-model.number="formData.agility" type="number" min="0" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Weakness</label>
                <input v-model="formData.weakness" type="text" placeholder="e.g., Fire, Piercing" class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Difficulty (1-5)</label>
                <input v-model.number="formData.difficulty" type="number" min="1" max="5" class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Experience Reward *</label>
                <input v-model.number="formData.experience_reward" type="number" min="0" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Min Cash Reward *</label>
                <input v-model.number="formData.cash_reward_min" type="number" min="0" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Max Cash Reward *</label>
                <input v-model.number="formData.cash_reward_max" type="number" min="0" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Spawn Rate (0.01-1.00) *</label>
                <input v-model.number="formData.spawn_rate" type="number" step="0.01" min="0.01" max="1.00" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
                <p class="text-xs text-slate-400 mt-1">Higher = more common</p>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">Active</label>
                <div class="flex items-center gap-2">
                  <input v-model="formData.active" type="checkbox" class="w-5 h-5 accent-blue-500" />
                  <span class="text-sm text-slate-400">Enable this enemy</span>
                </div>
              </div>
            </div>
          </div>
          <div class="sticky bottom-0 bg-slate-800 border-t border-slate-700 p-6 flex items-center gap-3 justify-end">
            <button @click="closeModal" class="px-4 py-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">Cancel</button>
            <button @click="saveItem" class="px-4 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg shadow-amber-500/25">Save Enemy</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import TableSkeleton from '@/components/TableSkeleton.vue'

const toast = useToast()
const confirm = useConfirm()

const selectedLocationId = ref(null)
const selectedAreaId = ref(null)
const locations = ref([])
const areas = ref([])
const items = ref([])
const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const showModal = ref(false)
const editingItem = ref(null)
const formData = ref({})
const pagination = ref(null)

const defaultItem = {
  area_id: null,
  name: '',
  icon: '👤',
  description: '',
  level: 1,
  health: 100,
  max_health: 100,
  strength: 10,
  defense: 5,
  speed: 10,
  agility: 10,
  weakness: '',
  difficulty: 1,
  experience_reward: 10,
  cash_reward_min: 50,
  cash_reward_max: 100,
  spawn_rate: 1.00,
  active: true
}

onMounted(async () => {
  try {
    const response = await api.get('/admin/combat-locations')
    locations.value = response.data.data || response.data
  } catch (err) {
    console.error('Failed to load locations:', err)
  }
})

const loadAreas = async () => {
  selectedAreaId.value = null
  areas.value = []
  items.value = []
  pagination.value = null
  if (!selectedLocationId.value) return
  try {
    const response = await api.get('/admin/combat-areas', { params: { location_id: selectedLocationId.value } })
    areas.value = response.data.data || response.data
  } catch (err) {
    console.error('Failed to load areas:', err)
  }
}

const onAreaChange = () => {
  items.value = []
  pagination.value = null
  if (selectedAreaId.value) fetchItems()
}

const fetchItems = async (page = 1) => {
  if (!selectedAreaId.value) return
  loading.value = true
  error.value = null
  try {
    const response = await api.get('/admin/combat-enemies', { params: { page, search: searchQuery.value, area_id: selectedAreaId.value } })
    if (response.data.data) {
      items.value = response.data.data
      pagination.value = { current_page: response.data.current_page, last_page: response.data.last_page, per_page: response.data.per_page, total: response.data.total, from: response.data.from, to: response.data.to }
    } else {
      items.value = response.data
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load data'
  } finally {
    loading.value = false
  }
}

const openCreate = () => { editingItem.value = null; formData.value = { ...defaultItem, area_id: selectedAreaId.value }; showModal.value = true }
const openEdit = (item) => { editingItem.value = item; formData.value = { ...item }; showModal.value = true }
const closeModal = () => { showModal.value = false; editingItem.value = null; formData.value = {} }

const saveItem = async () => {
  try {
    const data = { ...formData.value, area_id: selectedAreaId.value }
    if (editingItem.value) {
      await api.patch(`/admin/combat-enemies/${editingItem.value.id}`, data)
      toast.success('Combat Enemy updated successfully!')
    } else {
      await api.post('/admin/combat-enemies', data)
      toast.success('Combat Enemy created successfully!')
    }
    closeModal()
    fetchItems()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save Combat Enemy')
  }
}

const deleteItem = async (item) => {
  const confirmed = await confirm.confirm('Are you sure you want to delete this enemy? This action cannot be undone.', 'Delete Combat Enemy')
  if (!confirmed) return
  try {
    await api.delete(`/admin/combat-enemies/${item.id}`)
    toast.success('Combat Enemy deleted successfully!')
    fetchItems()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to delete Combat Enemy')
  }
}

const goToPage = (page) => { if (page >= 1 && page <= pagination.value.last_page) fetchItems(page) }
let searchTimeout
const debouncedSearch = () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => fetchItems(), 300) }
</script>

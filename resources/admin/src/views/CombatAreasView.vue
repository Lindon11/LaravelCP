<template>
  <div class="space-y-6">
    <!-- Location Selector -->
    <div class="p-6 bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-2xl">
      <label class="block text-sm font-medium text-gray-300 mb-2">Select Combat Location:</label>
      <select v-model="selectedLocationId" @change="onLocationChange" class="w-full max-w-md px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
        <option :value="null">-- Select Location --</option>
        <option v-for="location in locations" :key="location.id" :value="location.id">
          {{ location.icon }} {{ location.name }}
        </option>
      </select>
    </div>

    <template v-if="selectedLocationId">
      <!-- Search & Actions -->
      <div class="flex items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <input v-model="searchQuery" type="text" placeholder="Search areas..." @input="debouncedSearch" class="w-full pl-10 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all" />
        </div>
        <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg shadow-amber-500/25">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Create Combat Area
        </button>
      </div>

      <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <TableSkeleton :rows="5" :columns="5" />
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
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Name</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Difficulty</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Level</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Status</th>
                <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
              <tr v-for="item in items" :key="item.id" class="hover:bg-slate-700/25 transition-colors">
                <td class="px-6 py-4 text-sm text-slate-300">{{ item.name }}</td>
                <td class="px-6 py-4 text-sm text-slate-300">{{ '💀'.repeat(item.difficulty || item.difficulty_level || 1) }}</td>
                <td class="px-6 py-4 text-sm text-slate-300">{{ item.min_level || item.required_level }}</td>
                <td class="px-6 py-4 text-sm text-slate-300">{{ (item.active || item.is_active) ? '🟢' : '🔴' }}</td>
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
      <p class="text-slate-400 text-lg">👆 Please select a combat location to manage its areas</p>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
        <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <div class="sticky top-0 bg-slate-800 border-b border-slate-700 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">{{ editingItem ? 'Edit' : 'Create' }} Combat Area</h2>
            <button @click="closeModal" class="text-slate-400 hover:text-white transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-2 gap-6">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
                <input v-model="formData.name" type="text" required placeholder="e.g. Main Hall" class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">Description *</label>
                <textarea v-model="formData.description" rows="3" required placeholder="Description of the area..." class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Difficulty Level (1-5) *</label>
                <input v-model.number="formData.difficulty_level" type="number" min="1" max="5" required class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
                <p class="text-xs text-slate-400 mt-1">💀 = 1, 💀💀 = 2, 💀💀💀 = 3, etc.</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Display Order</label>
                <input v-model.number="formData.order" type="number" min="0" class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Required Level</label>
                <input v-model.number="formData.required_level" type="number" min="1" class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Required Respect</label>
                <input v-model.number="formData.required_respect" type="number" min="0" class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">Active</label>
                <div class="flex items-center gap-2">
                  <input v-model="formData.is_active" type="checkbox" class="w-5 h-5 accent-blue-500" />
                  <span class="text-sm text-slate-400">Enable this area</span>
                </div>
              </div>
            </div>
          </div>
          <div class="sticky bottom-0 bg-slate-800 border-t border-slate-700 p-6 flex items-center gap-3 justify-end">
            <button @click="closeModal" class="px-4 py-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">Cancel</button>
            <button @click="saveItem" class="px-4 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg shadow-amber-500/25">Save Combat Area</button>
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
const locations = ref([])
const items = ref([])
const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const showModal = ref(false)
const editingItem = ref(null)
const formData = ref({})
const pagination = ref(null)

const defaultItem = {
  combat_location_id: null,
  name: '',
  description: '',
  difficulty_level: 1,
  order: 0,
  required_level: 1,
  required_respect: 0,
  is_active: true
}

onMounted(async () => {
  try {
    const response = await api.get('/admin/combat-locations')
    locations.value = response.data.data || response.data
  } catch (err) {
    console.error('Failed to load locations:', err)
  }
})

const onLocationChange = () => {
  items.value = []
  pagination.value = null
  if (selectedLocationId.value) fetchItems()
}

const fetchItems = async (page = 1) => {
  if (!selectedLocationId.value) return
  loading.value = true
  error.value = null
  try {
    const response = await api.get('/admin/combat-areas', { params: { page, search: searchQuery.value, location_id: selectedLocationId.value } })
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

const openCreate = () => { editingItem.value = null; formData.value = { ...defaultItem, combat_location_id: selectedLocationId.value }; showModal.value = true }
const openEdit = (item) => { editingItem.value = item; formData.value = { ...item }; showModal.value = true }
const closeModal = () => { showModal.value = false; editingItem.value = null; formData.value = {} }

const saveItem = async () => {
  try {
    const data = { ...formData.value, combat_location_id: selectedLocationId.value }
    if (editingItem.value) {
      await api.patch(`/admin/combat-areas/${editingItem.value.id}`, data)
      toast.success('Combat Area updated successfully!')
    } else {
      await api.post('/admin/combat-areas', data)
      toast.success('Combat Area created successfully!')
    }
    closeModal()
    fetchItems()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save Combat Area')
  }
}

const deleteItem = async (item) => {
  const confirmed = await confirm.confirm('Are you sure you want to delete this Combat Area? This action cannot be undone.', 'Delete Combat Area')
  if (!confirmed) return
  try {
    await api.delete(`/admin/combat-areas/${item.id}`)
    toast.success('Combat Area deleted successfully!')
    fetchItems()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to delete Combat Area')
  }
}

const goToPage = (page) => { if (page >= 1 && page <= pagination.value.last_page) fetchItems(page) }
let searchTimeout
const debouncedSearch = () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => fetchItems(), 300) }
</script>

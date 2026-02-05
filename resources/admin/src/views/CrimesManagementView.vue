<template>
  <div class="space-y-6">
    <!-- Search & Actions -->
    <div class="flex items-center justify-between gap-4">
      <div class="relative flex-1 max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <input v-model="searchQuery" type="text" placeholder="Search crimes..." @input="debouncedSearch" class="w-full pl-10 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all" />
      </div>
      <button @click="showCreateModal" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg shadow-amber-500/25">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Create Crime
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
      <TableSkeleton :rows="5" :columns="6" />
    </div>

    <!-- Error -->
    <div v-else-if="error" class="rounded-2xl bg-red-500/10 border border-red-500/30 p-6">
      <div class="flex items-center gap-3">
        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        <p class="text-red-400 font-medium">{{ error }}</p>
      </div>
    </div>

    <!-- Table -->
    <div v-else class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-700/50 border-b border-slate-600/50">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300 cursor-pointer hover:text-white" @click="toggleSort('name')">
                Name
                <span v-if="sortKey === 'name'" class="ml-1">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
              </th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Cooldown</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Reward</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Level</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Energy</th>
              <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/50">
            <tr v-for="item in items" :key="item.id" class="hover:bg-slate-700/25 transition-colors">
              <td class="px-6 py-4 text-sm text-slate-200">{{ item.name }}</td>
              <td class="px-6 py-4 text-sm text-slate-200">{{ item.cooldown_seconds }}s</td>
              <td class="px-6 py-4 text-sm text-slate-200">${{ item.min_cash }} - ${{ item.max_cash }}</td>
              <td class="px-6 py-4 text-sm text-slate-200">{{ item.required_level }}</td>
              <td class="px-6 py-4 text-sm text-slate-200">{{ item.energy_cost }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button @click="editItem(item)" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 transition-colors">Edit</button>
                  <button @click="deleteItem(item)" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="flex items-center justify-between">
      <p class="text-sm text-slate-400">Showing {{ pagination.from || 1 }} to {{ pagination.to || pagination.total }} of {{ pagination.total }} results</p>
      <div class="flex items-center gap-2">
        <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" :class="['px-3 py-1.5 rounded-lg text-sm font-medium transition-colors', pagination.current_page === 1 ? 'bg-slate-800 text-slate-600 cursor-not-allowed' : 'bg-slate-700 text-slate-300 hover:bg-slate-600']">Previous</button>
        <span class="text-sm text-slate-400 px-3">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
        <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" :class="['px-3 py-1.5 rounded-lg text-sm font-medium transition-colors', pagination.current_page === pagination.last_page ? 'bg-slate-800 text-slate-600 cursor-not-allowed' : 'bg-slate-700 text-slate-300 hover:bg-slate-600']">Next</button>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
        <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <div class="sticky top-0 bg-slate-800 border-b border-slate-700 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">{{ editingItem ? 'Edit' : 'Create' }} Crime</h2>
            <button @click="closeModal" class="text-slate-400 hover:text-white transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
          </div>
          <div class="p-6">
            <div class="form-grid">
              <div class="form-group full-width">
                <label>Name *</label>
                <input v-model="formData.name" type="text" required placeholder="e.g. Rob a Bank">
              </div>

              <div class="form-group full-width">
                <label>Description *</label>
                <textarea v-model="formData.description" required rows="3" placeholder="Description of the crime..."></textarea>
              </div>

              <div class="form-group">
                <label>Success Rate (%) *</label>
                <input v-model.number="formData.success_rate" type="number" min="1" max="100" required>
              </div>

              <div class="form-group">
                <label>Difficulty *</label>
                <select v-model="formData.difficulty" required>
                  <option v-for="opt in crimeDifficulties" :key="opt.name" :value="opt.name">{{ opt.label }}</option>
                </select>
              </div>

              <div class="form-group">
                <label>Min Cash Reward *</label>
                <input v-model.number="formData.min_cash" type="number" min="0" required>
              </div>

              <div class="form-group">
                <label>Max Cash Reward *</label>
                <input v-model.number="formData.max_cash" type="number" min="0" required>
              </div>

              <div class="form-group">
                <label>Experience Reward *</label>
                <input v-model.number="formData.experience_reward" type="number" min="0" required>
              </div>

              <div class="form-group">
                <label>Respect Reward *</label>
                <input v-model.number="formData.respect_reward" type="number" min="0" required>
              </div>

              <div class="form-group">
                <label>Cooldown (seconds) *</label>
                <input v-model.number="formData.cooldown_seconds" type="number" min="0" required>
              </div>

              <div class="form-group">
                <label>Energy Cost *</label>
                <input v-model.number="formData.energy_cost" type="number" min="1" required>
              </div>

              <div class="form-group">
                <label>Required Level *</label>
                <input v-model.number="formData.required_level" type="number" min="1" required>
              </div>

              <div class="form-group">
                <label>Active</label>
                <div class="checkbox-wrapper">
                  <input v-model="formData.active" type="checkbox" id="active-checkbox">
                  <label for="active-checkbox">Enable this crime</label>
                </div>
              </div>
            </div>
          </div>
          <div class="sticky bottom-0 bg-slate-800 border-t border-slate-700 p-6 flex items-center gap-3 justify-end">
            <button @click="closeModal" class="px-4 py-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">Cancel</button>
            <button @click="saveItem" class="px-4 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg shadow-amber-500/25">Save Crime</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import TableSkeleton from '@/components/TableSkeleton.vue'

const toast = useToast()
const confirm = useConfirm()
const crimeDifficulties = ref([])

const items = ref([])
const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const showModal = ref(false)
const editingItem = ref(null)
const formData = ref({})
const pagination = ref(null)
const sortKey = ref('')
const sortOrder = ref('asc')

const defaultItem = {
  name: '',
  description: '',
  success_rate: 50,
  min_cash: 0,
  max_cash: 0,
  experience_reward: 0,
  respect_reward: 0,
  cooldown_seconds: 60,
  energy_cost: 10,
  required_level: 1,
  difficulty: 'easy',
  active: true
}

onMounted(async () => {
  try { crimeDifficulties.value = (await api.get('/admin/crime-difficulties')).data } catch(e) {}
  fetchItems()
})

const fetchItems = async (page = 1) => {
  loading.value = true
  error.value = null
  try {
    const params = { page, search: searchQuery.value }
    if (sortKey.value) {
      params.sort = sortKey.value
      params.order = sortOrder.value
    }
    const response = await api.get('/admin/crimes', { params })
    if (response.data.data) {
      items.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
        from: response.data.from,
        to: response.data.to
      }
    } else {
      items.value = response.data
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load data'
  } finally {
    loading.value = false
  }
}

const showCreateModal = () => {
  editingItem.value = null
  formData.value = { ...defaultItem }
  showModal.value = true
}

const editItem = (item) => {
  editingItem.value = item
  formData.value = { ...item }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingItem.value = null
  formData.value = {}
}

const saveItem = async () => {
  try {
    if (editingItem.value) {
      await api.patch(`/admin/crimes/${editingItem.value.id}`, formData.value)
      toast.success('Crime updated successfully!')
    } else {
      await api.post('/admin/crimes', formData.value)
      toast.success('Crime created successfully!')
    }
    closeModal()
    fetchItems()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save Crime')
  }
}

const deleteItem = async (item) => {
  const confirmed = await confirm.confirm(
    'Are you sure you want to delete this Crime? This action cannot be undone.',
    'Delete Crime'
  )
  if (!confirmed) return
  try {
    await api.delete(`/admin/crimes/${item.id}`)
    toast.success('Crime deleted successfully!')
    fetchItems()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to delete Crime')
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) fetchItems(page)
}

let searchTimeout
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchItems(), 300)
}

const toggleSort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
  fetchItems()
}
</script>

<style scoped>
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 600;
  color: #f1f5f9;
  font-size: 0.875rem;
  letter-spacing: 0.01em;
}

.form-group input,
.form-group textarea,
.form-group select {
  padding: 0.875rem 1.125rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.625rem;
  background: rgba(15, 23, 42, 0.5);
  color: #f1f5f9;
  font-size: 0.938rem;
  transition: all 0.2s ease;
  backdrop-filter: blur(8px);
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  background: rgba(15, 23, 42, 0.8);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #64748b;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
  font-family: inherit;
}

.form-group select {
  cursor: pointer;
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0;
}

.checkbox-wrapper input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #3b82f6;
}

.checkbox-wrapper label {
  margin: 0;
  cursor: pointer;
  font-weight: 500;
  color: #cbd5e1;
  user-select: none;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>

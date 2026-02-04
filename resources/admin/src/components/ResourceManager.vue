<template>
  <div class="space-y-6">
    <!-- Header & Toolbar -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">{{ resourceName }} Management</h1>
        <p class="text-sm text-slate-400 mt-1">Manage your {{ resourceName.toLowerCase() }}s</p>
      </div>
      <button @click="showCreateModal" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg shadow-amber-500/25">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Create {{ resourceName }}
      </button>
    </div>

    <!-- Search -->
    <div class="relative max-w-md">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </div>
      <input
        v-model="searchQuery"
        type="text"
        :placeholder="`Search ${resourceName.toLowerCase()}s...`"
        @input="debouncedSearch"
        class="w-full pl-10 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
      />
    </div>

    <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
      <TableSkeleton :rows="5" :columns="columns.length + 1" />
    </div>

    <div v-else-if="error" class="rounded-2xl bg-red-500/10 border border-red-500/30 p-6">
      <div class="flex items-center gap-3">
        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <p class="text-red-400 font-medium">{{ error }}</p>
      </div>
    </div>

    <div v-else class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-700/50 border-b border-slate-600/50">
            <tr>
              <th v-for="column in columns" :key="column.key" class="px-6 py-4 text-left text-sm font-semibold text-slate-300">
                {{ column.label }}
              </th>
              <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/50">
            <tr v-for="item in items" :key="item.id" class="hover:bg-slate-700/25 transition-colors">
              <td v-for="column in columns" :key="column.key" class="px-6 py-4">
                <component
                  v-if="column.component"
                  :is="column.component"
                  :value="getNestedValue(item, column.key)"
                />
                <div v-else-if="column.format === 'image'" class="flex items-center gap-3">
                  <img
                    v-if="getNestedValue(item, column.key)"
                    :src="getNestedValue(item, column.key)"
                    :alt="item.name || 'Image'"
                    class="w-10 h-10 rounded-lg object-cover border border-slate-600/50"
                  />
                  <span v-else class="w-10 h-10 rounded-lg bg-slate-700/50 border border-slate-600/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </span>
                </div>
                <div v-else-if="column.format === 'image-with-name'" class="flex items-center gap-3">
                  <img
                    v-if="getNestedValue(item, column.imageKey || 'image')"
                    :src="getNestedValue(item, column.imageKey || 'image')"
                    :alt="item.name || 'Image'"
                    class="w-10 h-10 rounded-lg object-cover border border-slate-600/50"
                  />
                  <span v-else class="w-10 h-10 rounded-lg bg-slate-700/50 border border-slate-600/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </span>
                  <span class="text-sm text-slate-300">{{ getNestedValue(item, column.key) }}</span>
                </div>
                <div v-else-if="column.format === 'array'" class="flex flex-wrap gap-1">
                  <span v-for="(val, idx) in getNestedValue(item, column.key)" :key="idx" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-300">
                    {{ val.name || val }}
                  </span>
                </div>
                <div v-else-if="column.key === 'roles'" class="flex flex-wrap gap-1">
                  <span v-if="!item.roles || item.roles.length === 0" class="text-sm text-slate-500 italic">No roles</span>
                  <span v-else v-for="role in item.roles" :key="role.id || role.name" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-500/20 text-amber-400">
                    {{ role.name }}
                  </span>
                </div>
                <span v-else class="text-sm text-slate-300">{{ formatValue(getNestedValue(item, column.key), column.format, item) }}</span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button @click="editItem(item)" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 transition-colors">
                    Edit
                  </button>
                  <button @click="deleteItem(item)" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="flex items-center justify-between">
      <p class="text-sm text-slate-400">
        Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of {{ pagination.total }} results
      </p>
      <div class="flex items-center gap-2">
        <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" :class="[
          'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
          pagination.current_page === 1
            ? 'bg-slate-800 text-slate-600 cursor-not-allowed'
            : 'bg-slate-700 text-slate-300 hover:bg-slate-600'
        ]">
          Previous
        </button>
        <span class="text-sm text-slate-400 px-3">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
        <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" :class="[
          'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
          pagination.current_page === pagination.last_page
            ? 'bg-slate-800 text-slate-600 cursor-not-allowed'
            : 'bg-slate-700 text-slate-300 hover:bg-slate-600'
        ]">
          Next
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
        <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <div class="sticky top-0 bg-slate-800 border-b border-slate-700 p-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">{{ editingItem ? 'Edit' : 'Create' }} {{ resourceName }}</h2>
            <button @click="closeModal" class="text-slate-400 hover:text-white transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="p-6">
            <slot name="form" :item="formData" :is-editing="!!editingItem"></slot>
          </div>
          <div class="sticky bottom-0 bg-slate-800 border-t border-slate-700 p-6 flex items-center gap-3 justify-end">
            <button @click="closeModal" class="px-4 py-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">
              Cancel
            </button>
            <button @click="saveItem" class="px-4 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg shadow-amber-500/25">
              Save {{ resourceName }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import TableSkeleton from '@/components/TableSkeleton.vue'

const toast = useToast()
const confirm = useConfirm()

const props = defineProps({
  resourceName: { type: String, required: true },
  endpoint: { type: String, required: true },
  columns: { type: Array, required: true },
  defaultItem: { type: Object, default: () => ({}) }
})

const items = ref([])
const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const showModal = ref(false)
const editingItem = ref(null)
const formData = ref({})
const pagination = ref(null)

onMounted(() => {
  fetchItems()
})

const fetchItems = async (page = 1) => {
  loading.value = true
  error.value = null
  try {
    const params = { page, search: searchQuery.value }
    const response = await api.get(props.endpoint, { params })

    if (response.data.data) {
      items.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        total: response.data.total
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
  formData.value = { ...props.defaultItem }
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
      await api.patch(`${props.endpoint}/${editingItem.value.id}`, formData.value)
      toast.success(`${props.resourceName} updated successfully!`)
    } else {
      await api.post(props.endpoint, formData.value)
      toast.success(`${props.resourceName} created successfully!`)
    }
    closeModal()
    fetchItems()
  } catch (err) {
    toast.error(err.response?.data?.message || `Failed to save ${props.resourceName}`)
  }
}

const deleteItem = async (item) => {
  const confirmed = await confirm.confirm(
    `Are you sure you want to delete this ${props.resourceName}? This action cannot be undone.`,
    `Delete ${props.resourceName}`
  )

  if (!confirmed) return

  try {
    await api.delete(`${props.endpoint}/${item.id}`)
    toast.success(`${props.resourceName} deleted successfully!`)
    fetchItems()
  } catch (err) {
    toast.error(err.response?.data?.message || `Failed to delete ${props.resourceName}`)
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchItems(page)
  }
}

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj)
}

const formatValue = (value, format, item) => {
  if (!format) return value
  if (typeof format === 'function') return format(value, item)
  if (format === 'date') return new Date(value).toLocaleDateString()
  if (format === 'datetime') return new Date(value).toLocaleString()
  if (format === 'currency') return `$${parseFloat(value).toFixed(2)}`
  if (format === 'boolean') return value ? '✓' : '✗'
  return value
}

let searchTimeout
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchItems(), 300)
}
</script>

<style scoped>
.resource-manager {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  max-width: 400px;
}

.search-box input {
  width: 100%;
  padding: 0.625rem 1rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.5rem;
  background: rgba(30, 41, 59, 0.5);
  color: #f1f5f9;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  backdrop-filter: blur(8px);
}

.search-box input:focus {
  outline: none;
  border-color: #3b82f6;
  background: rgba(30, 41, 59, 0.8);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-box input::placeholder {
  color: #64748b;
}

.btn-primary {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.btn-primary:active {
  transform: translateY(0);
}

.btn-secondary {
  padding: 0.625rem 1.25rem;
  background: rgba(51, 65, 85, 0.5);
  color: #e2e8f0;
  border: 2px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: rgba(51, 65, 85, 0.8);
  border-color: rgba(148, 163, 184, 0.3);
}

.table-container {
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.5rem;
  overflow: hidden;
  backdrop-filter: blur(12px);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: rgba(51, 65, 85, 0.5);
  padding: 0.75rem 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.75rem;
  color: #cbd5e1;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.data-table td {
  padding: 0.75rem 1rem;
  border-top: 1px solid rgba(51, 65, 85, 0.3);
  color: #e2e8f0;
  font-size: 0.875rem;
}

.data-table tbody tr {
  transition: background 0.15s ease;
}

.data-table tbody tr:hover {
  background: rgba(59, 130, 246, 0.08);
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 0.8125rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-edit {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.btn-edit:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-delete {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}

.btn-delete:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.75rem;
  margin-top: 1rem;
}

.pagination button {
  padding: 0.5rem 1rem;
  background: rgba(51, 65, 85, 0.5);
  color: #e2e8f0;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.pagination button:hover:not(:disabled) {
  background: rgba(51, 65, 85, 0.8);
  border-color: rgba(148, 163, 184, 0.3);
}

.pagination button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pagination span {
  color: #cbd5e1;
  font-weight: 500;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  border: 1px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.75rem;
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal::-webkit-scrollbar {
  width: 8px;
}

.modal::-webkit-scrollbar-track {
  background: rgba(51, 65, 85, 0.3);
}

.modal::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.4);
  border-radius: 4px;
}

.modal::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.6);
}

.modal-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(51, 65, 85, 0.5);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  color: #f1f5f9;
  font-size: 1.125rem;
  font-weight: 700;
}

.close-btn {
  background: rgba(148, 163, 184, 0.1);
  border: none;
  color: #94a3b8;
  font-size: 1.5rem;
  cursor: pointer;
  line-height: 1;
  width: 32px;
  height: 32px;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: rgba(148, 163, 184, 0.2);
  color: #cbd5e1;
}

.modal-body {
  padding: 1.25rem;
}

.modal-footer {
  padding: 1rem 1.25rem;
  border-top: 1px solid rgba(51, 65, 85, 0.5);
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.loading, .error-message {
  padding: 2rem 1rem;
  text-align: center;
  color: #94a3b8;
  font-size: 0.875rem;
}

.error-message {
  color: #f87171;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 0.5rem;
  margin: 1rem;
  padding: 1rem;
}

.badge-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.badge {
  padding: 0.25rem 0.625rem;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  box-shadow: 0 1px 4px rgba(59, 130, 246, 0.3);
}

/* Form Styling */
.modal-body :deep(.form-grid) {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.modal-body :deep(.form-group) {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.modal-body :deep(.form-group.full-width) {
  grid-column: 1 / -1;
}

.modal-body :deep(.form-group label) {
  color: #cbd5e1;
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.modal-body :deep(.form-group input),
.modal-body :deep(.form-group textarea),
.modal-body :deep(.form-group select) {
  padding: 0.625rem 0.875rem;
  background: rgba(30, 41, 59, 0.6);
  border: 2px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.375rem;
  color: #f1f5f9;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  width: 100%;
}

.modal-body :deep(.form-group input:focus),
.modal-body :deep(.form-group textarea:focus),
.modal-body :deep(.form-group select:focus) {
  outline: none;
  border-color: #3b82f6;
  background: rgba(30, 41, 59, 0.8);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.modal-body :deep(.form-group input::placeholder),
.modal-body :deep(.form-group textarea::placeholder) {
  color: #64748b;
}

.modal-body :deep(.form-group textarea) {
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
}

.modal-body :deep(.form-group select) {
  cursor: pointer;
}

.modal-body :deep(.form-group small) {
  color: #94a3b8;
  font-size: 0.813rem;
  margin-top: 0.25rem;
}

.modal-body :deep(.checkbox-wrapper) {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.modal-body :deep(.checkbox-wrapper input[type="checkbox"]) {
  width: auto;
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #3b82f6;
}

.modal-body :deep(.checkbox-wrapper label) {
  margin: 0;
  cursor: pointer;
  user-select: none;
}

@media (max-width: 768px) {
  .modal-body :deep(.form-grid) {
    grid-template-columns: 1fr;
  }
}
</style>

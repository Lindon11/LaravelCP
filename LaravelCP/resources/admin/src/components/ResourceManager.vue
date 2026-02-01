<template>
  <div class="resource-manager">
    <div class="toolbar">
      <div class="search-box">
        <input 
          v-model="searchQuery" 
          type="text" 
          :placeholder="`Search ${resourceName}...`"
          @input="debouncedSearch"
        />
      </div>
      <button @click="showCreateModal" class="btn-primary">
        <span>➕</span> Create {{ resourceName }}
      </button>
    </div>

    <div v-if="loading" class="loading">
      <TableSkeleton :rows="5" :columns="columns.length + 1" />
    </div>

    <div v-else-if="error" class="error-message">{{ error }}</div>

    <div v-else class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th v-for="column in columns" :key="column.key">
              {{ column.label }}
            </th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td v-for="column in columns" :key="column.key">
              <component 
                v-if="column.component" 
                :is="column.component" 
                :value="getNestedValue(item, column.key)"
              />
              <div v-else-if="column.format === 'array'" class="badge-list">
                <span v-for="(val, idx) in getNestedValue(item, column.key)" :key="idx" class="badge">
                  {{ val.name || val }}
                </span>
              </div>
              <span v-else>{{ formatValue(getNestedValue(item, column.key), column.format) }}</span>
            </td>
            <td class="actions">
              <button @click="editItem(item)" class="btn-sm btn-edit">Edit</button>
              <button @click="deleteItem(item)" class="btn-sm btn-delete">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="pagination" class="pagination">
      <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1">
        Previous
      </button>
      <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
      <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page">
        Next
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ editingItem ? 'Edit' : 'Create' }} {{ resourceName }}</h2>
          <button @click="closeModal" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <slot name="form" :item="formData" :is-editing="!!editingItem"></slot>
        </div>
        <div class="modal-footer">
          <button @click="closeModal" class="btn-secondary">Cancel</button>
          <button @click="saveItem" class="btn-primary">Save</button>
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

const formatValue = (value, format) => {
  if (!format) return value
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
  margin-bottom: 2rem;
  gap: 1rem;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  max-width: 400px;
}

.search-box input {
  width: 100%;
  padding: 0.875rem 1.25rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.75rem;
  background: rgba(30, 41, 59, 0.5);
  color: #f1f5f9;
  font-size: 0.938rem;
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
  padding: 0.875rem 1.75rem;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 0.938rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.625rem;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.btn-primary:active {
  transform: translateY(0);
}

.btn-secondary {
  padding: 0.875rem 1.75rem;
  background: rgba(51, 65, 85, 0.5);
  color: #e2e8f0;
  border: 2px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.75rem;
  font-weight: 500;
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
  border-radius: 1rem;
  overflow: hidden;
  backdrop-filter: blur(12px);
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: rgba(51, 65, 85, 0.5);
  padding: 1.125rem 1.5rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.813rem;
  color: #cbd5e1;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.data-table td {
  padding: 1.125rem 1.5rem;
  border-top: 1px solid rgba(51, 65, 85, 0.3);
  color: #e2e8f0;
  font-size: 0.938rem;
}

.data-table tbody tr {
  transition: background 0.15s ease;
}

.data-table tbody tr:hover {
  background: rgba(59, 130, 246, 0.08);
}

.actions {
  display: flex;
  gap: 0.625rem;
}

.btn-sm {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 0.875rem;
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
  gap: 1rem;
  margin-top: 2rem;
}

.pagination button {
  padding: 0.625rem 1.25rem;
  background: rgba(51, 65, 85, 0.5);
  color: #e2e8f0;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.5rem;
  cursor: pointer;
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
  border-radius: 1.25rem;
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
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
  padding: 2rem;
  border-bottom: 1px solid rgba(51, 65, 85, 0.5);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  color: #f1f5f9;
  font-size: 1.5rem;
  font-weight: 700;
}

.close-btn {
  background: rgba(148, 163, 184, 0.1);
  border: none;
  color: #94a3b8;
  font-size: 1.75rem;
  cursor: pointer;
  line-height: 1;
  width: 40px;
  height: 40px;
  border-radius: 0.5rem;
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
  padding: 2rem;
}

.modal-footer {
  padding: 2rem;
  border-top: 1px solid rgba(51, 65, 85, 0.5);
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.loading, .error-message {
  padding: 4rem 2rem;
  text-align: center;
  color: #94a3b8;
  font-size: 1rem;
}

.error-message {
  color: #f87171;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 0.75rem;
  margin: 1rem;
  padding: 1.5rem;
}

.badge-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.badge {
  padding: 0.375rem 0.875rem;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border-radius: 9999px;
  font-size: 0.813rem;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}
</style>

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

    <div v-if="loading" class="loading">Loading...</div>

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
    } else {
      await api.post(props.endpoint, formData.value)
    }
    closeModal()
    fetchItems()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save item'
  }
}

const deleteItem = async (item) => {
  if (!confirm(`Are you sure you want to delete this ${props.resourceName}?`)) return
  
  try {
    await api.delete(`${props.endpoint}/${item.id}`)
    fetchItems()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete item'
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
  padding: 1rem;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  gap: 1rem;
}

.search-box input {
  padding: 0.75rem 1rem;
  border: 1px solid #334155;
  border-radius: 0.5rem;
  background: #1e293b;
  color: #fff;
  width: 300px;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: #334155;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
}

.table-container {
  background: #1e293b;
  border-radius: 0.5rem;
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: #334155;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #cbd5e1;
}

.data-table td {
  padding: 1rem;
  border-top: 1px solid #334155;
  color: #e2e8f0;
}

.data-table tbody tr:hover {
  background: rgba(148, 163, 184, 0.05);
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-sm {
  padding: 0.5rem 0.75rem;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 0.875rem;
}

.btn-edit {
  background: #3b82f6;
  color: white;
}

.btn-delete {
  background: #ef4444;
  color: white;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.pagination button {
  padding: 0.5rem 1rem;
  background: #334155;
  color: white;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: #1e293b;
  border-radius: 0.75rem;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #334155;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  color: #fff;
}

.close-btn {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 2rem;
  cursor: pointer;
  line-height: 1;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #334155;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.loading, .error-message {
  padding: 2rem;
  text-align: center;
  color: #94a3b8;
}

.error-message {
  color: #ef4444;
}

.badge-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.badge {
  padding: 0.25rem 0.75rem;
  background: #3b82f6;
  color: white;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}
</style>

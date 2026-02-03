<template>
  <div class="categories-page">
    <div class="page-header">
      <div class="header-left">
        <h1>📂 Ticket Categories</h1>
        <span class="subtitle">Manage support ticket categories</span>
      </div>
      <button class="btn btn-primary" @click="showCreateModal = true">
        + Add Category
      </button>
    </div>

    <div class="categories-grid" v-if="!loading">
      <div
        v-for="category in categories"
        :key="category.id"
        class="category-card"
      >
        <div class="card-header">
          <span class="card-icon">{{ category.icon || '📁' }}</span>
          <div class="card-actions">
            <button class="action-btn" @click="editCategory(category)" title="Edit">✏️</button>
            <button class="action-btn" @click="deleteCategory(category)" title="Delete">🗑️</button>
          </div>
        </div>
        <h3>{{ category.name }}</h3>
        <p class="description">{{ category.description || 'No description' }}</p>
        <div class="card-footer">
          <span class="ticket-count">{{ category.tickets_count || 0 }} tickets</span>
          <span v-if="category.is_active" class="status-active">Active</span>
          <span v-else class="status-inactive">Inactive</span>
        </div>
      </div>

      <div v-if="categories.length === 0" class="empty-state">
        <span class="empty-icon">📂</span>
        <h3>No categories yet</h3>
        <p>Create your first ticket category</p>
        <button class="btn btn-primary" @click="showCreateModal = true">
          + Add Category
        </button>
      </div>
    </div>

    <div v-else class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading categories...</p>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" v-if="showCreateModal || showEditModal" @click.self="closeModals">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ showEditModal ? 'Edit Category' : 'Create Category' }}</h3>
          <button class="modal-close" @click="closeModals">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Name *</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g., Technical Support"
              class="form-input"
            >
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea
              v-model="form.description"
              placeholder="Brief description of this category"
              class="form-textarea"
              rows="3"
            ></textarea>
          </div>
          <div class="form-group">
            <label>Icon (emoji)</label>
            <div class="icon-picker">
              <input
                v-model="form.icon"
                type="text"
                placeholder="📁"
                class="form-input icon-input"
              >
              <div class="icon-suggestions">
                <button
                  v-for="icon in suggestedIcons"
                  :key="icon"
                  type="button"
                  class="icon-btn"
                  @click="form.icon = icon"
                >
                  {{ icon }}
                </button>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.is_active">
              <span>Active</span>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeModals">Cancel</button>
          <button
            class="btn btn-primary"
            @click="showEditModal ? updateCategory() : createCategory()"
            :disabled="!form.name.trim() || saving"
          >
            {{ saving ? 'Saving...' : (showEditModal ? 'Update' : 'Create') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const toast = useToast()

const loading = ref(true)
const saving = ref(false)
const categories = ref([])
const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingCategory = ref(null)

const form = ref({
  name: '',
  description: '',
  icon: '📁',
  is_active: true
})

const suggestedIcons = ['📁', '🐛', '💰', '🔒', '⚙️', '📱', '💬', '🎮', '📧', '❓', '🛒', '👤']

const fetchCategories = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/support/ticket-categories')
    categories.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Failed to fetch categories:', error)
    toast.error('Failed to load categories')
  } finally {
    loading.value = false
  }
}

const createCategory = async () => {
  if (!form.value.name.trim()) return

  saving.value = true
  try {
    const response = await api.post('/admin/support/ticket-categories', form.value)
    categories.value.push(response.data.data || response.data)
    closeModals()
    toast.success('Category created')
  } catch (error) {
    console.error('Failed to create category:', error)
    toast.error('Failed to create category')
  } finally {
    saving.value = false
  }
}

const editCategory = (category) => {
  editingCategory.value = category
  form.value = {
    name: category.name,
    description: category.description || '',
    icon: category.icon || '📁',
    is_active: category.is_active !== false
  }
  showEditModal.value = true
}

const updateCategory = async () => {
  if (!form.value.name.trim()) return

  saving.value = true
  try {
    const response = await api.put(`/admin/support/ticket-categories/${editingCategory.value.id}`, form.value)
    const idx = categories.value.findIndex(c => c.id === editingCategory.value.id)
    if (idx !== -1) {
      categories.value[idx] = response.data.data || response.data
    }
    closeModals()
    toast.success('Category updated')
  } catch (error) {
    console.error('Failed to update category:', error)
    toast.error('Failed to update category')
  } finally {
    saving.value = false
  }
}

const deleteCategory = async (category) => {
  if (!confirm(`Delete "${category.name}" category? Tickets in this category will be moved to General.`)) return

  try {
    await api.delete(`/admin/support/ticket-categories/${category.id}`)
    categories.value = categories.value.filter(c => c.id !== category.id)
    toast.success('Category deleted')
  } catch (error) {
    console.error('Failed to delete category:', error)
    toast.error('Failed to delete category')
  }
}

const closeModals = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingCategory.value = null
  form.value = {
    name: '',
    description: '',
    icon: '📁',
    is_active: true
  }
}

onMounted(() => {
  fetchCategories()
})
</script>

<style scoped>
.categories-page {
  padding: 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.header-left h1 {
  margin: 0 0 0.25rem;
  font-size: 1.5rem;
  color: #f1f5f9;
}

.subtitle {
  color: #64748b;
  font-size: 0.875rem;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.category-card {
  background: #1e293b;
  border-radius: 12px;
  padding: 1.5rem;
  transition: transform 0.15s, box-shadow 0.15s;
}

.category-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.card-icon {
  font-size: 2rem;
}

.card-actions {
  display: flex;
  gap: 0.5rem;
  opacity: 0;
  transition: opacity 0.15s;
}

.category-card:hover .card-actions {
  opacity: 1;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  background: #334155;
  cursor: pointer;
  font-size: 0.875rem;
  transition: background 0.15s;
}

.action-btn:hover {
  background: #475569;
}

.category-card h3 {
  margin: 0 0 0.5rem;
  font-size: 1.125rem;
  color: #f1f5f9;
}

.description {
  color: #94a3b8;
  font-size: 0.875rem;
  margin: 0 0 1rem;
  line-height: 1.5;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #334155;
}

.ticket-count {
  font-size: 0.8rem;
  color: #64748b;
}

.status-active {
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
  border-radius: 4px;
  font-weight: 600;
}

.status-inactive {
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
  background: rgba(100, 116, 139, 0.2);
  color: #64748b;
  border-radius: 4px;
  font-weight: 600;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem 2rem;
  background: #1e293b;
  border-radius: 12px;
}

.empty-icon {
  font-size: 4rem;
  display: block;
  margin-bottom: 1rem;
}

.empty-state h3 {
  margin: 0 0 0.5rem;
  color: #f1f5f9;
}

.empty-state p {
  color: #64748b;
  margin: 0 0 1.5rem;
}

.loading-state {
  text-align: center;
  padding: 4rem 2rem;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #334155;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: #1e293b;
  border-radius: 12px;
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #334155;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.125rem;
  color: #f1f5f9;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #64748b;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}

.modal-close:hover {
  color: #f1f5f9;
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  color: #94a3b8;
}

.form-input,
.form-textarea {
  width: 100%;
  padding: 0.75rem;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 8px;
  color: #f1f5f9;
  font-size: 0.875rem;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

.icon-picker {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.icon-input {
  width: 80px;
  text-align: center;
  font-size: 1.25rem;
}

.icon-suggestions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border: 1px solid #334155;
  border-radius: 6px;
  background: #0f172a;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.15s;
}

.icon-btn:hover {
  border-color: #3b82f6;
  background: rgba(59, 130, 246, 0.1);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.875rem;
  color: #e2e8f0;
}

.checkbox-label input {
  accent-color: #3b82f6;
  width: 16px;
  height: 16px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.25rem 1.5rem;
  border-top: 1px solid #334155;
}

.btn {
  padding: 0.625rem 1.25rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.15s;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: #334155;
  color: #f1f5f9;
}

.btn-secondary:hover {
  background: #475569;
}
</style>

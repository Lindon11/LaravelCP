<template>
  <div class="modules-page">
    <div class="modules-header">
      <h1>🎮 Game Modules</h1>
      <button class="btn-primary" @click="loadModules">
        <span>🔄</span>
        Refresh
      </button>
    </div>

    <div v-if="loading" class="loading">Loading modules...</div>

    <div v-else-if="modules.length === 0" class="no-modules">
      <p>No modules found.</p>
    </div>

    <div v-else class="modules-grid">
      <div v-for="module in modules" :key="module.id" class="module-card">
        <div class="module-header">
          <div class="module-icon">{{ module.icon || '📦' }}</div>
          <div class="module-info">
            <h3>{{ module.display_name || module.name }}</h3>
            <p>{{ module.description }}</p>
          </div>
        </div>
        
        <div class="module-details">
          <div class="detail">
            <span class="label">Required Level:</span>
            <input 
              v-model.number="module.required_level" 
              type="number" 
              min="1"
              @change="updateModule(module)"
              class="level-input"
            />
          </div>
          <div class="detail">
            <span class="label">Order:</span>
            <input 
              v-model.number="module.order" 
              type="number" 
              min="0"
              @change="updateModule(module)"
              class="order-input"
            />
          </div>
          <div class="detail">
            <span class="label">Status:</span>
            <label class="toggle-switch">
              <input 
                type="checkbox" 
                :checked="module.enabled"
                @change="toggleModule(module)"
              />
              <span class="slider"></span>
            </label>
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

const { showToast } = useToast()
const modules = ref([])
const loading = ref(true)

const loadModules = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/modules')
    modules.value = response.data.modules || []
  } catch (error) {
    console.error('Failed to load modules:', error)
    showToast('Failed to load modules', 'error')
  } finally {
    loading.value = false
  }
}

const toggleModule = async (module) => {
  try {
    const newEnabled = !module.enabled
    await api.patch(`/admin/modules/${module.id}/toggle`, {
      enabled: newEnabled
    })
    module.enabled = newEnabled
    showToast(`${module.display_name || module.name} ${newEnabled ? 'enabled' : 'disabled'}`, 'success')
  } catch (error) {
    console.error('Failed to toggle module:', error)
    showToast('Failed to toggle module', 'error')
  }
}

const updateModule = async (module) => {
  try {
    await api.patch(`/admin/modules/${module.id}`, {
      required_level: module.required_level,
      order: module.order
    })
    showToast('Module updated', 'success')
  } catch (error) {
    console.error('Failed to update module:', error)
    showToast('Failed to update module', 'error')
  }
}

onMounted(() => {
  loadModules()
})
</script>

<style scoped>
.modules-page {
  width: 100%;
}

.modules-list {
  width: 100%;
}

.modules-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.modules-header h1 {
  margin: 0;
  color: #ffffff;
  font-size: 1.875rem;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: #ffffff;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
}

.no-modules {
  text-align: center;
  padding: 3rem;
  color: #94a3b8;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #94a3b8;
}

.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.module-card {
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.75rem;
  padding: 1.5rem;
  transition: all 0.2s;
}

.module-card:hover {
  background: rgba(30, 41, 59, 1);
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.module-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.module-icon {
  font-size: 2.5rem;
  line-height: 1;
}

.module-info {
  flex: 1;
}

.module-info h3 {
  margin: 0 0 0.5rem 0;
  color: #ffffff;
  font-size: 1.25rem;
}

.module-info p {
  margin: 0;
  color: #94a3b8;
  font-size: 0.875rem;
  line-height: 1.5;
}

.module-details {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.label {
  color: #94a3b8;
  font-size: 0.875rem;
  font-weight: 500;
}

.level-input,
.order-input {
  width: 80px;
  padding: 0.5rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.375rem;
  color: #ffffff;
  font-size: 0.875rem;
  text-align: center;
}

.level-input:focus,
.order-input:focus {
  outline: none;
  border-color: #3b82f6;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 24px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(148, 163, 184, 0.3);
  transition: 0.3s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #10b981;
}

input:checked + .slider:before {
  transform: translateX(24px);
  font-size: 1.25rem;
}

.module-detail {
  width: 100%;
}

.detail-header {
  margin-bottom: 2rem;
}

.btn-back {
  background: rgba(148, 163, 184, 0.1);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #94a3b8;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 0.875rem;
  margin-bottom: 1rem;
  transition: all 0.2s;
}

.btn-back:hover {
  background: rgba(148, 163, 184, 0.2);
  color: #ffffff;
}

.detail-header h1 {
  margin: 0;
  color: #ffffff;
  font-size: 1.875rem;
}

.tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.tabs button {
  background: none;
  border: none;
  color: #94a3b8;
  padding: 0.75rem 1.5rem;
  cursor: pointer;
  font-size: 0.938rem;
  font-weight: 500;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
}

.tabs button:hover {
  color: #ffffff;
}

.tabs button.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
}

.table-container {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.75rem;
  padding: 1.5rem;
}

.table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  gap: 1rem;
}

.btn-export {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  cursor: pointer;
  font-weight: 600;
}

.control-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #94a3b8;
  font-size: 0.875rem;
}

.control-group select {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #ffffff;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #94a3b8;
  font-size: 0.875rem;
}

.search-box input {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #ffffff;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
  width: 200px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
}

.data-table thead {
  border-bottom: 2px solid rgba(148, 163, 184, 0.2);
}

.data-table th {
  text-align: left;
  padding: 0.75rem;
  color: #94a3b8;
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
}

.data-table td {
  padding: 0.75rem;
  color: #ffffff;
  border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.data-table tbody tr:hover {
  background: rgba(59, 130, 246, 0.05);
}

.actions a {
  color: #3b82f6;
  text-decoration: none;
  margin-right: 0.75rem;
}

.actions a:hover {
  text-decoration: underline;
}

.table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #94a3b8;
  font-size: 0.875rem;
}

.pagination {
  display: flex;
  gap: 0.5rem;
}

.pagination button {
  background: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.2);
  color: #3b82f6;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination button.active {
  background: #3b82f6;
  color: white;
}

.form-container {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.75rem;
  padding: 2rem;
  max-width: 800px;
}

.form-container h2 {
  margin: 0 0 1.5rem 0;
  color: #ffffff;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  color: #94a3b8;
  font-size: 0.875rem;
  font-weight: 600;
}

.form-group input,
.form-group textarea,
.form-group select {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #ffffff;
  padding: 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.938rem;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-cancel,
.btn-submit {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
}

.btn-cancel {
  background: rgba(148, 163, 184, 0.1);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #94a3b8;
}

.btn-submit {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.no-data {
  text-align: center;
  padding: 3rem 1rem;
}

.empty-state {
  color: #94a3b8;
}

.empty-state p {
  margin: 0.5rem 0;
}

.empty-state .hint {
  font-size: 0.875rem;
  color: #64748b;
}
</style>

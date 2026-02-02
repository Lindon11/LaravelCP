<template>
  <div class="roles-permissions">
    <div class="toolbar">
      <div class="search-box">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search roles..."
        />
      </div>
      <button @click="showCreateModal" class="btn-primary">
        <span>➕</span> Create Role
      </button>
    </div>

    <div v-if="loading" class="loading">Loading...</div>
    <div v-else-if="error" class="error-message">{{ error }}</div>

    <div v-else class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Role Name</th>
            <th>Permissions</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="role in roles" :key="role.id">
            <td>{{ role.id }}</td>
            <td>{{ role.name }}</td>
            <td>
              <div class="badge-list">
                <span v-for="perm in role.permissions" :key="perm.id" class="badge">{{ perm.name }}</span>
                <span v-if="!role.permissions?.length" class="text-muted">No permissions</span>
              </div>
            </td>
            <td>{{ new Date(role.created_at).toLocaleDateString() }}</td>
            <td class="actions">
              <button @click="editRole(role)" class="btn-sm btn-edit">Edit</button>
              <button @click="deleteRole(role)" class="btn-sm btn-delete">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ editingRole ? 'Edit' : 'Create' }} Role</h2>
          <button @click="closeModal" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Role Name</label>
            <input v-model="formData.name" type="text" required placeholder="e.g., moderator, support" class="form-input" />
          </div>
          
          <div class="form-group">
            <label>Permissions ({{ formData.permissions.length }} selected)</label>
            <div v-if="loadingPermissions" class="loading-text">Loading permissions...</div>
            <div v-else class="permissions-container">
              <div v-for="(perms, group) in permissionsByGroup" :key="group" class="permission-group">
                <div class="group-header">
                  <h4>{{ group }}</h4>
                  <button @click="toggleGroup(group, perms)" class="btn-link" type="button">
                    {{ isGroupSelected(group, perms) ? 'Deselect All' : 'Select All' }}
                  </button>
                </div>
                <div class="permissions-grid">
                  <label v-for="permission in perms" :key="permission.id" class="permission-checkbox">
                    <input type="checkbox" :value="permission.name" v-model="formData.permissions" />
                    <span>{{ permission.name }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="closeModal" class="btn-secondary">Cancel</button>
          <button @click="saveRole" class="btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'

const roles = ref([])
const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const showModal = ref(false)
const editingRole = ref(null)
const formData = ref({ name: '', permissions: [] })
const saving = ref(false)
const allPermissions = ref({})
const loadingPermissions = ref(true)

const permissionsByGroup = computed(() => allPermissions.value)

onMounted(() => {
  fetchRoles()
  fetchPermissions()
})

const fetchRoles = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await api.get('/admin/roles')
    roles.value = response.data
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load roles'
  } finally {
    loading.value = false
  }
}

const fetchPermissions = async () => {
  try {
    const response = await api.get('/admin/permissions')
    allPermissions.value = response.data
  } catch (err) {
    console.error('Failed to load permissions:', err)
  } finally {
    loadingPermissions.value = false
  }
}

const showCreateModal = () => {
  editingRole.value = null
  formData.value = { name: '', permissions: [] }
  showModal.value = true
}

const editRole = (role) => {
  editingRole.value = role
  formData.value = { name: role.name, permissions: role.permissions?.map(p => p.name) || [] }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingRole.value = null
  formData.value = { name: '', permissions: [] }
}

const saveRole = async () => {
  saving.value = true
  try {
    if (editingRole.value) {
      await api.patch(`/admin/roles/${editingRole.value.id}`, formData.value)
    } else {
      await api.post('/admin/roles', formData.value)
    }
    closeModal()
    fetchRoles()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save role'
  } finally {
    saving.value = false
  }
}

const deleteRole = async (role) => {
  if (!confirm(`Delete role "${role.name}"?`)) return
  try {
    await api.delete(`/admin/roles/${role.id}`)
    fetchRoles()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete role'
  }
}

const toggleGroup = (group, perms) => {
  const permNames = perms.map(p => p.name)
  const allSelected = permNames.every(name => formData.value.permissions.includes(name))
  if (allSelected) {
    formData.value.permissions = formData.value.permissions.filter(name => !permNames.includes(name))
  } else {
    formData.value.permissions = [...new Set([...formData.value.permissions, ...permNames])]
  }
}

const isGroupSelected = (group, perms) => {
  return perms.every(p => formData.value.permissions.includes(p.name))
}
</script>

<style scoped>
.roles-permissions { padding: 1rem; }
.toolbar { display: flex; justify-content: space-between; margin-bottom: 1.5rem; gap: 1rem; }
.search-box input { padding: 0.75rem 1rem; border: 1px solid #334155; border-radius: 0.5rem; background: #1e293b; color: #fff; width: 300px; }
.btn-primary { padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-secondary { padding: 0.75rem 1.5rem; background: #334155; color: white; border: none; border-radius: 0.5rem; cursor: pointer; }
.btn-link { background: none; border: none; color: #3b82f6; cursor: pointer; font-size: 0.875rem; text-decoration: underline; }
.table-container { background: #1e293b; border-radius: 0.5rem; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: #334155; padding: 1rem; text-align: left; font-weight: 600; color: #cbd5e1; }
.data-table td { padding: 1rem; border-top: 1px solid #334155; color: #e2e8f0; }
.data-table tbody tr:hover { background: rgba(148, 163, 184, 0.05); }
.actions { display: flex; gap: 0.5rem; }
.btn-sm { padding: 0.5rem 0.75rem; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem; }
.btn-edit { background: #3b82f6; color: white; }
.btn-delete { background: #ef4444; color: white; }
.badge-list { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.badge { padding: 0.25rem 0.75rem; background: #3b82f6; color: white; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
.text-muted { color: #64748b; font-style: italic; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.75); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: #1e293b; border-radius: 0.75rem; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; }
.modal-header { padding: 1.5rem; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; }
.modal-header h2 { margin: 0; color: #fff; }
.close-btn { background: none; border: none; color: #94a3b8; font-size: 2rem; cursor: pointer; line-height: 1; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1.5rem; border-top: 1px solid #334155; display: flex; justify-content: flex-end; gap: 1rem; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.75rem; color: #cbd5e1; font-weight: 600; font-size: 0.875rem; }
.form-input { width: 100%; padding: 0.75rem; background: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; color: #fff; font-size: 1rem; }
.loading, .loading-text, .error-message { padding: 2rem; text-align: center; color: #94a3b8; }
.error-message { color: #ef4444; }
.permissions-container { max-height: 400px; overflow-y: auto; padding: 1rem; background: #0f172a; border: 1px solid #334155; border-radius: 0.5rem; }
.permission-group { margin-bottom: 1.5rem; }
.permission-group:last-child { margin-bottom: 0; }
.group-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #334155; }
.group-header h4 { color: #3b82f6; font-size: 0.875rem; text-transform: uppercase; margin: 0; font-weight: 600; }
.permissions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.5rem; }
.permission-checkbox { display: flex; align-items: center; gap: 0.5rem; padding: 0.625rem; color: #cbd5e1; cursor: pointer; border-radius: 0.375rem; transition: background 0.2s; }
.permission-checkbox:hover { background: rgba(59, 130, 246, 0.1); }
.permission-checkbox input[type="checkbox"] { width: 1.125rem; height: 1.125rem; cursor: pointer; accent-color: #3b82f6; }
.permission-checkbox span { font-size: 0.875rem; }
</style>

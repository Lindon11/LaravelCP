<template>
  <div class="space-y-6">
    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="relative w-full sm:w-80">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search roles..."
          class="w-full pl-10 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
        />
      </div>
      <button
        @click="showCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all"
      >
        <PlusIcon class="w-5 h-5" />
        Create Role
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-12 h-12 border-4 border-amber-500/30 border-t-amber-500 rounded-full animate-spin mb-4"></div>
        <p class="text-slate-400">Loading roles...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="rounded-2xl bg-red-500/10 border border-red-500/30 p-6">
      <div class="flex items-center gap-3">
        <ExclamationTriangleIcon class="w-6 h-6 text-red-400" />
        <p class="text-red-400 font-medium">{{ error }}</p>
      </div>
    </div>

    <!-- Roles Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6">
      <div
        v-for="role in filteredRoles"
        :key="role.id"
        class="group bg-slate-800/50 hover:bg-slate-800 backdrop-blur-sm rounded-2xl border border-slate-700/50 hover:border-slate-600/50 p-6 transition-all"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
              <ShieldCheckIcon class="w-5 h-5 text-white" />
            </div>
            <div>
              <h3 class="font-semibold text-white group-hover:text-amber-400 transition-colors">{{ role.name }}</h3>
              <p class="text-xs text-slate-400">Created {{ formatDate(role.created_at) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-1">
            <button
              @click="editRole(role)"
              class="p-2 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-700/50 transition-colors"
            >
              <PencilIcon class="w-4 h-4" />
            </button>
            <button
              @click="deleteRole(role)"
              class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>

        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Permissions</span>
            <span class="text-xs font-medium text-slate-500">{{ role.permissions?.length || 0 }} total</span>
          </div>
          <div v-if="role.permissions?.length" class="flex flex-wrap gap-1.5">
            <span
              v-for="perm in role.permissions.slice(0, 6)"
              :key="perm.id"
              class="px-2 py-1 text-xs font-medium rounded-md bg-slate-700/50 text-slate-300"
            >
              {{ perm.name }}
            </span>
            <span
              v-if="role.permissions.length > 6"
              class="px-2 py-1 text-xs font-medium rounded-md bg-amber-500/20 text-amber-400"
            >
              +{{ role.permissions.length - 6 }} more
            </span>
          </div>
          <p v-else class="text-sm text-slate-500 italic">No permissions assigned</p>
        </div>
      </div>

      <div v-if="filteredRoles.length === 0" class="col-span-full flex flex-col items-center justify-center py-12">
        <div class="w-16 h-16 rounded-2xl bg-slate-700/30 flex items-center justify-center mb-4">
          <ShieldCheckIcon class="w-8 h-8 text-slate-500" />
        </div>
        <h3 class="text-lg font-medium text-white mb-2">No roles found</h3>
        <p class="text-slate-400 text-center max-w-sm">{{ searchQuery ? 'Try adjusting your search query' : 'Create your first role to get started' }}</p>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
          <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-6 border-b border-slate-700 shrink-0">
              <h2 class="text-lg font-bold text-white">{{ editingRole ? 'Edit Role' : 'Create Role' }}</h2>
              <button @click="closeModal" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-6">
              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Role Name</label>
                <input
                  v-model="formData.name"
                  type="text"
                  placeholder="e.g., moderator, support"
                  class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                />
              </div>

              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <label class="block text-sm font-medium text-slate-300">Permissions</label>
                  <span class="text-xs text-slate-400">{{ formData.permissions.length }} selected</span>
                </div>

                <div v-if="loadingPermissions" class="flex items-center justify-center py-8">
                  <div class="w-8 h-8 border-4 border-amber-500/30 border-t-amber-500 rounded-full animate-spin"></div>
                </div>

                <div v-else class="space-y-4 max-h-[400px] overflow-y-auto p-4 bg-slate-900/30 rounded-xl border border-slate-700/50">
                  <div v-for="(perms, group) in permissionsByGroup" :key="group" class="space-y-2">
                    <div class="flex items-center justify-between sticky top-0 bg-slate-900/90 backdrop-blur py-2 -mx-2 px-2">
                      <h4 class="text-sm font-semibold text-amber-400 uppercase tracking-wider">{{ group }}</h4>
                      <button
                        @click="toggleGroup(group, perms)"
                        type="button"
                        class="text-xs text-slate-400 hover:text-white transition-colors"
                      >
                        {{ isGroupSelected(group, perms) ? 'Deselect All' : 'Select All' }}
                      </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                      <label
                        v-for="permission in perms"
                        :key="permission.id"
                        class="flex items-center gap-3 p-3 rounded-lg bg-slate-800/50 hover:bg-slate-700/50 cursor-pointer transition-colors"
                      >
                        <input
                          type="checkbox"
                          :value="permission.name"
                          v-model="formData.permissions"
                          class="w-4 h-4 rounded border-slate-600 text-amber-500 focus:ring-amber-500 focus:ring-offset-0 bg-slate-700"
                        />
                        <span class="text-sm text-slate-300">{{ permission.name }}</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 p-6 border-t border-slate-700 bg-slate-800/50 shrink-0">
              <button @click="closeModal" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl font-medium transition-colors">
                Cancel
              </button>
              <button
                @click="saveRole"
                :disabled="saving"
                class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : (editingRole ? 'Save Changes' : 'Create Role') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import { MagnifyingGlassIcon, PlusIcon, XMarkIcon, ShieldCheckIcon, PencilIcon, TrashIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const toast = useToast()
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
const filteredRoles = computed(() => {
  if (!searchQuery.value) return roles.value
  return roles.value.filter(role => role.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
})

onMounted(() => { fetchRoles(); fetchPermissions() })

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

const showCreateModal = () => { editingRole.value = null; formData.value = { name: '', permissions: [] }; showModal.value = true }
const editRole = (role) => { editingRole.value = role; formData.value = { name: role.name, permissions: role.permissions?.map(p => p.name) || [] }; showModal.value = true }
const closeModal = () => { showModal.value = false; editingRole.value = null; formData.value = { name: '', permissions: [] } }

const saveRole = async () => {
  if (!formData.value.name) { toast.error('Role name is required'); return }
  saving.value = true
  try {
    if (editingRole.value) {
      await api.patch(`/admin/roles/${editingRole.value.id}`, formData.value)
      toast.success('Role updated')
    } else {
      await api.post('/admin/roles', formData.value)
      toast.success('Role created')
    }
    closeModal(); fetchRoles()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save role')
  } finally {
    saving.value = false
  }
}

const deleteRole = async (role) => {
  if (!confirm(`Delete role "${role.name}"?`)) return
  try {
    await api.delete(`/admin/roles/${role.id}`)
    toast.success('Role deleted')
    fetchRoles()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to delete role')
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

const isGroupSelected = (group, perms) => perms.every(p => formData.value.permissions.includes(p.name))
const formatDate = (dateStr) => dateStr ? new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>

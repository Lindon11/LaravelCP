<template>
  <div class="modules-page">
    <div class="modules-header">
      <h1>📦 Module Manager</h1>
      <div class="header-actions">
        <button class="btn-secondary" @click="showUploadModal = true">
          <span>⬆️</span>
          Upload Module
        </button>
        <button class="btn-primary" @click="loadModules">
          <span>🔄</span>
          Refresh
        </button>
      </div>
    </div>

    <div class="tabs">
      <button 
        :class="{ active: activeTab === 'installed' }"
        @click="activeTab = 'installed'"
      >
        Installed Modules ({{ installedModules.length }})
      </button>
      <button 
        :class="{ active: activeTab === 'staging' }"
        @click="activeTab = 'staging'"
      >
        Staging ({{ stagingModules.length }})
      </button>
      <button 
        :class="{ active: activeTab === 'disabled' }"
        @click="activeTab = 'disabled'"
      >
        Disabled Modules ({{ disabledModules.length }})
      </button>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      Loading modules...
    </div>

    <div v-else-if="activeTab === 'installed'" class="modules-grid">
      <div v-if="installedModules.length === 0" class="no-modules">
        <p>No modules installed.</p>
        <p class="hint">Upload a module ZIP file to get started.</p>
      </div>
      
      <div v-for="module in installedModules" :key="module.slug" class="module-card installed">
        <div class="module-header">
          <div class="module-icon">{{ getModuleIcon(module.type) }}</div>
          <div class="module-info">
            <h3>{{ module.name }}</h3>
            <div class="module-meta">
              <span class="version">v{{ module.version }}</span>
              <span v-if="module.author" class="author">by {{ module.author }}</span>
            </div>
          </div>
          <div class="module-status">
            <span :class="['badge', module.enabled ? 'badge-success' : 'badge-disabled']">
              {{ module.enabled ? 'Enabled' : 'Disabled' }}
            </span>
          </div>
        </div>
        
        <p class="module-description">{{ module.description || 'No description available' }}</p>
        
        <div v-if="module.dependencies?.length" class="dependencies">
          <strong>Dependencies:</strong>
          <span v-for="dep in module.dependencies" :key="dep" class="dep-tag">{{ dep }}</span>
        </div>
        
        <div class="module-actions">
          <button 
            v-if="module.enabled"
            class="btn-action btn-warning"
            @click="disableModule(module.slug)"
          >
            Disable
          </button>
          <button 
            v-else
            class="btn-action btn-success"
            @click="enableModule(module.slug)"
          >
            Enable
          </button>
          <button 
            class="btn-action btn-danger"
            @click="confirmUninstall(module)"
          >
            Uninstall
          </button>
        </div>
      </div>
    </div>

    <div v-else-if="activeTab === 'staging'" class="modules-grid">
      <div v-if="stagingModules.length === 0" class="no-modules">
        <p>No modules in staging.</p>
        <p class="hint">Upload module ZIP files to stage them for installation.</p>
      </div>
      
      <div v-for="module in stagingModules" :key="module.slug" class="module-card staging">
        <div class="module-header">
          <div class="module-icon">{{ getModuleIcon(module.type) }}</div>
          <div class="module-info">
            <h3>{{ module.name }}</h3>
            <div class="module-meta">
              <span class="version">v{{ module.version }}</span>
              <span v-if="module.author" class="author">by {{ module.author }}</span>
            </div>
            <div v-if="module.is_upgrade" class="upgrade-badge">
              ⚠️ Upgrade: v{{ module.current_version }} → v{{ module.version }}
            </div>
          </div>
        </div>
        
        <p class="module-description">{{ module.description || 'No description available' }}</p>
        
        <div v-if="module.dependencies?.length" class="dependencies">
          <strong>Dependencies:</strong>
          <span v-for="dep in module.dependencies" :key="dep" class="dep-tag">{{ dep }}</span>
        </div>
        
        <div class="module-actions">
          <button 
            class="btn-action btn-primary"
            @click="installModule(module.slug)"
          >
            {{ module.is_upgrade ? 'Upgrade' : 'Install' }}
          </button>
          <button 
            class="btn-action btn-danger"
            @click="removeFromStaging(module.slug)"
          >
            Remove
          </button>
        </div>
      </div>
    </div>

    <div v-else-if="activeTab === 'disabled'" class="modules-grid">
      <div v-if="disabledModules.length === 0" class="no-modules">
        <p>No disabled modules.</p>
      </div>
      
      <div v-for="module in disabledModules" :key="module.slug" class="module-card disabled">
        <div class="module-header">
          <div class="module-icon">{{ getModuleIcon(module.type) }}</div>
          <div class="module-info">
            <h3>{{ module.name }}</h3>
            <div class="module-meta">
              <span class="version">v{{ module.version }}</span>
              <span v-if="module.author" class="author">by {{ module.author }}</span>
            </div>
          </div>
          <div class="module-status">
            <span class="badge badge-disabled">Disabled</span>
          </div>
        </div>
        
        <p class="module-description">{{ module.description || 'No description available' }}</p>
        
        <div v-if="module.dependencies?.length" class="dependencies">
          <strong>Dependencies:</strong>
          <span v-for="dep in module.dependencies" :key="dep" class="dep-tag">{{ dep }}</span>
        </div>
        
        <div class="module-actions">
          <button 
            class="btn-action btn-success"
            @click="reactivateModule(module.slug)"
          >
            Reactivate
          </button>
          <button 
            class="btn-action btn-danger"
            @click="confirmUninstall(module)"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Upload Modal -->
    <div v-if="showUploadModal" class="modal-overlay" @click="showUploadModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>Upload Module</h2>
          <button class="btn-close" @click="showUploadModal = false">×</button>
        </div>
        <div class="modal-body">
          <div class="upload-area" @dragover.prevent @drop.prevent="handleDrop">
            <input 
              ref="fileInput"
              type="file" 
              accept=".zip"
              @change="handleFileSelect"
              style="display: none"
            />
            <div class="upload-prompt" @click="$refs.fileInput.click()">
              <span class="upload-icon">📦</span>
              <p>Click to select or drag & drop module ZIP file</p>
              <p class="hint">Maximum file size: 10MB</p>
            </div>
          </div>
          
          <div v-if="selectedFile" class="file-info">
            <span>📄 {{ selectedFile.name }}</span>
            <span class="file-size">{{ formatFileSize(selectedFile.size) }}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="showUploadModal = false">Cancel</button>
          <button 
            class="btn-submit" 
            :disabled="!selectedFile || uploading"
            @click="uploadModule"
          >
            {{ uploading ? 'Uploading...' : 'Upload' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Uninstall Modal -->
    <div v-if="showUninstallModal" class="modal-overlay" @click="showUninstallModal = false">
      <div class="modal modal-confirm" @click.stop>
        <div class="modal-header">
          <h2>Confirm Uninstall</h2>
          <button class="btn-close" @click="showUninstallModal = false">×</button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to uninstall <strong>{{ moduleToUninstall?.name }}</strong>?</p>
          <p class="warning">This will remove all module files and database entries. This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="showUninstallModal = false">Cancel</button>
          <button class="btn-danger" @click="uninstallModule">Uninstall</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const { success: showSuccess, error: showError } = useToast()
const modules = ref([])
const loading = ref(true)
const activeTab = ref('installed')
const showUploadModal = ref(false)
const showUninstallModal = ref(false)
const selectedFile = ref(null)
const uploading = ref(false)
const moduleToUninstall = ref(null)
const fileInput = ref(null)

const installedModules = computed(() => 
  modules.value.filter(m => m.status === 'installed')
)

const stagingModules = computed(() => 
  modules.value.filter(m => m.status === 'staging')
)

const disabledModules = computed(() => 
  modules.value.filter(m => m.status === 'disabled')
)

const getModuleIcon = (type) => {
  const icons = {
    module: '🎮',
    theme: '🎨',
    plugin: '🔌'
  }
  return icons[type] || '📦'
}

const loadModules = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/modules')
    modules.value = response.data.modules || []
  } catch (error) {
    console.error('Failed to load modules:', error)
    showError('Failed to load modules')
  } finally {
    loading.value = false
  }
}

const enableModule = async (slug) => {
  try {
    await api.put(`/admin/modules/${slug}/enable`)
    showSuccess('Module enabled')
    await loadModules()
  } catch (error) {
    console.error('Failed to enable module:', error)
    showError(error.response?.data?.message || 'Failed to enable module')
  }
}

const disableModule = async (slug) => {
  try {
    await api.put(`/admin/modules/${slug}/disable`)
    showSuccess('Module disabled')
    await loadModules()
  } catch (error) {
    console.error('Failed to disable module:', error)
    showError('Failed to disable module')
  }
}

const installModule = async (slug) => {
  try {
    await api.post(`/admin/modules/${slug}/install`)
    showSuccess('Module installed successfully')
    await loadModules()
  } catch (error) {
    console.error('Failed to install module:', error)
    showError(error.response?.data?.message || 'Failed to install module')
  }
}

const confirmUninstall = (module) => {
  moduleToUninstall.value = module
  showUninstallModal.value = true
}

const uninstallModule = async () => {
  if (!moduleToUninstall.value) return
  
  try {
    await api.delete(`/admin/modules/${moduleToUninstall.value.slug}`)
    showSuccess('Module uninstalled successfully')
    showUninstallModal.value = false
    moduleToUninstall.value = null
    await loadModules()
  } catch (error) {
    console.error('Failed to uninstall module:', error)
    showError(error.response?.data?.message || 'Failed to uninstall module')
  }
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 10 * 1024 * 1024) {
      showError('File size must be less than 10MB')
      return
    }
    selectedFile.value = file
  }
}

const handleDrop = (event) => {
  const file = event.dataTransfer.files[0]
  if (file && file.name.endsWith('.zip')) {
    if (file.size > 10 * 1024 * 1024) {
      showError('File size must be less than 10MB')
      return
    }
    selectedFile.value = file
  } else {
    showError('Please upload a ZIP file')
  }
}

const uploadModule = async () => {
  if (!selectedFile.value) return
  
  uploading.value = true
  const formData = new FormData()
  formData.append('file', selectedFile.value)
  formData.append('type', 'module')
  
  try {
    const response = await api.post('/admin/modules/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    const data = response.data
    if (data.is_upgrade) {
      showSuccess(`Module uploaded to staging. Upgrade available: v${data.current_version} → v${data.new_version}`)
    } else {
      showSuccess('Module uploaded to staging successfully')
    }
    
    // Switch to staging tab after upload
    activeTab.value = 'staging'
    showUploadModal.value = false
    selectedFile.value = null
    await loadModules()
  } catch (error) {
    console.error('Failed to upload module:', error)
    showError(error.response?.data?.message || 'Failed to upload module')
  } finally {
    uploading.value = false
  }
}

const reactivateModule = async (slug) => {
  try {
    await api.put(`/admin/modules/${slug}/reactivate`)
    showSuccess('Module reactivated successfully')
    await loadModules()
  } catch (error) {
    console.error('Failed to reactivate module:', error)
    showError(error.response?.data?.message || 'Failed to reactivate module')
  }
}

const removeFromStaging = async (slug) => {
  try {
    await api.delete(`/admin/modules/${slug}/staging`)
    showSuccess('Module removed from staging')
    await loadModules()
  } catch (error) {
    console.error('Failed to remove from staging:', error)
    showError(error.response?.data?.message || 'Failed to remove from staging')
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

onMounted(() => {
  loadModules()
})
</script>

<style scoped>
.modules-page {
  width: 100%;
}

.modules-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.modules-header h1 {
  margin: 0;
  color: #ffffff;
  font-size: 1.375rem;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-primary,
.btn-secondary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  color: #ffffff;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.btn-secondary {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.btn-primary:hover,
.btn-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
}

.tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.tabs button {
  background: none;
  border: none;
  color: #94a3b8;
  padding: 0.625rem 1.25rem;
  cursor: pointer;
  font-size: 0.875rem;
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

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  color: #94a3b8;
  gap: 0.75rem;
  font-size: 0.875rem;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid rgba(148, 163, 184, 0.2);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.no-modules {
  text-align: center;
  padding: 2rem;
  color: #94a3b8;
  font-size: 0.875rem;
}

.no-modules .hint {
  margin-top: 0.375rem;
  font-size: 0.8125rem;
  color: #64748b;
}

.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1rem;
}

.module-card {
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  padding: 1rem;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
}

.module-card:hover {
  background: rgba(30, 41, 59, 1);
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.module-card.installed {
  border-left: 3px solid #10b981;
}

.module-card.staging {
  border-left: 3px solid #f59e0b;
}

.module-card.disabled {
  border-left: 3px solid #64748b;
  opacity: 0.8;
}

.module-card.available {
  border-left: 3px solid #8b5cf6;
}

.module-header {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.module-icon {
  font-size: 2rem;
  line-height: 1;
}

.module-info {
  flex: 1;
}

.module-info h3 {
  margin: 0 0 0.375rem 0;
  color: #ffffff;
  font-size: 1rem;
}

.module-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.version {
  color: #94a3b8;
  font-size: 0.75rem;
  background: rgba(148, 163, 184, 0.1);
  padding: 0.125rem 0.5rem;
  border-radius: 0.25rem;
}

.author {
  color: #64748b;
  font-size: 0.75rem;
  font-style: italic;
}

.upgrade-badge {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: #ffffff;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  margin-top: 0.5rem;
}

.module-status {
  display: flex;
  align-items: center;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.badge-success {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
}

.badge-disabled {
  background: rgba(148, 163, 184, 0.2);
  color: #94a3b8;
}

.module-description {
  color: #94a3b8;
  font-size: 0.8125rem;
  line-height: 1.5;
  margin: 0 0 0.75rem 0;
  flex: 1;
}

.dependencies {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
  align-items: center;
  margin-bottom: 0.75rem;
  font-size: 0.75rem;
}

.dependencies strong {
  color: #94a3b8;
}

.dep-tag {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

.module-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: auto;
  padding-top: 0.75rem;
  border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.btn-action {
  flex: 1;
  padding: 0.5rem 0.875rem;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action.btn-success {
  background: #10b981;
  color: white;
}

.btn-action.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-action.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-action.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-action:hover {
  transform: translateY(-1px);
  opacity: 0.9;
}

/* Modal Styles */
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
  padding: 1rem;
}

.modal {
  background: rgba(30, 41, 59, 0.98);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.75rem;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.modal-header h2 {
  margin: 0;
  color: #ffffff;
  font-size: 1.125rem;
}

.btn-close {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 1.5rem;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:hover {
  color: #ffffff;
}

.modal-body {
  padding: 1.25rem;
  overflow-y: auto;
}

.upload-area {
  border: 2px dashed rgba(148, 163, 184, 0.3);
  border-radius: 0.5rem;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.upload-area:hover {
  border-color: #3b82f6;
  background: rgba(59, 130, 246, 0.05);
}

.upload-prompt {
  color: #94a3b8;
}

.upload-icon {
  font-size: 2.5rem;
  display: block;
  margin-bottom: 0.75rem;
}

.upload-prompt p {
  margin: 0.375rem 0;
  font-size: 0.875rem;
}

.upload-prompt .hint {
  font-size: 0.75rem;
  color: #64748b;
}

.file-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.2);
  border-radius: 0.5rem;
  padding: 0.75rem;
  margin-top: 0.75rem;
  color: #ffffff;
  font-size: 0.875rem;
}

.file-size {
  color: #94a3b8;
  font-size: 0.875rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.625rem;
  padding: 1rem 1.25rem;
  border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.btn-cancel,
.btn-submit,
.btn-danger {
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
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

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.modal-confirm .warning {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 0.5rem;
  padding: 0.75rem;
  margin-top: 0.75rem;
  color: #fca5a5;
  font-size: 0.8125rem;
}
</style>

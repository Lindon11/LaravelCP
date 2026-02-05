<template>
  <div class="p-6 space-y-6">
    <!-- Actions -->
    <div class="flex items-center justify-end gap-3">
      <button @click="showUploadModal = true" class="flex items-center gap-2 px-4 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 hover:text-white rounded-xl border border-slate-700/50 font-medium transition-all">
        <ArrowUpTrayIcon class="w-5 h-5" />
        Upload Plugin
      </button>
      <button @click="loadModules" class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all">
        <ArrowPathIcon class="w-5 h-5" />
        Refresh
      </button>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 border-b border-slate-700/50">
      <button
        :class="[
          'px-5 py-3 text-sm font-medium transition-all border-b-2 -mb-px',
          activeTab === 'installed'
            ? 'text-amber-400 border-amber-400'
            : 'text-slate-400 border-transparent hover:text-white'
        ]"
        @click="activeTab = 'installed'"
      >
        <div class="flex items-center gap-2">
          <CheckCircleIcon class="w-4 h-4" />
          Installed ({{ installedModules.length }})
        </div>
      </button>
      <button
        :class="[
          'px-5 py-3 text-sm font-medium transition-all border-b-2 -mb-px',
          activeTab === 'staging'
            ? 'text-amber-400 border-amber-400'
            : 'text-slate-400 border-transparent hover:text-white'
        ]"
        @click="activeTab = 'staging'"
      >
        <div class="flex items-center gap-2">
          <ClockIcon class="w-4 h-4" />
          Staging ({{ stagingModules.length }})
        </div>
      </button>
      <button
        :class="[
          'px-5 py-3 text-sm font-medium transition-all border-b-2 -mb-px',
          activeTab === 'disabled'
            ? 'text-amber-400 border-amber-400'
            : 'text-slate-400 border-transparent hover:text-white'
        ]"
        @click="activeTab = 'disabled'"
      >
        <div class="flex items-center gap-2">
          <PauseCircleIcon class="w-4 h-4" />
          Disabled ({{ disabledModules.length }})
        </div>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-12">
      <div class="w-10 h-10 border-3 border-slate-700 border-t-amber-500 rounded-full animate-spin mb-4"></div>
      <p class="text-slate-400 text-sm">Loading plugins...</p>
    </div>

    <!-- Installed Modules Tab -->
    <div v-else-if="activeTab === 'installed'" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
      <div v-if="installedModules.length === 0" class="col-span-full text-center py-12">
        <CubeIcon class="w-16 h-16 text-slate-600 mx-auto mb-4" />
        <p class="text-slate-400">No plugins installed.</p>
        <p class="text-slate-500 text-sm mt-1">Upload a plugin ZIP file to get started.</p>
      </div>

      <div
        v-for="module in installedModules"
        :key="module.slug"
        class="bg-slate-800/30 backdrop-blur-sm rounded-2xl border border-slate-700/50 overflow-hidden hover:border-emerald-500/50 transition-all group"
      >
        <div class="p-5">
          <!-- Module Header -->
          <div class="flex items-start gap-4 mb-4">
            <div class="p-3 bg-slate-700/50 rounded-xl group-hover:bg-emerald-500/20 transition-colors">
              <component :is="getModuleIconComponent(module.type)" class="w-6 h-6 text-slate-300 group-hover:text-emerald-400" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-white truncate">{{ module.name }}</h3>
              <div class="flex flex-wrap items-center gap-2 mt-1">
                <span class="text-xs text-slate-400 bg-slate-700/50 px-2 py-0.5 rounded">v{{ module.version }}</span>
                <span v-if="module.author" class="text-xs text-slate-500 italic">by {{ module.author }}</span>
              </div>
            </div>
            <span :class="[
              'px-2.5 py-1 rounded-lg text-xs font-semibold uppercase',
              module.enabled
                ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                : 'bg-slate-600/20 text-slate-400 border border-slate-600/30'
            ]">
              {{ module.enabled ? 'Enabled' : 'Disabled' }}
            </span>
          </div>

          <!-- Description -->
          <p class="text-slate-400 text-sm leading-relaxed mb-4">{{ module.description || 'No description available' }}</p>

          <!-- Dependencies -->
          <div v-if="module.dependencies?.length" class="flex flex-wrap items-center gap-2 mb-4">
            <span class="text-xs text-slate-500">Dependencies:</span>
            <span v-for="dep in module.dependencies" :key="dep" class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded">
              {{ dep }}
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex border-t border-slate-700/50">
          <button
            v-if="module.enabled"
            @click="disableModule(module.slug)"
            class="flex-1 py-3 text-sm font-medium text-amber-400 hover:bg-amber-500/10 transition-colors"
          >
            Disable
          </button>
          <button
            v-else
            @click="enableModule(module.slug)"
            class="flex-1 py-3 text-sm font-medium text-emerald-400 hover:bg-emerald-500/10 transition-colors"
          >
            Enable
          </button>
          <div class="w-px bg-slate-700/50"></div>
          <button
            @click="confirmUninstall(module)"
            class="flex-1 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 transition-colors"
          >
            Uninstall
          </button>
        </div>
      </div>
    </div>

    <!-- Staging Modules Tab -->
    <div v-else-if="activeTab === 'staging'" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
      <div v-if="stagingModules.length === 0" class="col-span-full text-center py-12">
        <ClockIcon class="w-16 h-16 text-slate-600 mx-auto mb-4" />
        <p class="text-slate-400">No plugins in staging.</p>
        <p class="text-slate-500 text-sm mt-1">Upload plugin ZIP files to stage them for installation.</p>
      </div>

      <div
        v-for="module in stagingModules"
        :key="module.slug"
        class="bg-slate-800/30 backdrop-blur-sm rounded-2xl border border-slate-700/50 overflow-hidden hover:border-amber-500/50 transition-all group"
      >
        <div class="p-5">
          <!-- Module Header -->
          <div class="flex items-start gap-4 mb-4">
            <div class="p-3 bg-slate-700/50 rounded-xl group-hover:bg-amber-500/20 transition-colors">
              <component :is="getModuleIconComponent(module.type)" class="w-6 h-6 text-slate-300 group-hover:text-amber-400" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-white truncate">{{ module.name }}</h3>
              <div class="flex flex-wrap items-center gap-2 mt-1">
                <span class="text-xs text-slate-400 bg-slate-700/50 px-2 py-0.5 rounded">v{{ module.version }}</span>
                <span v-if="module.author" class="text-xs text-slate-500 italic">by {{ module.author }}</span>
              </div>
              <div v-if="module.is_upgrade" class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-500/20 text-amber-400 text-xs font-medium rounded-lg border border-amber-500/30">
                <ExclamationTriangleIcon class="w-4 h-4" />
                Upgrade: v{{ module.current_version }} → v{{ module.version }}
              </div>
            </div>
          </div>

          <!-- Description -->
          <p class="text-slate-400 text-sm leading-relaxed mb-4">{{ module.description || 'No description available' }}</p>

          <!-- Dependencies -->
          <div v-if="module.dependencies?.length" class="flex flex-wrap items-center gap-2 mb-4">
            <span class="text-xs text-slate-500">Dependencies:</span>
            <span v-for="dep in module.dependencies" :key="dep" class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded">
              {{ dep }}
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex border-t border-slate-700/50">
          <button
            @click="installModule(module.slug)"
            class="flex-1 py-3 text-sm font-medium text-emerald-400 hover:bg-emerald-500/10 transition-colors"
          >
            {{ module.is_upgrade ? 'Upgrade' : 'Install' }}
          </button>
          <div class="w-px bg-slate-700/50"></div>
          <button
            @click="removeFromStaging(module.slug)"
            class="flex-1 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 transition-colors"
          >
            Remove
          </button>
        </div>
      </div>
    </div>

    <!-- Disabled Modules Tab -->
    <div v-else-if="activeTab === 'disabled'" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
      <div v-if="disabledModules.length === 0" class="col-span-full text-center py-12">
        <PauseCircleIcon class="w-16 h-16 text-slate-600 mx-auto mb-4" />
        <p class="text-slate-400">No disabled modules.</p>
      </div>

      <div
        v-for="module in disabledModules"
        :key="module.slug"
        class="bg-slate-800/30 backdrop-blur-sm rounded-2xl border border-slate-700/50 overflow-hidden opacity-75 hover:opacity-100 hover:border-slate-500/50 transition-all group"
      >
        <div class="p-5">
          <!-- Module Header -->
          <div class="flex items-start gap-4 mb-4">
            <div class="p-3 bg-slate-700/50 rounded-xl">
              <component :is="getModuleIconComponent(module.type)" class="w-6 h-6 text-slate-400" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-white truncate">{{ module.name }}</h3>
              <div class="flex flex-wrap items-center gap-2 mt-1">
                <span class="text-xs text-slate-400 bg-slate-700/50 px-2 py-0.5 rounded">v{{ module.version }}</span>
                <span v-if="module.author" class="text-xs text-slate-500 italic">by {{ module.author }}</span>
              </div>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold uppercase bg-slate-600/20 text-slate-400 border border-slate-600/30">
              Disabled
            </span>
          </div>

          <!-- Description -->
          <p class="text-slate-400 text-sm leading-relaxed mb-4">{{ module.description || 'No description available' }}</p>

          <!-- Dependencies -->
          <div v-if="module.dependencies?.length" class="flex flex-wrap items-center gap-2 mb-4">
            <span class="text-xs text-slate-500">Dependencies:</span>
            <span v-for="dep in module.dependencies" :key="dep" class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded">
              {{ dep }}
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex border-t border-slate-700/50">
          <button
            @click="reactivateModule(module.slug)"
            class="flex-1 py-3 text-sm font-medium text-emerald-400 hover:bg-emerald-500/10 transition-colors"
          >
            Reactivate
          </button>
          <div class="w-px bg-slate-700/50"></div>
          <button
            @click="confirmUninstall(module)"
            class="flex-1 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 transition-colors"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Upload Modal -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showUploadModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showUploadModal = false">
        <div class="bg-slate-800 rounded-2xl border border-slate-700/50 shadow-2xl w-full max-w-md">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-slate-700/50">
            <h3 class="text-xl font-bold text-white">Upload Plugin</h3>
            <button @click="showUploadModal = false" class="p-1 hover:bg-slate-700/50 rounded-lg text-slate-400 hover:text-white transition-colors">
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6">
            <div
              class="border-2 border-dashed border-slate-600/50 hover:border-amber-500/50 rounded-xl p-8 text-center cursor-pointer transition-all"
              @dragover.prevent
              @drop.prevent="handleDrop"
              @click="fileInput.click()"
            >
              <input
                ref="fileInput"
                type="file"
                accept=".zip"
                class="hidden"
                @change="handleFileSelect"
              />
              <CloudArrowUpIcon class="w-12 h-12 text-slate-500 mx-auto mb-4" />
              <p class="text-slate-300 mb-1">Click to select or drag & drop</p>
              <p class="text-xs text-slate-500">Plugin ZIP file (max 10MB)</p>
            </div>

            <div v-if="selectedFile" class="mt-4 flex items-center justify-between p-4 bg-blue-500/10 border border-blue-500/30 rounded-xl">
              <div class="flex items-center gap-3">
                <DocumentIcon class="w-5 h-5 text-blue-400" />
                <span class="text-white text-sm">{{ selectedFile.name }}</span>
              </div>
              <span class="text-slate-400 text-sm">{{ formatFileSize(selectedFile.size) }}</span>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex justify-end gap-3 p-6 border-t border-slate-700/50">
            <button
              @click="showUploadModal = false"
              class="px-4 py-2.5 bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl font-medium transition-all"
            >
              Cancel
            </button>
            <button
              @click="uploadModule"
              :disabled="!selectedFile || uploading"
              class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ uploading ? 'Uploading...' : 'Upload' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Confirm Uninstall Modal -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showUninstallModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showUninstallModal = false">
        <div class="bg-slate-800 rounded-2xl border border-slate-700/50 shadow-2xl w-full max-w-md">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-slate-700/50">
            <h3 class="text-xl font-bold text-white">Confirm Uninstall</h3>
            <button @click="showUninstallModal = false" class="p-1 hover:bg-slate-700/50 rounded-lg text-slate-400 hover:text-white transition-colors">
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6">
            <p class="text-slate-300 mb-4">
              Are you sure you want to uninstall <strong class="text-white">{{ moduleToUninstall?.name }}</strong>?
            </p>
            <div class="flex items-start gap-3 p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
              <ExclamationTriangleIcon class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" />
              <p class="text-red-300 text-sm">
                This will remove all module files and database entries. This action cannot be undone.
              </p>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex justify-end gap-3 p-6 border-t border-slate-700/50">
            <button
              @click="showUninstallModal = false"
              class="px-4 py-2.5 bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl font-medium transition-all"
            >
              Cancel
            </button>
            <button
              @click="uninstallModule"
              class="px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl font-medium transition-all"
            >
              Uninstall
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import {
  CubeIcon,
  ArrowUpTrayIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  ClockIcon,
  PauseCircleIcon,
  XMarkIcon,
  CloudArrowUpIcon,
  DocumentIcon,
  ExclamationTriangleIcon,
  PuzzlePieceIcon,
  SwatchIcon,
  BoltIcon
} from '@heroicons/vue/24/outline'

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

const getModuleIconComponent = (type) => {
  const icons = {
    module: PuzzlePieceIcon,
    theme: SwatchIcon,
    plugin: BoltIcon
  }
  return icons[type] || CubeIcon
}

const loadModules = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/plugins')
    modules.value = response.data.plugins || []
  } catch (error) {
    console.error('Failed to load plugins:', error)
    showError('Failed to load plugins')
  } finally {
    loading.value = false
  }
}

const enableModule = async (slug) => {
  try {
    await api.put(`/admin/plugins/${slug}/enable`)
    showSuccess('Plugin enabled')
    await loadModules()
  } catch (error) {
    console.error('Failed to enable module:', error)
    showError(error.response?.data?.message || 'Failed to enable plugin')
  }
}

const disableModule = async (slug) => {
  try {
    await api.put(`/admin/plugins/${slug}/disable`)
    showSuccess('Plugin disabled')
    await loadModules()
  } catch (error) {
    console.error('Failed to disable module:', error)
    showError('Failed to disable plugin')
  }
}

const installModule = async (slug) => {
  try {
    await api.post(`/admin/plugins/${slug}/install`)
    showSuccess('Plugin installed successfully')
    await loadModules()
  } catch (error) {
    console.error('Failed to install module:', error)
    showError(error.response?.data?.message || 'Failed to install plugin')
  }
}

const confirmUninstall = (module) => {
  moduleToUninstall.value = module
  showUninstallModal.value = true
}

const uninstallModule = async () => {
  if (!moduleToUninstall.value) return

  try {
    await api.delete(`/admin/plugins/${moduleToUninstall.value.slug}`)
    showSuccess('Plugin uninstalled successfully')
    showUninstallModal.value = false
    moduleToUninstall.value = null
    await loadModules()
  } catch (error) {
    console.error('Failed to uninstall module:', error)
    showError(error.response?.data?.message || 'Failed to uninstall plugin')
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
    const response = await api.post('/admin/plugins/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    const data = response.data
    if (data.is_upgrade) {
      showSuccess(`Plugin uploaded to staging. Upgrade available: v${data.current_version} → v${data.new_version}`)
    } else {
      showSuccess('Plugin uploaded to staging successfully')
    }

    activeTab.value = 'staging'
    showUploadModal.value = false
    selectedFile.value = null
    await loadModules()
  } catch (error) {
    console.error('Failed to upload module:', error)
    showError(error.response?.data?.message || 'Failed to upload plugin')
  } finally {
    uploading.value = false
  }
}

const reactivateModule = async (slug) => {
  try {
    await api.put(`/admin/plugins/${slug}/reactivate`)
    showSuccess('Plugin reactivated successfully')
    await loadModules()
  } catch (error) {
    console.error('Failed to reactivate module:', error)
    showError(error.response?.data?.message || 'Failed to reactivate plugin')
  }
}

const removeFromStaging = async (slug) => {
  try {
    await api.delete(`/admin/plugins/${slug}/staging`)
    showSuccess('Plugin removed from staging')
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
/* Minimal custom styles - using Tailwind CSS */
</style>

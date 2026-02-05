<template>
  <div class="space-y-6">
    <!-- Actions -->
    <div class="flex items-center justify-end">
      <button
        @click="showCreateBackupModal = true"
        class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:opacity-90 transition-opacity"
      >
        <PlusIcon class="w-5 h-5" />
        Create Backup
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-400">Total Backups</p>
            <p class="text-3xl font-bold text-white mt-1">{{ stats.total_backups }}</p>
          </div>
          <div class="p-3 rounded-xl bg-emerald-500/20">
            <CircleStackIcon class="w-6 h-6 text-emerald-400" />
          </div>
        </div>
      </div>

      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-400">Storage Used</p>
            <p class="text-3xl font-bold text-white mt-1">{{ stats.storage_used }}</p>
          </div>
          <div class="p-3 rounded-xl bg-blue-500/20">
            <ServerIcon class="w-6 h-6 text-blue-400" />
          </div>
        </div>
      </div>

      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-400">Last Backup</p>
            <p class="text-xl font-bold text-white mt-1">{{ stats.last_backup || 'Never' }}</p>
          </div>
          <div class="p-3 rounded-xl bg-violet-500/20">
            <ClockIcon class="w-6 h-6 text-violet-400" />
          </div>
        </div>
      </div>

      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-400">Auto-Backup</p>
            <p class="text-xl font-bold mt-1" :class="settings.auto_backup_enabled ? 'text-emerald-400' : 'text-slate-400'">
              {{ settings.auto_backup_enabled ? 'Enabled' : 'Disabled' }}
            </p>
          </div>
          <div class="p-3 rounded-xl" :class="settings.auto_backup_enabled ? 'bg-emerald-500/20' : 'bg-slate-700'">
            <CalendarDaysIcon :class="['w-6 h-6', settings.auto_backup_enabled ? 'text-emerald-400' : 'text-slate-400']" />
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex items-center gap-1 p-1 bg-slate-800/50 rounded-xl w-fit">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="[
          'px-4 py-2 rounded-lg font-medium transition-all text-sm',
          activeTab === tab.id
            ? 'bg-slate-700 text-white'
            : 'text-slate-400 hover:text-white'
        ]"
      >
        {{ tab.name }}
      </button>
    </div>

    <!-- Backups List Tab -->
    <div v-if="activeTab === 'backups'" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-900/50">
            <tr class="text-left text-sm text-slate-400">
              <th class="px-6 py-4 font-medium">Backup Name</th>
              <th class="px-6 py-4 font-medium">Type</th>
              <th class="px-6 py-4 font-medium">Size</th>
              <th class="px-6 py-4 font-medium">Created</th>
              <th class="px-6 py-4 font-medium">Status</th>
              <th class="px-6 py-4 font-medium text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/50">
            <tr v-if="loading" class="text-center">
              <td colspan="6" class="px-6 py-12">
                <div class="flex items-center justify-center gap-3 text-slate-400">
                  <ArrowPathIcon class="w-5 h-5 animate-spin" />
                  Loading backups...
                </div>
              </td>
            </tr>
            <tr v-else-if="backups.length === 0" class="text-center">
              <td colspan="6" class="px-6 py-12">
                <CircleStackIcon class="w-12 h-12 text-slate-600 mx-auto mb-3" />
                <p class="text-slate-400">No backups found</p>
                <p class="text-sm text-slate-500">Create your first backup to get started</p>
              </td>
            </tr>
            <tr
              v-for="backup in backups"
              :key="backup.id"
              class="hover:bg-slate-700/30 transition-colors"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="p-2 rounded-lg bg-slate-700">
                    <CircleStackIcon class="w-5 h-5 text-emerald-400" />
                  </div>
                  <div>
                    <p class="font-medium text-white">{{ backup.name }}</p>
                    <p class="text-sm text-slate-400">{{ backup.filename }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-lg',
                  backup.type === 'full' ? 'bg-emerald-500/20 text-emerald-400' :
                  backup.type === 'database' ? 'bg-blue-500/20 text-blue-400' :
                  'bg-amber-500/20 text-amber-400'
                ]">
                  {{ backup.type }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-300">{{ backup.size }}</td>
              <td class="px-6 py-4 text-slate-300">{{ backup.created_at }}</td>
              <td class="px-6 py-4">
                <span :class="[
                  'flex items-center gap-1.5 text-sm',
                  backup.status === 'completed' ? 'text-emerald-400' :
                  backup.status === 'in_progress' ? 'text-amber-400' :
                  'text-red-400'
                ]">
                  <span :class="[
                    'w-2 h-2 rounded-full',
                    backup.status === 'completed' ? 'bg-emerald-400' :
                    backup.status === 'in_progress' ? 'bg-amber-400 animate-pulse' :
                    'bg-red-400'
                  ]"></span>
                  {{ backup.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="downloadBackup(backup)"
                    class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-colors"
                    title="Download"
                  >
                    <ArrowDownTrayIcon class="w-5 h-5" />
                  </button>
                  <button
                    @click="confirmRestore(backup)"
                    class="p-2 text-slate-400 hover:text-emerald-400 hover:bg-slate-700 rounded-lg transition-colors"
                    title="Restore"
                  >
                    <ArrowPathRoundedSquareIcon class="w-5 h-5" />
                  </button>
                  <button
                    @click="confirmDelete(backup)"
                    class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-700 rounded-lg transition-colors"
                    title="Delete"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Schedule Tab -->
    <div v-if="activeTab === 'schedule'" class="space-y-6">
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
          <CalendarDaysIcon class="w-5 h-5 text-emerald-400" />
          Automatic Backup Schedule
        </h3>

        <div class="space-y-6">
          <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-lg bg-emerald-500/20">
                <ClockIcon class="w-5 h-5 text-emerald-400" />
              </div>
              <div>
                <p class="font-medium text-white">Enable Automatic Backups</p>
                <p class="text-sm text-slate-400">Automatically create backups on a schedule</p>
              </div>
            </div>
            <button
              @click="settings.auto_backup_enabled = !settings.auto_backup_enabled"
              :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                settings.auto_backup_enabled ? 'bg-emerald-500' : 'bg-slate-600'
              ]"
            >
              <span
                :class="[
                  'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                  settings.auto_backup_enabled ? 'translate-x-6' : 'translate-x-1'
                ]"
              />
            </button>
          </div>

          <div v-if="settings.auto_backup_enabled" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Backup Frequency</label>
              <select
                v-model="settings.backup_frequency"
                class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
              >
                <option value="hourly">Every Hour</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Backup Time</label>
              <input
                v-model="settings.backup_time"
                type="time"
                class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Backup Type</label>
              <select
                v-model="settings.backup_type"
                class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
              >
                <option value="full">Full (Database + Files)</option>
                <option value="database">Database Only</option>
                <option value="files">Files Only</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Keep Last N Backups</label>
              <input
                v-model.number="settings.retention_count"
                type="number"
                min="1"
                max="100"
                class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
              />
            </div>
          </div>

          <div class="flex justify-end">
            <button
              @click="saveScheduleSettings"
              class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:opacity-90 transition-opacity"
            >
              Save Schedule Settings
            </button>
          </div>
        </div>
      </div>

      <!-- Upcoming Backups -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <CalendarIcon class="w-5 h-5 text-blue-400" />
          Upcoming Scheduled Backups
        </h3>
        <div class="space-y-3">
          <div
            v-for="scheduled in upcomingBackups"
            :key="scheduled.id"
            class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl"
          >
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-lg bg-blue-500/20">
                <ClockIcon class="w-5 h-5 text-blue-400" />
              </div>
              <div>
                <p class="font-medium text-white">{{ scheduled.name }}</p>
                <p class="text-sm text-slate-400">{{ scheduled.type }} backup</p>
              </div>
            </div>
            <span class="text-slate-300">{{ scheduled.scheduled_at }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Storage Tab -->
    <div v-if="activeTab === 'storage'" class="space-y-6">
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
          <ServerIcon class="w-5 h-5 text-blue-400" />
          Storage Configuration
        </h3>

        <div class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Storage Driver</label>
            <select
              v-model="settings.storage_driver"
              class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
            >
              <option value="local">Local Storage</option>
              <option value="s3">Amazon S3</option>
              <option value="gcs">Google Cloud Storage</option>
              <option value="dropbox">Dropbox</option>
            </select>
          </div>

          <!-- Local Storage Settings -->
          <div v-if="settings.storage_driver === 'local'" class="p-4 bg-slate-900/50 rounded-xl">
            <p class="text-sm text-slate-400 mb-2">Storage Path</p>
            <p class="text-white font-mono text-sm">{{ settings.local_path }}</p>
          </div>

          <!-- S3 Settings -->
          <div v-if="settings.storage_driver === 's3'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">S3 Bucket</label>
              <input
                v-model="settings.s3_bucket"
                type="text"
                class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                placeholder="my-backup-bucket"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">S3 Region</label>
              <input
                v-model="settings.s3_region"
                type="text"
                class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                placeholder="us-east-1"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Access Key ID</label>
              <input
                v-model="settings.s3_key"
                type="password"
                class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                placeholder="••••••••"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-2">Secret Access Key</label>
              <input
                v-model="settings.s3_secret"
                type="password"
                class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                placeholder="••••••••"
              />
            </div>
          </div>

          <div class="flex justify-end gap-3">
            <button
              @click="testStorageConnection"
              class="px-4 py-2 bg-slate-700 text-white rounded-xl font-medium hover:bg-slate-600 transition-colors"
            >
              Test Connection
            </button>
            <button
              @click="saveStorageSettings"
              class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:opacity-90 transition-opacity"
            >
              Save Storage Settings
            </button>
          </div>
        </div>
      </div>

      <!-- Storage Usage -->
      <div class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <ChartPieIcon class="w-5 h-5 text-violet-400" />
          Storage Usage
        </h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-slate-300">Used</span>
            <span class="text-white font-medium">{{ storageUsage.used }} / {{ storageUsage.total }}</span>
          </div>
          <div class="w-full h-3 bg-slate-700 rounded-full overflow-hidden">
            <div
              class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all"
              :style="{ width: `${storageUsage.percentage}%` }"
            ></div>
          </div>
          <p class="text-sm text-slate-400">{{ storageUsage.percentage }}% of storage used</p>
        </div>
      </div>
    </div>

    <!-- Create Backup Modal -->
    <div v-if="showCreateBackupModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showCreateBackupModal = false"></div>
      <div class="relative w-full max-w-md rounded-2xl bg-slate-800 border border-slate-700 p-6 shadow-xl">
        <h3 class="text-xl font-semibold text-white flex items-center gap-2">
          <CircleStackIcon class="w-6 h-6 text-emerald-400" />
          Create New Backup
        </h3>

                <div class="mt-6 space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Backup Name</label>
                    <input
                      v-model="newBackup.name"
                      type="text"
                      class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                      placeholder="Manual backup"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Backup Type</label>
                    <select
                      v-model="newBackup.type"
                      class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                    >
                      <option value="full">Full (Database + Files)</option>
                      <option value="database">Database Only</option>
                      <option value="files">Files Only</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Description (Optional)</label>
                    <textarea
                      v-model="newBackup.description"
                      rows="3"
                      class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 resize-none"
                      placeholder="Backup before major update..."
                    ></textarea>
                  </div>
                </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="showCreateBackupModal = false"
            class="px-4 py-2 text-slate-300 hover:text-white transition-colors"
          >
            Cancel
          </button>
          <button
            @click="createBackup"
            :disabled="!newBackup.name"
            class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:opacity-90 transition-opacity disabled:opacity-50"
          >
            Create Backup
          </button>
        </div>
      </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div v-if="showRestoreModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showRestoreModal = false"></div>
      <div class="relative w-full max-w-md rounded-2xl bg-slate-800 border border-slate-700 p-6 shadow-xl">
        <h3 class="text-xl font-semibold text-white flex items-center gap-2">
          <ExclamationTriangleIcon class="w-6 h-6 text-amber-400" />
          Confirm Restore
        </h3>

                <div class="mt-4">
                  <p class="text-slate-300">
                    Are you sure you want to restore from backup <span class="font-semibold text-white">"{{ selectedBackup?.name }}"</span>?
                  </p>
                  <p class="mt-2 text-sm text-amber-400">
                    Warning: This will overwrite current data and cannot be undone.
                  </p>
                </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="showRestoreModal = false"
            class="px-4 py-2 text-slate-300 hover:text-white transition-colors"
          >
            Cancel
          </button>
          <button
            @click="restoreBackup"
            class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl font-medium hover:opacity-90 transition-opacity"
          >
            Restore Backup
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>
      <div class="relative w-full max-w-md rounded-2xl bg-slate-800 border border-slate-700 p-6 shadow-xl">
        <h3 class="text-xl font-semibold text-white flex items-center gap-2">
          <TrashIcon class="w-6 h-6 text-red-400" />
          Delete Backup
        </h3>

                <div class="mt-4">
                  <p class="text-slate-300">
                    Are you sure you want to delete backup <span class="font-semibold text-white">"{{ selectedBackup?.name }}"</span>?
                  </p>
                  <p class="mt-2 text-sm text-red-400">
                    This action cannot be undone.
                  </p>
                </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-slate-300 hover:text-white transition-colors"
          >
            Cancel
          </button>
          <button
            @click="deleteBackup"
            class="px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl font-medium hover:opacity-90 transition-opacity"
          >
            Delete Backup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
  CircleStackIcon,
  ServerIcon,
  ClockIcon,
  CalendarDaysIcon,
  CalendarIcon,
  PlusIcon,
  ArrowPathIcon,
  ArrowDownTrayIcon,
  ArrowPathRoundedSquareIcon,
  TrashIcon,
  ExclamationTriangleIcon,
  ChartPieIcon
} from '@heroicons/vue/24/outline'
import api from '../services/api'

const loading = ref(false)
const activeTab = ref('backups')

const tabs = [
  { id: 'backups', name: 'Backups' },
  { id: 'schedule', name: 'Schedule' },
  { id: 'storage', name: 'Storage' }
]

const stats = ref({
  total_backups: 0,
  storage_used: '0 MB',
  last_backup: null
})

const backups = ref([])

const settings = ref({
  auto_backup_enabled: false,
  backup_frequency: 'daily',
  backup_time: '03:00',
  backup_type: 'full',
  retention_count: 7,
  storage_driver: 'local',
  local_path: '/storage/backups',
  s3_bucket: '',
  s3_region: '',
  s3_key: '',
  s3_secret: ''
})

const storageUsage = ref({
  used: '2.5 GB',
  total: '10 GB',
  percentage: 25
})

const upcomingBackups = ref([
  { id: 1, name: 'Daily Backup', type: 'Full', scheduled_at: 'Tomorrow at 03:00' },
  { id: 2, name: 'Daily Backup', type: 'Full', scheduled_at: 'In 2 days at 03:00' }
])

const showCreateBackupModal = ref(false)
const showRestoreModal = ref(false)
const showDeleteModal = ref(false)
const selectedBackup = ref(null)

const newBackup = ref({
  name: '',
  type: 'full',
  description: ''
})

onMounted(async () => {
  await loadBackups()
  await loadSettings()
})

async function loadBackups() {
  loading.value = true
  try {
    const response = await api.get('/admin/backups')
    backups.value = response.data.backups || []
    stats.value = response.data.stats || stats.value
  } catch (error) {
    console.error('Failed to load backups:', error)
    // Sample data
    backups.value = [
      { id: 1, name: 'Daily Backup', filename: 'backup_2025_02_04_030000.zip', type: 'full', size: '256 MB', created_at: '2025-02-04 03:00', status: 'completed' },
      { id: 2, name: 'Manual Backup', filename: 'backup_2025_02_03_142530.zip', type: 'database', size: '128 MB', created_at: '2025-02-03 14:25', status: 'completed' },
      { id: 3, name: 'Pre-update Backup', filename: 'backup_2025_02_02_180000.zip', type: 'full', size: '245 MB', created_at: '2025-02-02 18:00', status: 'completed' }
    ]
    stats.value = {
      total_backups: 3,
      storage_used: '629 MB',
      last_backup: '2 hours ago'
    }
  } finally {
    loading.value = false
  }
}

async function loadSettings() {
  try {
    const response = await api.get('/admin/backups/settings')
    Object.assign(settings.value, response.data)
  } catch (error) {
    console.error('Failed to load backup settings:', error)
  }
}

async function createBackup() {
  try {
    await api.post('/admin/backups', newBackup.value)
    showCreateBackupModal.value = false
    newBackup.value = { name: '', type: 'full', description: '' }
    await loadBackups()
  } catch (error) {
    console.error('Failed to create backup:', error)
    alert('Failed to create backup')
  }
}

function confirmRestore(backup) {
  selectedBackup.value = backup
  showRestoreModal.value = true
}

async function restoreBackup() {
  try {
    await api.post(`/admin/backups/${selectedBackup.value.id}/restore`)
    showRestoreModal.value = false
    alert('Backup restoration started')
  } catch (error) {
    console.error('Failed to restore backup:', error)
    alert('Failed to restore backup')
  }
}

function confirmDelete(backup) {
  selectedBackup.value = backup
  showDeleteModal.value = true
}

async function deleteBackup() {
  try {
    await api.delete(`/admin/backups/${selectedBackup.value.id}`)
    showDeleteModal.value = false
    await loadBackups()
  } catch (error) {
    console.error('Failed to delete backup:', error)
    alert('Failed to delete backup')
  }
}

function downloadBackup(backup) {
  window.open(`/api/admin/backups/${backup.id}/download`, '_blank')
}

async function saveScheduleSettings() {
  try {
    await api.put('/admin/backups/settings', {
      auto_backup_enabled: settings.value.auto_backup_enabled,
      backup_frequency: settings.value.backup_frequency,
      backup_time: settings.value.backup_time,
      backup_type: settings.value.backup_type,
      retention_count: settings.value.retention_count
    })
    alert('Schedule settings saved')
  } catch (error) {
    console.error('Failed to save schedule settings:', error)
    alert('Failed to save settings')
  }
}

async function saveStorageSettings() {
  try {
    await api.put('/admin/backups/storage', {
      storage_driver: settings.value.storage_driver,
      s3_bucket: settings.value.s3_bucket,
      s3_region: settings.value.s3_region,
      s3_key: settings.value.s3_key,
      s3_secret: settings.value.s3_secret
    })
    alert('Storage settings saved')
  } catch (error) {
    console.error('Failed to save storage settings:', error)
    alert('Failed to save settings')
  }
}

async function testStorageConnection() {
  try {
    await api.post('/admin/backups/test-storage')
    alert('Storage connection successful!')
  } catch (error) {
    console.error('Storage connection failed:', error)
    alert('Storage connection failed')
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="relative w-full sm:w-80">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search announcements..."
          @input="debouncedSearch"
          class="w-full pl-10 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
        />
      </div>
      <div class="flex items-center gap-3">
        <select
          v-model="filterType"
          @change="fetchAnnouncements"
          class="px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all"
        >
          <option value="">All Types</option>
          <option v-for="t in announcementTypes" :key="t.name" :value="t.name">{{ t.label }}</option>
        </select>
        <button
          @click="showCreateModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all"
        >
          <PlusIcon class="w-5 h-5" />
          New Announcement
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-12 h-12 border-4 border-amber-500/30 border-t-amber-500 rounded-full animate-spin mb-4"></div>
        <p class="text-slate-400">Loading announcements...</p>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="announcements.length === 0" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-700/30 flex items-center justify-center mb-4">
          <MegaphoneIcon class="w-8 h-8 text-slate-500" />
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">No announcements</h3>
        <p class="text-slate-400 text-center max-w-sm mb-4">Create your first announcement to broadcast to players</p>
        <button @click="showCreateModal" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium transition-colors">
          Create Announcement
        </button>
      </div>
    </div>

    <!-- Announcements List -->
    <div v-else class="space-y-4">
      <TransitionGroup name="list">
        <div
          v-for="announcement in announcements"
          :key="announcement.id"
          class="group bg-slate-800/50 hover:bg-slate-800 backdrop-blur-sm rounded-2xl border border-slate-700/50 hover:border-slate-600/50 overflow-hidden transition-all"
        >
          <!-- Type Indicator -->
          <div :class="['h-1', typeColors[announcement.type]?.bar || 'bg-slate-600']" />

          <div class="p-6">
            <div class="flex items-start gap-4">
              <!-- Type Icon -->
              <div :class="['w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0', typeColors[announcement.type]?.bg || 'bg-slate-700']">
                <component :is="typeIcons[announcement.type] || MegaphoneIcon" :class="['w-6 h-6', typeColors[announcement.type]?.icon || 'text-slate-400']" />
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-lg font-semibold text-white group-hover:text-amber-400 transition-colors truncate">
                    {{ announcement.title }}
                  </h3>
                  <span v-if="announcement.is_sticky" class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-400 text-xs font-medium">
                    <PinIcon class="w-3 h-3 mr-1" />
                    Pinned
                  </span>
                  <span :class="[
                    'inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium',
                    announcement.is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-600/50 text-slate-400'
                  ]">
                    {{ announcement.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>

                <p class="text-slate-400 text-sm line-clamp-2 mb-3">{{ announcement.message }}</p>

                <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500">
                  <span class="inline-flex items-center gap-1.5">
                    <CalendarIcon class="w-4 h-4" />
                    {{ formatDate(announcement.published_at || announcement.created_at) }}
                  </span>
                  <span v-if="announcement.expires_at" class="inline-flex items-center gap-1.5">
                    <ClockIcon class="w-4 h-4" />
                    Expires {{ formatDate(announcement.expires_at) }}
                  </span>
                  <span class="inline-flex items-center gap-1.5 capitalize">
                    <UsersIcon class="w-4 h-4" />
                    {{ announcement.target || 'All Users' }}
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-2">
                <button
                  @click="editAnnouncement(announcement)"
                  class="p-2 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-700/50 transition-colors"
                >
                  <PencilIcon class="w-5 h-5" />
                </button>
                <button
                  @click="deleteAnnouncement(announcement)"
                  class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                >
                  <TrashIcon class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </TransitionGroup>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
          <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-6 border-b border-slate-700 shrink-0">
              <h2 class="text-lg font-bold text-white">{{ editingItem ? 'Edit Announcement' : 'New Announcement' }}</h2>
              <button @click="closeModal" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-5">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-slate-300">Title *</label>
                  <input
                    v-model="formData.title"
                    type="text"
                    placeholder="Announcement title"
                    class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                  />
                </div>

                <div class="space-y-2">
                  <label class="block text-sm font-medium text-slate-300">Type *</label>
                  <select
                    v-model="formData.type"
                    class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                  >
                    <option v-for="t in announcementTypes" :key="t.name" :value="t.name">{{ t.label }}</option>
                  </select>
                </div>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Message *</label>
                <textarea
                  v-model="formData.message"
                  rows="4"
                  placeholder="Write your announcement..."
                  class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all resize-none"
                />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-slate-300">Target Audience</label>
                  <select
                    v-model="formData.target"
                    class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                  >
                    <option value="all">All Users</option>
                    <option value="level_range">Level Range</option>
                    <option value="location">Specific Location</option>
                  </select>
                </div>

                <div class="space-y-2">
                  <label class="block text-sm font-medium text-slate-300">Published At</label>
                  <input
                    v-model="formData.published_at"
                    type="datetime-local"
                    class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                  />
                </div>
              </div>

              <div v-if="formData.target === 'level_range'" class="grid grid-cols-2 gap-5">
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-slate-300">Min Level</label>
                  <input
                    v-model.number="formData.min_level"
                    type="number"
                    min="1"
                    class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                  />
                </div>
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-slate-300">Max Level</label>
                  <input
                    v-model.number="formData.max_level"
                    type="number"
                    min="1"
                    class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                  />
                </div>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Expires At</label>
                <input
                  v-model="formData.expires_at"
                  type="datetime-local"
                  class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                />
                <p class="text-xs text-slate-500">Leave empty for no expiration</p>
              </div>

              <div class="flex items-center gap-6 pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="formData.is_active" type="checkbox" class="w-5 h-5 rounded border-slate-600 text-amber-500 focus:ring-amber-500 focus:ring-offset-0 bg-slate-700" />
                  <span class="text-sm text-slate-300">Active</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="formData.is_sticky" type="checkbox" class="w-5 h-5 rounded border-slate-600 text-amber-500 focus:ring-amber-500 focus:ring-offset-0 bg-slate-700" />
                  <span class="text-sm text-slate-300">Pin to Top</span>
                </label>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 p-6 border-t border-slate-700 bg-slate-800/50 shrink-0">
              <button @click="closeModal" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl font-medium transition-colors">
                Cancel
              </button>
              <button
                @click="saveAnnouncement"
                :disabled="saving"
                class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : (editingItem ? 'Save Changes' : 'Publish') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import {
  MagnifyingGlassIcon, PlusIcon, XMarkIcon, MegaphoneIcon, PencilIcon, TrashIcon,
  CalendarIcon, ClockIcon, UsersIcon, NewspaperIcon, SparklesIcon, WrenchScrewdriverIcon,
  RocketLaunchIcon, ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// Pin icon as custom component
const PinIcon = { template: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/></svg>' }

const toast = useToast()
const announcementTypes = ref([])
const announcements = ref([])
const loading = ref(false)
const searchQuery = ref('')
const filterType = ref('')
const showModal = ref(false)
const editingItem = ref(null)
const saving = ref(false)
let searchTimeout = null

const defaultFormData = {
  title: '', message: '', type: 'news', target: 'all', min_level: null, max_level: null,
  published_at: null, expires_at: null, is_active: true, is_sticky: false, created_by: null
}
const formData = ref({ ...defaultFormData })

const typeIcons = { news: NewspaperIcon, event: SparklesIcon, maintenance: WrenchScrewdriverIcon, update: RocketLaunchIcon, alert: ExclamationTriangleIcon }

const colorClassMap = {
  blue: { bar: 'bg-blue-500', bg: 'bg-blue-500/20', icon: 'text-blue-400' },
  purple: { bar: 'bg-purple-500', bg: 'bg-purple-500/20', icon: 'text-purple-400' },
  amber: { bar: 'bg-amber-500', bg: 'bg-amber-500/20', icon: 'text-amber-400' },
  emerald: { bar: 'bg-emerald-500', bg: 'bg-emerald-500/20', icon: 'text-emerald-400' },
  red: { bar: 'bg-red-500', bg: 'bg-red-500/20', icon: 'text-red-400' },
  slate: { bar: 'bg-slate-500', bg: 'bg-slate-500/20', icon: 'text-slate-400' },
  green: { bar: 'bg-green-500', bg: 'bg-green-500/20', icon: 'text-green-400' },
  cyan: { bar: 'bg-cyan-500', bg: 'bg-cyan-500/20', icon: 'text-cyan-400' },
  pink: { bar: 'bg-pink-500', bg: 'bg-pink-500/20', icon: 'text-pink-400' },
  orange: { bar: 'bg-orange-500', bg: 'bg-orange-500/20', icon: 'text-orange-400' },
}
const getTypeColors = (typeName) => {
  const found = announcementTypes.value.find(t => t.name === typeName)
  return colorClassMap[found?.color] || colorClassMap.slate
}
const typeColors = new Proxy({}, { get: (_, prop) => getTypeColors(prop) })

onMounted(async () => {
  try { announcementTypes.value = (await api.get('/admin/announcement-types')).data } catch(e) {}
  fetchAnnouncements()
})

const fetchAnnouncements = async () => {
  loading.value = true
  try {
    const params = { search: searchQuery.value, type: filterType.value }
    const response = await api.get('/admin/content/announcements', { params })
    announcements.value = response.data.data || response.data
  } catch (err) {
    toast.error('Failed to load announcements')
  } finally {
    loading.value = false
  }
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchAnnouncements, 300)
}

const showCreateModal = () => { editingItem.value = null; formData.value = { ...defaultFormData }; showModal.value = true }
const editAnnouncement = (item) => { editingItem.value = item; formData.value = { ...item }; showModal.value = true }
const closeModal = () => { showModal.value = false; editingItem.value = null }

const saveAnnouncement = async () => {
  if (!formData.value.title || !formData.value.message) { toast.error('Title and message are required'); return }
  saving.value = true
  try {
    if (editingItem.value) {
      await api.patch(`/admin/content/announcements/${editingItem.value.id}`, formData.value)
      toast.success('Announcement updated')
    } else {
      await api.post('/admin/content/announcements', formData.value)
      toast.success('Announcement published')
    }
    closeModal(); fetchAnnouncements()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save announcement')
  } finally {
    saving.value = false
  }
}

const deleteAnnouncement = async (item) => {
  if (!confirm(`Delete "${item.title}"?`)) return
  try {
    await api.delete(`/admin/content/announcements/${item.id}`)
    toast.success('Announcement deleted')
    fetchAnnouncements()
  } catch (err) {
    toast.error('Failed to delete announcement')
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'Not set'
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.list-enter-active, .list-leave-active { transition: all 0.3s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(-10px); }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>

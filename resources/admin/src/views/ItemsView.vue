<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-white">Item Management</h1>
        <p class="text-slate-400 mt-1">Manage game items by category</p>
      </div>
      <button
        @click="showCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg shadow-amber-500/25"
      >
        <PlusIcon class="w-5 h-5" />
        Create Item
      </button>
    </div>

    <!-- Type Tabs -->
    <div class="flex flex-wrap gap-2">
      <button
        v-for="tab in itemTypes"
        :key="tab.value"
        @click="activeType = tab.value"
        :class="[
          'px-4 py-2.5 rounded-xl font-medium transition-all flex items-center gap-2',
          activeType === tab.value
            ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/25'
            : 'bg-slate-800/50 text-slate-300 hover:bg-slate-700/50 border border-slate-700/50'
        ]"
      >
        <component :is="tab.icon" class="w-5 h-5" />
        {{ tab.label }}
        <span :class="[
          'px-2 py-0.5 rounded-full text-xs font-semibold',
          activeType === tab.value ? 'bg-white/20' : 'bg-slate-700'
        ]">
          {{ getTypeCount(tab.value) }}
        </span>
      </button>
    </div>

    <!-- Search -->
    <div class="relative max-w-md">
      <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
      <input
        v-model="searchQuery"
        @input="debouncedSearch"
        type="text"
        placeholder="Search items..."
        class="w-full pl-10 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
      />
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6">
      <div class="animate-pulse space-y-4">
        <div class="h-10 bg-slate-700/50 rounded-lg w-full"></div>
        <div class="h-16 bg-slate-700/50 rounded-lg w-full"></div>
        <div class="h-16 bg-slate-700/50 rounded-lg w-full"></div>
        <div class="h-16 bg-slate-700/50 rounded-lg w-full"></div>
      </div>
    </div>

    <!-- Items Table -->
    <div v-else class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-700/50 border-b border-slate-600/50">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Item</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Rarity</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Price</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Sell Price</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Tradeable</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Stackable</th>
              <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/50">
            <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-slate-700/25 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-lg bg-slate-700/50 border border-slate-600/50 flex items-center justify-center overflow-hidden">
                    <img
                      v-if="item.image"
                      :src="item.image"
                      :alt="item.name"
                      class="w-full h-full object-cover"
                    />
                    <CubeIcon v-else class="w-6 h-6 text-slate-500" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-white">{{ item.name }}</p>
                    <p class="text-xs text-slate-400 truncate max-w-[200px]">{{ item.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="getRarityClass(item.rarity)" class="px-2.5 py-1 rounded-lg text-xs font-semibold capitalize">
                  {{ item.rarity }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-emerald-400 font-medium">${{ formatNumber(item.price) }}</td>
              <td class="px-6 py-4 text-sm text-slate-300">${{ formatNumber(item.sell_price || 0) }}</td>
              <td class="px-6 py-4">
                <span v-if="item.tradeable" class="inline-flex items-center gap-1 text-emerald-400">
                  <CheckCircleIcon class="w-5 h-5" />
                </span>
                <span v-else class="inline-flex items-center gap-1 text-slate-500">
                  <XCircleIcon class="w-5 h-5" />
                </span>
              </td>
              <td class="px-6 py-4">
                <span v-if="item.stackable" class="text-xs text-slate-300">
                  Max: {{ item.max_stack || 1 }}
                </span>
                <span v-else class="text-slate-500">—</span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button @click="editItem(item)" class="p-2 rounded-lg bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 transition-colors">
                    <PencilIcon class="w-4 h-4" />
                  </button>
                  <button @click="deleteItem(item)" class="p-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors">
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!filteredItems.length">
              <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                <CubeIcon class="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>No {{ activeType === 'all' ? 'items' : activeType + ' items' }} found</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-slate-700/50 flex items-center justify-between">
        <p class="text-sm text-slate-400">
          Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }}
          to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
          of {{ pagination.total }} items
        </p>
        <div class="flex items-center gap-2">
          <button
            @click="goToPage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-3 py-1.5 rounded-lg bg-slate-700/50 text-slate-300 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <button
            @click="goToPage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-3 py-1.5 rounded-lg bg-slate-700/50 text-slate-300 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
          <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-slate-800 border-b border-slate-700 p-6 flex items-center justify-between">
              <h2 class="text-xl font-bold text-white">{{ editingItem ? 'Edit' : 'Create' }} Item</h2>
              <button @click="closeModal" class="text-slate-400 hover:text-white transition-colors">
                <XMarkIcon class="w-6 h-6" />
              </button>
            </div>
            <form @submit.prevent="saveItem" class="p-6">
              <div class="form-grid">
                <div class="form-group full-width">
                  <label>Name *</label>
                  <input v-model="formData.name" type="text" required placeholder="e.g. Bulletproof Vest">
                </div>

                <div class="form-group full-width">
                  <label>Description *</label>
                  <textarea v-model="formData.description" required rows="3" placeholder="Item description..."></textarea>
                </div>

                <div class="form-group">
                  <label>Type *</label>
                  <select v-model="formData.type" required>
                    <option value="weapon">Weapon</option>
                    <option value="armor">Armor</option>
                    <option value="consumable">Consumable</option>
                    <option value="collectible">Collectible</option>
                    <option value="misc">Miscellaneous</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Rarity *</label>
                  <select v-model="formData.rarity" required>
                    <option value="common">Common</option>
                    <option value="uncommon">Uncommon</option>
                    <option value="rare">Rare</option>
                    <option value="epic">Epic</option>
                    <option value="legendary">Legendary</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Price *</label>
                  <input v-model.number="formData.price" type="number" min="0" required>
                </div>

                <div class="form-group">
                  <label>Sell Price</label>
                  <input v-model.number="formData.sell_price" type="number" min="0">
                </div>

                <div class="form-group">
                  <label>Max Stack</label>
                  <input v-model.number="formData.max_stack" type="number" min="1">
                </div>

                <div class="form-group">
                  <label>Image URL</label>
                  <input v-model="formData.image" type="text" placeholder="https://...">
                </div>

                <div class="form-group">
                  <div class="checkbox-wrapper">
                    <input v-model="formData.tradeable" type="checkbox" id="tradeable-checkbox">
                    <label for="tradeable-checkbox">Tradeable</label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox-wrapper">
                    <input v-model="formData.stackable" type="checkbox" id="stackable-checkbox">
                    <label for="stackable-checkbox">Stackable</label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox-wrapper">
                    <input v-model="formData.is_usable" type="checkbox" id="usable-checkbox">
                    <label for="usable-checkbox">Can Be Used (Consumable)</label>
                  </div>
                </div>
              </div>

              <!-- Effects Section (for usable items) -->
              <div v-if="formData.is_usable" class="mt-6 pt-6 border-t border-slate-700">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-lg font-semibold text-white">Item Effects</h3>
                  <button
                    type="button"
                    @click="addEffect"
                    class="px-3 py-1.5 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors text-sm flex items-center gap-2"
                  >
                    <PlusIcon class="w-4 h-4" />
                    Add Effect
                  </button>
                </div>

                <div class="space-y-3">
                  <div v-for="(effect, index) in formData.effects" :key="index" class="flex items-start gap-3 p-4 bg-slate-900/50 rounded-lg border border-slate-700">
                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-4 gap-3">
                      <div>
                        <label class="text-xs text-slate-400">Effect Type</label>
                        <select v-model="effect.name" class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white text-sm">
                          <option value="heal">Heal (HP)</option>
                          <option value="heal_percent">Heal % (HP)</option>
                          <option value="restore_energy">Restore Energy</option>
                          <option value="boost_strength">Strength Boost</option>
                          <option value="boost_defense">Defense Boost</option>
                          <option value="boost_speed">Speed Boost</option>
                          <option value="boost_damage">Damage Boost %</option>
                          <option value="reduce_cooldown">Cooldown Reduction %</option>
                          <option value="experience_boost">XP Boost %</option>
                          <option value="money_boost">Money Boost %</option>
                          <option value="crime_success">Crime Success %</option>
                          <option value="jail_reduction">Jail Reduction %</option>
                          <option value="revive">Revive from Hospital</option>
                        </select>
                      </div>
                      <div>
                        <label class="text-xs text-slate-400">Value</label>
                        <input v-model.number="effect.value" type="number" min="0" class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white text-sm" placeholder="e.g. 50">
                      </div>
                      <div>
                        <label class="text-xs text-slate-400">Modifier</label>
                        <select v-model="effect.modifier_type" class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white text-sm">
                          <option value="flat">Flat Value</option>
                          <option value="percent">Percentage</option>
                        </select>
                      </div>
                      <div class="flex items-end">
                        <button
                          type="button"
                          @click="removeEffect(index)"
                          class="px-3 py-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors"
                        >
                          <TrashIcon class="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </div>
                  <p v-if="!formData.effects?.length" class="text-slate-500 text-sm text-center py-4">
                    No effects added. Click "Add Effect" to add one.
                  </p>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                  <div class="form-group">
                    <label>Cooldown (seconds)</label>
                    <input v-model.number="formData.cooldown" type="number" min="0" placeholder="e.g. 300 (5 min)">
                  </div>
                  <div class="form-group">
                    <label>Duration (seconds, 0 = instant)</label>
                    <input v-model.number="formData.duration" type="number" min="0" placeholder="e.g. 3600 (1 hour)">
                  </div>
                </div>
              </div>

              <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-700">
                <button type="button" @click="closeModal" class="px-4 py-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">
                  Cancel
                </button>
                <button type="submit" :disabled="saving" class="px-4 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium hover:from-amber-600 hover:to-orange-700 transition-all disabled:opacity-50">
                  {{ saving ? 'Saving...' : (editingItem ? 'Update' : 'Create') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import {
  PlusIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  XMarkIcon,
  CubeIcon,
  CheckCircleIcon,
  XCircleIcon,
  SparklesIcon,
  ShieldCheckIcon,
  BeakerIcon,
  TrophyIcon,
  ArchiveBoxIcon
} from '@heroicons/vue/24/outline'

const toast = useToast()

// Item types with icons
const itemTypes = [
  { value: 'all', label: 'All Items', icon: CubeIcon },
  { value: 'weapon', label: 'Weapons', icon: SparklesIcon },
  { value: 'armor', label: 'Armor', icon: ShieldCheckIcon },
  { value: 'consumable', label: 'Consumables', icon: BeakerIcon },
  { value: 'collectible', label: 'Collectibles', icon: TrophyIcon },
  { value: 'misc', label: 'Miscellaneous', icon: ArchiveBoxIcon }
]

// State
const activeType = ref('all')
const items = ref([])
const allItems = ref([])
const loading = ref(false)
const searchQuery = ref('')
const showModal = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const pagination = ref(null)
const currentPage = ref(1)

const defaultItem = {
  name: '',
  type: 'misc',
  description: '',
  image: '',
  price: 0,
  sell_price: 0,
  tradeable: true,
  stackable: false,
  max_stack: 1,
  rarity: 'common',
  is_usable: false,
  effects: [],
  cooldown: 0,
  duration: 0
}

const formData = ref({ ...defaultItem })

// Effect management
const addEffect = () => {
  if (!formData.value.effects) {
    formData.value.effects = []
  }
  formData.value.effects.push({
    name: 'heal',
    value: 0,
    modifier_type: 'flat'
  })
}

const removeEffect = (index) => {
  formData.value.effects.splice(index, 1)
}

// Computed filtered items
const filteredItems = computed(() => {
  let result = items.value

  // Filter by type
  if (activeType.value !== 'all') {
    result = result.filter(item => item.type === activeType.value)
  }

  // Filter by search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(item =>
      item.name.toLowerCase().includes(query) ||
      (item.description && item.description.toLowerCase().includes(query))
    )
  }

  return result
})

// Get count for each type
const getTypeCount = (type) => {
  if (type === 'all') return allItems.value.length
  return allItems.value.filter(item => item.type === type).length
}

// Fetch items
const fetchItems = async (page = 1) => {
  loading.value = true
  try {
    const params = { page, per_page: 100 }
    const response = await api.get('/admin/items', { params })

    if (response.data.data) {
      items.value = response.data.data
      allItems.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        total: response.data.total,
        per_page: response.data.per_page || 100
      }
    } else {
      items.value = response.data
      allItems.value = response.data
    }
  } catch (err) {
    toast.error('Failed to load items')
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Debounced search
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    // Search is client-side via computed
  }, 300)
}

// Modal handlers
const showCreateModal = () => {
  editingItem.value = null
  formData.value = { ...defaultItem, type: activeType.value !== 'all' ? activeType.value : 'misc' }
  showModal.value = true
}

const editItem = (item) => {
  editingItem.value = item
  formData.value = {
    ...item,
    effects: item.effects || [],
    is_usable: item.is_usable || false,
    cooldown: item.cooldown || 0,
    duration: item.duration || 0
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingItem.value = null
  formData.value = { ...defaultItem }
}

const saveItem = async () => {
  saving.value = true
  try {
    if (editingItem.value) {
      await api.patch(`/admin/items/${editingItem.value.id}`, formData.value)
      toast.success('Item updated successfully!')
    } else {
      await api.post('/admin/items', formData.value)
      toast.success('Item created successfully!')
    }
    closeModal()
    fetchItems(currentPage.value)
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save item')
  } finally {
    saving.value = false
  }
}

const deleteItem = async (item) => {
  if (!confirm(`Are you sure you want to delete "${item.name}"? This cannot be undone.`)) return

  try {
    await api.delete(`/admin/items/${item.id}`)
    toast.success('Item deleted successfully!')
    fetchItems(currentPage.value)
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to delete item')
  }
}

const goToPage = (page) => {
  currentPage.value = page
  fetchItems(page)
}

// Helper functions
const formatNumber = (num) => {
  return num?.toLocaleString() || '0'
}

const getRarityClass = (rarity) => {
  const classes = {
    common: 'bg-slate-500/20 text-slate-300',
    uncommon: 'bg-emerald-500/20 text-emerald-400',
    rare: 'bg-blue-500/20 text-blue-400',
    epic: 'bg-purple-500/20 text-purple-400',
    legendary: 'bg-amber-500/20 text-amber-400'
  }
  return classes[rarity] || classes.common
}

// Watch for type changes
watch(activeType, () => {
  searchQuery.value = ''
})

onMounted(() => {
  fetchItems()
})
</script>

<style scoped>
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 600;
  color: #f1f5f9;
  font-size: 0.875rem;
}

.form-group input,
.form-group textarea,
.form-group select {
  padding: 0.875rem 1.125rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.625rem;
  background: rgba(15, 23, 42, 0.5);
  color: #f1f5f9;
  font-size: 0.938rem;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.form-group textarea {
  resize: vertical;
  font-family: inherit;
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0;
}

.checkbox-wrapper input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #f59e0b;
}

.checkbox-wrapper label {
  margin: 0;
  cursor: pointer;
  font-weight: 500;
  color: #cbd5e1;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>

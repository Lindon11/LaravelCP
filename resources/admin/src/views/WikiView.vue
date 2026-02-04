<template>
  <div class="space-y-6">
    <!-- Action Bar -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full lg:w-auto">
        <div class="relative w-full sm:w-80">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search wiki articles..."
            @input="debouncedSearch"
            class="w-full pl-10 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
          />
        </div>
        <select
          v-model="selectedCategory"
          @change="fetchArticles"
          class="px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all"
        >
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="showCategoryModal = true"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white rounded-xl font-medium transition-all"
        >
          <FolderPlusIcon class="w-5 h-5" />
          Categories
        </button>
        <button
          @click="showCreateModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all"
        >
          <PlusIcon class="w-5 h-5" />
          New Article
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-12 h-12 border-4 border-amber-500/30 border-t-amber-500 rounded-full animate-spin mb-4"></div>
        <p class="text-slate-400">Loading articles...</p>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="articles.length === 0" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-700/30 flex items-center justify-center mb-4">
          <BookOpenIcon class="w-8 h-8 text-slate-500" />
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">No articles found</h3>
        <p class="text-slate-400 text-center max-w-sm mb-4">{{ searchQuery || selectedCategory ? 'Try adjusting your filters' : 'Create your first wiki article' }}</p>
        <button @click="showCreateModal" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium transition-colors">
          Create Article
        </button>
      </div>
    </div>

    <!-- Articles Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6">
      <TransitionGroup name="list">
        <div
          v-for="article in articles"
          :key="article.id"
          class="group bg-slate-800/50 hover:bg-slate-800 backdrop-blur-sm rounded-2xl border border-slate-700/50 hover:border-slate-600/50 overflow-hidden transition-all"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                  <DocumentTextIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                  <span v-if="article.category" class="text-xs font-medium text-amber-400">{{ article.category.name }}</span>
                  <span v-else class="text-xs font-medium text-slate-500">Uncategorized</span>
                </div>
              </div>
              <span :class="[
                'inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium',
                article.is_published ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-600/50 text-slate-400'
              ]">
                {{ article.is_published ? 'Published' : 'Draft' }}
              </span>
            </div>

            <h3 class="text-lg font-semibold text-white group-hover:text-amber-400 transition-colors mb-2 line-clamp-1">
              {{ article.title }}
            </h3>

            <p class="text-sm text-slate-400 line-clamp-2 mb-4">{{ article.excerpt || stripHtml(article.content) }}</p>

            <div class="flex items-center justify-between pt-4 border-t border-slate-700/50">
              <div class="flex items-center gap-4 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1">
                  <EyeIcon class="w-4 h-4" />
                  {{ article.views || 0 }}
                </span>
                <span class="inline-flex items-center gap-1">
                  <CalendarIcon class="w-4 h-4" />
                  {{ formatDate(article.updated_at || article.created_at) }}
                </span>
              </div>
              <div class="flex items-center gap-1">
                <button
                  @click="editArticle(article)"
                  class="p-2 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-700/50 transition-colors"
                >
                  <PencilIcon class="w-4 h-4" />
                </button>
                <button
                  @click="deleteArticle(article)"
                  class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                >
                  <TrashIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </TransitionGroup>
    </div>

    <!-- Article Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
          <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-6 border-b border-slate-700 shrink-0">
              <h2 class="text-lg font-bold text-white">{{ editingItem ? 'Edit Article' : 'New Article' }}</h2>
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
                    placeholder="Article title"
                    class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                  />
                </div>

                <div class="space-y-2">
                  <label class="block text-sm font-medium text-slate-300">Category</label>
                  <select
                    v-model="formData.category_id"
                    class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                  >
                    <option :value="null">Select category</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                </div>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Slug</label>
                <input
                  v-model="formData.slug"
                  type="text"
                  placeholder="article-url-slug"
                  class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all font-mono text-sm"
                />
                <p class="text-xs text-slate-500">Leave empty to auto-generate from title</p>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Excerpt</label>
                <textarea
                  v-model="formData.excerpt"
                  rows="2"
                  placeholder="Brief description (optional)"
                  class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all resize-none"
                />
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Content *</label>
                <textarea
                  v-model="formData.content"
                  rows="12"
                  placeholder="Write your article content here... (Markdown supported)"
                  class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all resize-none font-mono text-sm"
                />
              </div>

              <div class="flex items-center gap-6 pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="formData.is_published" type="checkbox" class="w-5 h-5 rounded border-slate-600 text-amber-500 focus:ring-amber-500 focus:ring-offset-0 bg-slate-700" />
                  <span class="text-sm text-slate-300">Published</span>
                </label>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 p-6 border-t border-slate-700 bg-slate-800/50 shrink-0">
              <button @click="closeModal" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl font-medium transition-colors">
                Cancel
              </button>
              <button
                @click="saveArticle"
                :disabled="saving"
                class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : (editingItem ? 'Save Changes' : 'Create Article') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Category Management Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showCategoryModal = false"></div>
          <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between p-6 border-b border-slate-700">
              <h2 class="text-lg font-bold text-white">Wiki Categories</h2>
              <button @click="showCategoryModal = false" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

            <div class="p-6 space-y-4">
              <div class="flex gap-2">
                <input
                  v-model="newCategoryName"
                  type="text"
                  placeholder="New category name"
                  @keyup.enter="addCategory"
                  class="flex-1 px-4 py-2.5 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                />
                <button
                  @click="addCategory"
                  :disabled="!newCategoryName.trim()"
                  class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-medium transition-colors disabled:opacity-50"
                >
                  Add
                </button>
              </div>

              <div v-if="categories.length === 0" class="text-center py-6 text-slate-400">
                No categories yet
              </div>

              <div v-else class="space-y-2 max-h-64 overflow-y-auto">
                <div
                  v-for="cat in categories"
                  :key="cat.id"
                  class="flex items-center justify-between p-3 rounded-xl bg-slate-700/30 hover:bg-slate-700/50 transition-colors"
                >
                  <div class="flex items-center gap-3">
                    <FolderIcon class="w-5 h-5 text-amber-400" />
                    <span class="text-sm text-white">{{ cat.name }}</span>
                  </div>
                  <button
                    @click="deleteCategory(cat)"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </div>
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
  MagnifyingGlassIcon, PlusIcon, XMarkIcon, PencilIcon, TrashIcon,
  BookOpenIcon, DocumentTextIcon, EyeIcon, CalendarIcon, FolderPlusIcon, FolderIcon
} from '@heroicons/vue/24/outline'

const toast = useToast()
const articles = ref([])
const categories = ref([])
const loading = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('')
const showModal = ref(false)
const showCategoryModal = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const newCategoryName = ref('')
let searchTimeout = null

const defaultFormData = { title: '', slug: '', excerpt: '', content: '', category_id: null, is_published: false }
const formData = ref({ ...defaultFormData })

onMounted(() => { fetchArticles(); fetchCategories() })

const fetchArticles = async () => {
  loading.value = true
  try {
    const params = { search: searchQuery.value, category_id: selectedCategory.value }
    const response = await api.get('/admin/wiki-pages', { params })
    articles.value = response.data.data || response.data
  } catch (err) {
    toast.error('Failed to load articles')
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const response = await api.get('/admin/wiki-categories')
    categories.value = response.data.data || response.data
  } catch (err) {
    console.error('Failed to load categories')
  }
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchArticles, 300)
}

const showCreateModal = () => { editingItem.value = null; formData.value = { ...defaultFormData }; showModal.value = true }
const editArticle = (item) => { editingItem.value = item; formData.value = { ...item }; showModal.value = true }
const closeModal = () => { showModal.value = false; editingItem.value = null }

const saveArticle = async () => {
  if (!formData.value.title || !formData.value.content) { toast.error('Title and content are required'); return }
  saving.value = true
  try {
    if (editingItem.value) {
      await api.patch(`/admin/wiki-pages/${editingItem.value.id}`, formData.value)
      toast.success('Article updated')
    } else {
      await api.post('/admin/wiki-pages', formData.value)
      toast.success('Article created')
    }
    closeModal(); fetchArticles()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save article')
  } finally {
    saving.value = false
  }
}

const deleteArticle = async (item) => {
  if (!confirm(`Delete "${item.title}"?`)) return
  try {
    await api.delete(`/admin/wiki-pages/${item.id}`)
    toast.success('Article deleted')
    fetchArticles()
  } catch (err) {
    toast.error('Failed to delete article')
  }
}

const addCategory = async () => {
  if (!newCategoryName.value.trim()) return
  try {
    await api.post('/admin/wiki-categories', { name: newCategoryName.value.trim() })
    toast.success('Category created')
    newCategoryName.value = ''
    fetchCategories()
  } catch (err) {
    toast.error('Failed to create category')
  }
}

const deleteCategory = async (cat) => {
  if (!confirm(`Delete category "${cat.name}"?`)) return
  try {
    await api.delete(`/admin/wiki-categories/${cat.id}`)
    toast.success('Category deleted')
    fetchCategories()
  } catch (err) {
    toast.error('Failed to delete category')
  }
}

const stripHtml = (html) => html?.replace(/<[^>]*>/g, '').substring(0, 150) || ''
const formatDate = (dateStr) => dateStr ? new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : 'N/A'
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.list-enter-active, .list-leave-active { transition: all 0.3s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: scale(0.95); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>

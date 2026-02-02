<template>
  <div class="wiki-view">
    <div class="page-header">
      <div class="header-content">
        <h1>📚 Wiki Management</h1>
        <p class="subtitle">Manage game documentation and help articles</p>
      </div>
      <button class="btn-create" @click="openCreateModal">
        ➕ New Article
      </button>
    </div>

    <div class="wiki-layout">
      <!-- Categories Sidebar -->
      <aside class="categories-sidebar">
        <h3>📂 Categories</h3>
        <ul class="category-list">
          <li 
            :class="{ active: selectedCategory === null }"
            @click="selectedCategory = null"
          >
            <span class="icon">📋</span>
            All Articles
            <span class="count">{{ articles.length }}</span>
          </li>
          <li 
            v-for="cat in categories" 
            :key="cat.id"
            :class="{ active: selectedCategory === cat.id }"
            @click="selectedCategory = cat.id"
          >
            <span class="icon">{{ cat.icon || '📁' }}</span>
            {{ cat.name }}
            <span class="count">{{ getArticleCount(cat.id) }}</span>
          </li>
        </ul>

        <div class="category-actions">
          <button class="btn-add-category" @click="openCategoryModal">
            ➕ Add Category
          </button>
        </div>
      </aside>

      <!-- Articles List -->
      <main class="articles-main">
        <div class="search-bar">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="🔍 Search articles..."
          >
        </div>

        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Loading articles...</p>
        </div>

        <div v-else-if="filteredArticles.length === 0" class="empty-state">
          <span class="icon">📝</span>
          <h3>No Articles Found</h3>
          <p>{{ searchQuery ? 'Try a different search term' : 'Create your first wiki article' }}</p>
        </div>

        <div v-else class="articles-list">
          <div 
            v-for="article in filteredArticles" 
            :key="article.id" 
            class="article-card"
            @click="editArticle(article)"
          >
            <div class="article-header">
              <h4>{{ article.title }}</h4>
              <span v-if="article.published" class="status published">Published</span>
              <span v-else class="status draft">Draft</span>
            </div>
            <p class="article-excerpt">{{ truncate(article.content, 150) }}</p>
            <div class="article-meta">
              <span class="category">📁 {{ getCategoryName(article.category_id) }}</span>
              <span class="date">📅 {{ formatDate(article.updated_at) }}</span>
              <span class="views">👁️ {{ article.views || 0 }} views</span>
            </div>
            <div class="article-actions" @click.stop>
              <button class="btn-icon" @click="editArticle(article)" title="Edit">✏️</button>
              <button class="btn-icon" @click="togglePublish(article)" :title="article.published ? 'Unpublish' : 'Publish'">
                {{ article.published ? '📤' : '📥' }}
              </button>
              <button class="btn-icon danger" @click="confirmDelete(article)" title="Delete">🗑️</button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Article Editor Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content large">
        <div class="modal-header">
          <h2>{{ editingArticle?.id ? '✏️ Edit Article' : '📝 New Article' }}</h2>
          <button class="close-btn" @click="closeModal">✕</button>
        </div>
        <form @submit.prevent="saveArticle" class="modal-body">
          <div class="form-grid">
            <div class="form-group full-width">
              <label>Title *</label>
              <input v-model="editingArticle.title" type="text" required placeholder="Article title">
            </div>

            <div class="form-group">
              <label>Category *</label>
              <select v-model="editingArticle.category_id" required>
                <option value="">Select Category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Slug</label>
              <input v-model="editingArticle.slug" type="text" placeholder="auto-generated-from-title">
            </div>

            <div class="form-group full-width">
              <label>Content *</label>
              <textarea 
                v-model="editingArticle.content" 
                rows="15" 
                required 
                placeholder="Article content... (Markdown supported)"
              ></textarea>
            </div>

            <div class="form-group">
              <label>Order</label>
              <input v-model.number="editingArticle.order" type="number" min="0">
            </div>

            <div class="form-group">
              <label>Status</label>
              <div class="checkbox-wrapper">
                <input v-model="editingArticle.published" type="checkbox" id="published">
                <label for="published">Published</label>
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-secondary" @click="closeModal">Cancel</button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? '💾 Saving...' : '💾 Save Article' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Category Modal -->
    <div v-if="showCategoryModal" class="modal-overlay" @click.self="closeCategoryModal">
      <div class="modal-content small">
        <div class="modal-header">
          <h2>📂 Add Category</h2>
          <button class="close-btn" @click="closeCategoryModal">✕</button>
        </div>
        <form @submit.prevent="saveCategory" class="modal-body">
          <div class="form-group">
            <label>Name *</label>
            <input v-model="newCategory.name" type="text" required placeholder="Category name">
          </div>

          <div class="form-group">
            <label>Icon</label>
            <input v-model="newCategory.icon" type="text" placeholder="📁">
          </div>

          <div class="form-group">
            <label>Order</label>
            <input v-model.number="newCategory.order" type="number" min="0">
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-secondary" @click="closeCategoryModal">Cancel</button>
            <button type="submit" class="btn-primary">Save Category</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'

const loading = ref(true)
const saving = ref(false)
const showModal = ref(false)
const showCategoryModal = ref(false)
const selectedCategory = ref(null)
const searchQuery = ref('')

const articles = ref([])
const categories = ref([])

const editingArticle = reactive({
  id: null,
  title: '',
  slug: '',
  content: '',
  category_id: '',
  order: 0,
  published: false
})

const newCategory = reactive({
  name: '',
  icon: '📁',
  order: 0
})

const filteredArticles = computed(() => {
  let result = articles.value

  if (selectedCategory.value !== null) {
    result = result.filter(a => a.category_id === selectedCategory.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(a => 
      a.title.toLowerCase().includes(query) ||
      a.content.toLowerCase().includes(query)
    )
  }

  return result
})

const loadData = async () => {
  loading.value = true
  try {
    const [articlesRes, categoriesRes] = await Promise.all([
      api.get('/admin/content/wiki-pages'),
      api.get('/admin/content/wiki-categories')
    ])

    articles.value = articlesRes.data.data || articlesRes.data || []
    categories.value = categoriesRes.data.data || categoriesRes.data || []
  } catch (error) {
    console.error('Error loading wiki data:', error)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  Object.assign(editingArticle, {
    id: null,
    title: '',
    slug: '',
    content: '',
    category_id: selectedCategory.value || '',
    order: 0,
    published: false
  })
  showModal.value = true
}

const editArticle = (article) => {
  Object.assign(editingArticle, article)
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const saveArticle = async () => {
  saving.value = true
  try {
    if (editingArticle.id) {
      await api.put(`/admin/content/wiki-pages/${editingArticle.id}`, editingArticle)
    } else {
      await api.post('/admin/content/wiki-pages', editingArticle)
    }
    await loadData()
    closeModal()
  } catch (error) {
    console.error('Error saving article:', error)
  } finally {
    saving.value = false
  }
}

const togglePublish = async (article) => {
  try {
    await api.put(`/admin/content/wiki-pages/${article.id}`, { ...article, published: !article.published })
    await loadData()
  } catch (error) {
    console.error('Error toggling publish:', error)
  }
}

const confirmDelete = async (article) => {
  if (confirm(`Delete "${article.title}"? This cannot be undone.`)) {
    try {
      await api.delete(`/admin/content/wiki-pages/${article.id}`)
      await loadData()
    } catch (error) {
      console.error('Error deleting article:', error)
    }
  }
}

const openCategoryModal = () => {
  Object.assign(newCategory, { name: '', icon: '📁', order: 0 })
  showCategoryModal.value = true
}

const closeCategoryModal = () => {
  showCategoryModal.value = false
}

const saveCategory = async () => {
  try {
    await api.post('/admin/content/wiki-categories', newCategory)
    await loadData()
    closeCategoryModal()
  } catch (error) {
    console.error('Error saving category:', error)
  }
}

const getArticleCount = (categoryId) => {
  return articles.value.filter(a => a.category_id === categoryId).length
}

const getCategoryName = (categoryId) => {
  const cat = categories.value.find(c => c.id === categoryId)
  return cat?.name || 'Uncategorized'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const truncate = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.wiki-view {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.header-content h1 {
  font-size: 2rem;
  color: #f1f5f9;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #94a3b8;
}

.btn-create {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  border-radius: 0.625rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-create:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.wiki-layout {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 2rem;
}

.categories-sidebar {
  background: rgba(30, 41, 59, 0.5);
  border-radius: 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.1);
  padding: 1.5rem;
  height: fit-content;
  position: sticky;
  top: 2rem;
}

.categories-sidebar h3 {
  font-size: 1rem;
  color: #f1f5f9;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.category-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.category-list li {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  cursor: pointer;
  color: #94a3b8;
  transition: all 0.2s ease;
}

.category-list li:hover {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.category-list li.active {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
}

.category-list .icon {
  font-size: 1rem;
}

.category-list .count {
  margin-left: auto;
  font-size: 0.75rem;
  background: rgba(148, 163, 184, 0.2);
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

.category-actions {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.btn-add-category {
  width: 100%;
  padding: 0.75rem;
  background: transparent;
  border: 1px dashed rgba(148, 163, 184, 0.3);
  border-radius: 0.5rem;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-add-category:hover {
  border-color: #3b82f6;
  color: #3b82f6;
}

.articles-main {
  min-height: 400px;
}

.search-bar {
  margin-bottom: 1.5rem;
}

.search-bar input {
  width: 100%;
  padding: 1rem 1.25rem;
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.625rem;
  color: #f1f5f9;
  font-size: 1rem;
}

.search-bar input:focus {
  outline: none;
  border-color: #3b82f6;
}

.loading-state,
.empty-state {
  padding: 4rem;
  text-align: center;
  color: #94a3b8;
  background: rgba(30, 41, 59, 0.5);
  border-radius: 0.75rem;
}

.empty-state .icon {
  font-size: 4rem;
  display: block;
  margin-bottom: 1rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(59, 130, 246, 0.3);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.articles-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.article-card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.75rem;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.article-card:hover {
  border-color: rgba(59, 130, 246, 0.3);
  transform: translateY(-2px);
}

.article-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.article-header h4 {
  flex: 1;
  font-size: 1.125rem;
  color: #f1f5f9;
  margin: 0;
}

.status {
  padding: 0.25rem 0.75rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.status.published {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
}

.status.draft {
  background: rgba(245, 158, 11, 0.2);
  color: #f59e0b;
}

.article-excerpt {
  color: #94a3b8;
  font-size: 0.875rem;
  margin-bottom: 1rem;
  line-height: 1.5;
}

.article-meta {
  display: flex;
  gap: 1.5rem;
  font-size: 0.75rem;
  color: #64748b;
  margin-bottom: 1rem;
}

.article-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.btn-icon {
  padding: 0.5rem;
  background: rgba(148, 163, 184, 0.1);
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-icon:hover {
  background: rgba(59, 130, 246, 0.2);
}

.btn-icon.danger:hover {
  background: rgba(239, 68, 68, 0.2);
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
  padding: 2rem;
}

.modal-content {
  background: #1e293b;
  border-radius: 1rem;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-content.large {
  max-width: 900px;
}

.modal-content.small {
  max-width: 450px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.modal-header h2 {
  color: #f1f5f9;
  font-size: 1.25rem;
}

.close-btn {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 1.5rem;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
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
.form-group select,
.form-group textarea {
  padding: 0.875rem 1rem;
  background: rgba(15, 23, 42, 0.5);
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.5rem;
  color: #f1f5f9;
  font-size: 0.875rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3b82f6;
}

.form-group textarea {
  resize: vertical;
  min-height: 200px;
  font-family: 'Monaco', 'Consolas', monospace;
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.checkbox-wrapper input[type="checkbox"] {
  width: 20px;
  height: 20px;
  accent-color: #3b82f6;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.btn-primary,
.btn-secondary {
  padding: 0.875rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  border: none;
}

.btn-primary:disabled {
  opacity: 0.7;
}

.btn-secondary {
  background: transparent;
  border: 1px solid rgba(148, 163, 184, 0.3);
  color: #94a3b8;
}

@media (max-width: 1024px) {
  .wiki-layout {
    grid-template-columns: 1fr;
  }

  .categories-sidebar {
    position: static;
  }
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 1rem;
  }

  .btn-create {
    width: 100%;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>

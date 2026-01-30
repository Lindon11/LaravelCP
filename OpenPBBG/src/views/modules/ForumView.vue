<template>
  <div class="forum-container">
    <div class="page-header">
      <h1>💬 Forum</h1>
      <router-link to="/dashboard" class="back-link">← Back to Dashboard</router-link>
    </div>

    <div class="content-wrapper">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading forum...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button @click="fetchCategories" class="retry-btn">Retry</button>
      </div>

      <!-- Forum Content -->
      <div v-else>
        <div class="forum-card">
          <div class="card-header">
            <h2>Forum Categories</h2>
            <p class="subtitle">Join the conversation with the community</p>
          </div>

          <div class="categories-list">
            <div
              v-for="category in categories"
              :key="category.id"
              class="category-item"
              @click="viewCategory(category.id)"
            >
              <div class="category-icon">
                {{ getCategoryIcon(category.name) }}
              </div>
              <div class="category-content">
                <h3 class="category-name">{{ category.name }}</h3>
                <p class="category-description">{{ category.description }}</p>
                <div class="category-meta">
                  <span class="meta-item">
                    <span class="meta-icon">📝</span>
                    {{ formatNumber(category.topic_count) }} topics
                  </span>
                  <span class="meta-item">
                    <span class="meta-icon">💬</span>
                    {{ formatNumber(category.post_count) }} posts
                  </span>
                </div>
              </div>
              <div class="category-stats">
                <div class="stat-number">{{ formatNumber(category.topic_count) }}</div>
                <div class="stat-label">Topics</div>
              </div>
              <div class="arrow-icon">→</div>
            </div>

            <div v-if="!categories || categories.length === 0" class="empty-state">
              <div class="empty-icon">💭</div>
              <p>No forum categories available yet.</p>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
              <div class="stat-value">{{ formatNumber(totalTopics) }}</div>
              <div class="stat-label">Total Topics</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-info">
              <div class="stat-value">{{ formatNumber(totalPosts) }}</div>
              <div class="stat-label">Total Posts</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
              <div class="stat-value">{{ categories.length }}</div>
              <div class="stat-label">Categories</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const router = useRouter();
const loading = ref(true);
const error = ref(null);
const categories = ref([]);

const totalTopics = computed(() => 
  categories.value.reduce((sum, cat) => sum + (cat.topic_count || 0), 0)
);

const totalPosts = computed(() => 
  categories.value.reduce((sum, cat) => sum + (cat.post_count || 0), 0)
);

const fetchCategories = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await api.get('/modules/forum');
    categories.value = response.data.categories || response.data || [];
  } catch (err) {
    console.error('Error fetching forum categories:', err);
    error.value = err.response?.data?.message || 'Failed to load forum. Please try again.';
  } finally {
    loading.value = false;
  }
};

const viewCategory = (categoryId) => {
  // Navigate to category view (to be implemented)
  console.log('View category:', categoryId);
  // router.push(`/modules/forum/category/${categoryId}`);
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num || 0);
};

const getCategoryIcon = (name) => {
  const icons = {
    'General Discussion': '💭',
    'Game Updates': '📢',
    'Help & Support': '🆘',
    'Suggestions': '💡',
    'Off Topic': '🎲',
    'Bug Reports': '🐛',
    'Gangs': '🎭',
    'Trading': '💰',
  };
  
  for (const [key, icon] of Object.entries(icons)) {
    if (name.toLowerCase().includes(key.toLowerCase())) {
      return icon;
    }
  }
  
  return '📌';
};

onMounted(() => {
  fetchCategories();
});
</script>

<style scoped>
.forum-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 50%, #93c5fd 100%);
  padding: 2rem 1rem;
}

.page-header {
  max-width: 1200px;
  margin: 0 auto 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1e3a8a;
  margin: 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.back-link {
  color: #1d4ed8;
  text-decoration: none;
  font-weight: 600;
  font-size: 1rem;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  background: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.back-link:hover {
  background: #eff6ff;
  transform: translateX(-4px);
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
}

.loading-state,
.error-state {
  background: white;
  border-radius: 1rem;
  padding: 3rem;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #dbeafe;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state p {
  color: #991b1b;
  font-size: 1.1rem;
  margin-bottom: 1rem;
}

.retry-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  background: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.forum-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 2rem;
}

.card-header {
  padding: 2rem;
  background: linear-gradient(135deg, #eff6ff, #dbeafe);
  border-bottom: 2px solid #bfdbfe;
}

.card-header h2 {
  font-size: 2rem;
  font-weight: 700;
  color: #1e3a8a;
  margin: 0 0 0.5rem;
}

.subtitle {
  color: #475569;
  font-size: 1rem;
  margin: 0;
}

.categories-list {
  padding: 1rem;
}

.category-item {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1.5rem;
  margin-bottom: 1rem;
  border: 2px solid #e0f2fe;
  border-radius: 0.75rem;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.category-item:hover {
  border-color: #3b82f6;
  background: #f0f9ff;
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.category-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.category-content {
  flex: 1;
}

.category-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e3a8a;
  margin: 0 0 0.5rem;
}

.category-description {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0 0 0.75rem;
  line-height: 1.5;
}

.category-meta {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #475569;
  font-size: 0.875rem;
  font-weight: 500;
}

.meta-icon {
  font-size: 1rem;
}

.category-stats {
  text-align: center;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  border-radius: 0.5rem;
  flex-shrink: 0;
}

.stat-number {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e40af;
}

.stat-label {
  font-size: 0.75rem;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.arrow-icon {
  font-size: 1.5rem;
  color: #3b82f6;
  flex-shrink: 0;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state p {
  color: #94a3b8;
  font-size: 1.1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.stat-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.stat-card .stat-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.stat-info {
  flex: 1;
}

.stat-card .stat-value {
  font-size: 1.875rem;
  font-weight: 700;
  color: #1e3a8a;
  margin-bottom: 0.25rem;
}

.stat-card .stat-label {
  color: #64748b;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

@media (max-width: 768px) {
  .page-header h1 {
    font-size: 2rem;
  }

  .category-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .category-icon {
    font-size: 2rem;
  }

  .category-stats {
    align-self: flex-start;
  }

  .arrow-icon {
    display: none;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>

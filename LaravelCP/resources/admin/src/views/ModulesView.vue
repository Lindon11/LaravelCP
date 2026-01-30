<template>
  <div class="modules-page">
    <div v-if="!selectedModule" class="modules-list">
      <div class="modules-header">
        <h1>Game Modules</h1>
        <button class="btn-primary" @click="scanModules">
          <span>🔄</span>
          Refresh
        </button>
      </div>

      <div v-if="modules.length === 0" class="no-modules">
        <p>No modules found. Add module.json files to your modules/ directory.</p>
      </div>

      <div class="modules-grid">
        <div v-for="module in modules" :key="module.id" class="module-card" @click="selectModule(module)">
          <div class="module-icon">{{ module.icon }}</div>
          <div class="module-info">
            <h3>{{ module.name }}</h3>
            <p>{{ module.description }}</p>
          </div>
          <div class="module-meta">
            <span class="version">v{{ module.version }}</span>
            <span class="arrow">→</span>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="module-detail">
      <div class="detail-header">
        <button class="btn-back" @click="selectedModule = null">← Back to Modules</button>
        <h1>{{ selectedModule?.icon || '' }} {{ selectedModule?.name || '' }}</h1>
      </div>

      <div class="tabs">
        <button :class="{ active: activeTab === 'view' }" @click="activeTab = 'view'">
          View {{ selectedModule?.name || '' }}
        </button>
        <button :class="{ active: activeTab === 'new' }" @click="activeTab = 'new'">
          New {{ selectedModule?.name?.slice(0, -1) || 'Item' }}
        </button>
      </div>

      <div v-if="activeTab === 'view'" class="table-container">
        <div class="table-controls">
          <button class="btn-export">CSV</button>
          <div class="control-group">
            <label>Show</label>
            <select v-model="entriesPerPage">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <span>entries</span>
          </div>
          <div class="search-box">
            <label>Search:</label>
            <input type="text" v-model="searchQuery" placeholder="">
          </div>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Cooldown</th>
              <th>Reward</th>
              <th>Level</th>
              <th>Energy</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in moduleItems" :key="item.id">
              <td>{{ item.name }}</td>
              <td>{{ item.cooldown }}s</td>
              <td>{{ item.reward }}</td>
              <td>{{ item.level }}</td>
              <td>{{ item.energy }}</td>
              <td class="actions">
                <a href="#" @click.prevent="editItem(item)">[Edit]</a>
                <a href="#" @click.prevent="deleteItem(item)">[Delete]</a>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="table-footer">
          <div class="showing-entries">Showing {{ startEntry }} to {{ endEntry }} of {{ totalEntries }} entries</div>
          <div class="pagination">
            <button :disabled="currentPage === 1" @click="currentPage--">Previous</button>
            <button class="active">{{ currentPage }}</button>
            <button :disabled="currentPage === totalPages" @click="currentPage++">Next</button>
          </div>
        </div>
      </div>

      <div v-else class="form-container">
        <h2>Add New {{ selectedModule?.name?.slice(0, -1) || 'Item' }}</h2>
        <form @submit.prevent="saveItem" class="item-form">
          <div class="form-row">
            <div class="form-group">
              <label>Name *</label>
              <input v-model="formData.name" type="text" required>
            </div>
            <div class="form-group">
              <label>Cooldown (seconds) *</label>
              <input v-model="formData.cooldown" type="number" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Min Reward *</label>
              <input v-model="formData.minReward" type="number" required>
            </div>
            <div class="form-group">
              <label>Max Reward *</label>
              <input v-model="formData.maxReward" type="number" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Level Required *</label>
              <input v-model="formData.level" type="number" required>
            </div>
            <div class="form-group">
              <label>Energy Cost *</label>
              <input v-model="formData.energy" type="number" required>
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="formData.description" rows="3"></textarea>
          </div>
          <div class="form-actions">
            <button type="button" class="btn-cancel" @click="activeTab = 'view'">Cancel</button>
            <button type="submit" class="btn-submit">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const modules = ref([])
const selectedModule = ref(null)
const activeTab = ref('view')
const entriesPerPage = ref(25)
const searchQuery = ref('')
const currentPage = ref(1)

const formData = ref({
  name: '',
  cooldown: 60,
  minReward: 1,
  maxReward: 5,
  level: 1,
  energy: 10,
  description: ''
})

const moduleItems = ref([])

const moduleDummyData = {
  'crimes': [
    { id: 1, name: 'Mug an old lady', cooldown: 20, reward: '$1 - $5', level: 1, energy: 1 },
    { id: 2, name: 'Rob a cab driver', cooldown: 45, reward: '$10 - $18', level: 1, energy: 1 }
  ],
  'gym': [
    { id: 1, name: 'Lift Weights', cooldown: 300, reward: '+2 Strength', level: 1, energy: 5 },
    { id: 2, name: 'Run on Treadmill', cooldown: 300, reward: '+2 Speed', level: 1, energy: 5 }
  ],
  'hospital': [
    { id: 1, name: 'Minor Treatment', cooldown: 0, reward: '+50 HP', level: 1, energy: 0 },
    { id: 2, name: 'Full Recovery', cooldown: 0, reward: '+100 HP', level: 1, energy: 0 }
  ],
  'bank': [
    { id: 1, name: 'Deposit Money', cooldown: 0, reward: 'N/A', level: 1, energy: 0 },
    { id: 2, name: 'Withdraw Money', cooldown: 0, reward: 'N/A', level: 1, energy: 0 }
  ],
  'travel': [
    { id: 1, name: 'Travel to New York', cooldown: 120, reward: 'N/A', level: 1, energy: 0 },
    { id: 2, name: 'Travel to Los Angeles', cooldown: 120, reward: 'N/A', level: 1, energy: 0 }
  ]
}

const totalEntries = ref(2)
const startEntry = ref(1)
const endEntry = ref(2)
const totalPages = ref(1)

const loadModules = async () => {
  try {
    console.log('Loading modules...')
    const response = await api.get('/admin/modules')
    console.log('Modules response:', response.data)
    if (response.data && response.data.modules) {
      modules.value = response.data.modules
      console.log('Modules loaded:', modules.value.length)
    } else {
      console.warn('No modules found in response')
    }
  } catch (error) {
    console.error('Failed to load modules:', error)
    console.error('Error details:', error.response)
  }
}

const scanModules = async () => {
  await loadModules()
  alert('Modules reloaded!')
}

const selectModule = (module) => {
  selectedModule.value = module
  activeTab.value = 'view'
  
  // Load dummy data for this module
  const moduleId = module.id
  if (moduleDummyData[moduleId]) {
    moduleItems.value = moduleDummyData[moduleId]
    totalEntries.value = moduleDummyData[moduleId].length
    endEntry.value = moduleDummyData[moduleId].length
  } else {
    // Generic placeholder for modules without specific dummy data
    moduleItems.value = [
      { id: 1, name: `${module.name} Item 1`, cooldown: 60, reward: 'TBD', level: 1, energy: 5 },
      { id: 2, name: `${module.name} Item 2`, cooldown: 120, reward: 'TBD', level: 1, energy: 10 }
    ]
    totalEntries.value = 2
    endEntry.value = 2
  }
}

const editItem = (item) => {
  formData.value = { ...item }
  activeTab.value = 'new'
}

const deleteItem = (item) => {
  if (confirm(`Delete ${item.name}?`)) {
    const index = moduleItems.value.findIndex(i => i.id === item.id)
    if (index !== -1) {
      moduleItems.value.splice(index, 1)
      totalEntries.value--
    }
  }
}

const saveItem = () => {
  alert('Item saved! (Connected to API in production)')
  activeTab.value = 'view'
}

onMounted(() => {
  loadModules()
})
</script>

<style scoped>
.modules-page {
  width: 100%;
}

.modules-list {
  width: 100%;
}

.modules-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.modules-header h1 {
  margin: 0;
  color: #ffffff;
  font-size: 1.875rem;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: #ffffff;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
}

.no-modules {
  text-align: center;
  padding: 3rem;
  color: #94a3b8;
}

.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.module-card {
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.75rem;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.module-card:hover {
  background: rgba(30, 41, 59, 1);
  border-color: #3b82f6;
  transform: translateX(4px);
}

.module-icon {
  font-size: 2rem;
  line-height: 1;
}

.module-info {
  flex: 1;
}

.module-info h3 {
  margin: 0 0 0.25rem 0;
  color: #ffffff;
  font-size: 1.125rem;
}

.module-info p {
  margin: 0;
  color: #94a3b8;
  font-size: 0.813rem;
}

.module-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.25rem;
}

.version {
  color: #10b981;
  font-size: 0.75rem;
  font-weight: 600;
}

.arrow {
  color: #3b82f6;
  font-size: 1.25rem;
}

.module-detail {
  width: 100%;
}

.detail-header {
  margin-bottom: 2rem;
}

.btn-back {
  background: rgba(148, 163, 184, 0.1);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #94a3b8;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 0.875rem;
  margin-bottom: 1rem;
  transition: all 0.2s;
}

.btn-back:hover {
  background: rgba(148, 163, 184, 0.2);
  color: #ffffff;
}

.detail-header h1 {
  margin: 0;
  color: #ffffff;
  font-size: 1.875rem;
}

.tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.tabs button {
  background: none;
  border: none;
  color: #94a3b8;
  padding: 0.75rem 1.5rem;
  cursor: pointer;
  font-size: 0.938rem;
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

.table-container {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.75rem;
  padding: 1.5rem;
}

.table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  gap: 1rem;
}

.btn-export {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  cursor: pointer;
  font-weight: 600;
}

.control-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #94a3b8;
  font-size: 0.875rem;
}

.control-group select {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #ffffff;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #94a3b8;
  font-size: 0.875rem;
}

.search-box input {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #ffffff;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
  width: 200px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
}

.data-table thead {
  border-bottom: 2px solid rgba(148, 163, 184, 0.2);
}

.data-table th {
  text-align: left;
  padding: 0.75rem;
  color: #94a3b8;
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
}

.data-table td {
  padding: 0.75rem;
  color: #ffffff;
  border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.data-table tbody tr:hover {
  background: rgba(59, 130, 246, 0.05);
}

.actions a {
  color: #3b82f6;
  text-decoration: none;
  margin-right: 0.75rem;
}

.actions a:hover {
  text-decoration: underline;
}

.table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #94a3b8;
  font-size: 0.875rem;
}

.pagination {
  display: flex;
  gap: 0.5rem;
}

.pagination button {
  background: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.2);
  color: #3b82f6;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination button.active {
  background: #3b82f6;
  color: white;
}

.form-container {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.75rem;
  padding: 2rem;
  max-width: 800px;
}

.form-container h2 {
  margin: 0 0 1.5rem 0;
  color: #ffffff;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  color: #94a3b8;
  font-size: 0.875rem;
  font-weight: 600;
}

.form-group input,
.form-group textarea,
.form-group select {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #ffffff;
  padding: 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.938rem;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-cancel,
.btn-submit {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
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
</style>

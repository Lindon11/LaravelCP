<template>
  <div class="combat-areas-container">
    <!-- Location Selector -->
    <div class="location-selector">
      <label>Select Combat Location:</label>
      <select v-model="selectedLocationId" @change="fetchAreas">
        <option :value="null">-- Select Location --</option>
        <option v-for="location in locations" :key="location.id" :value="location.id">
          {{ location.icon }} {{ location.name }}
        </option>
      </select>
    </div>

    <ResourceManager
      v-if="selectedLocationId"
      resourceName="Combat Area"
      :endpoint="`/admin/combat-areas?location_id=${selectedLocationId}`"
      :columns="columns"
      :defaultItem="defaultItem"
      :key="selectedLocationId"
    >
      <template #form="{ item }">
        <div class="form-grid">
          <input type="hidden" v-model="item.combat_location_id" :value="selectedLocationId">

          <div class="form-group full-width">
            <label>Name *</label>
            <input v-model="item.name" type="text" required placeholder="e.g. Main Hall">
          </div>

          <div class="form-group full-width">
            <label>Description *</label>
            <textarea v-model="item.description" required rows="3" placeholder="Description of the area..."></textarea>
          </div>

          <div class="form-group">
            <label>Difficulty Level (1-5) *</label>
            <input v-model.number="item.difficulty_level" type="number" min="1" max="5" required>
            <small>💀 = 1, 💀💀 = 2, 💀💀💀 = 3, etc.</small>
          </div>

          <div class="form-group">
            <label>Display Order</label>
            <input v-model.number="item.order" type="number" min="0">
          </div>

          <div class="form-group">
            <label>Required Level</label>
            <input v-model.number="item.required_level" type="number" min="1">
          </div>

          <div class="form-group">
            <label>Required Respect</label>
            <input v-model.number="item.required_respect" type="number" min="0">
          </div>

          <div class="form-group full-width">
            <label>Active</label>
            <div class="checkbox-wrapper">
              <input v-model="item.is_active" type="checkbox" id="area-active-checkbox">
              <label for="area-active-checkbox">Enable this area</label>
            </div>
          </div>
        </div>
      </template>
    </ResourceManager>

    <div v-else class="no-selection">
      <p>👆 Please select a combat location to manage its areas</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ResourceManager from '@/components/ResourceManager.vue'
import api from '@/services/api'

const selectedLocationId = ref(null)
const locations = ref([])

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  {
    key: 'difficulty',
    label: 'Difficulty',
    format: (val) => '💀'.repeat(val)
  },
  { key: 'min_level', label: 'Level' },
  {
    key: 'active',
    label: 'Status',
    format: (val) => val ? '🟢' : '🔴'
  }
]

const defaultItem = {
  location_id: null,
  name: '',
  description: '',
  difficulty: 1,
  min_level: 1,
  active: true
}

onMounted(async () => {
  try {
    const response = await api.get('/admin/combat-locations')
    locations.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to load locations:', error)
  }
})

function fetchAreas() {
  if (defaultItem.location_id !== selectedLocationId.value) {
    defaultItem.location_id = selectedLocationId.value
  }
}
</script>

<style scoped>
.combat-areas-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.location-selector {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1.5rem;
  background: rgba(15, 23, 42, 0.5);
  border-radius: 0.75rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
}

.location-selector label {
  font-weight: 600;
  color: #f1f5f9;
  font-size: 0.875rem;
}

.location-selector select {
  padding: 0.875rem 1.125rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.625rem;
  background: rgba(15, 23, 42, 0.8);
  color: #f1f5f9;
  font-size: 0.938rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.location-selector select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.no-selection {
  padding: 3rem;
  text-align: center;
  background: rgba(15, 23, 42, 0.3);
  border-radius: 0.75rem;
  border: 2px dashed rgba(148, 163, 184, 0.2);
}

.no-selection p {
  color: #94a3b8;
  font-size: 1.125rem;
  margin: 0;
}

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
  letter-spacing: 0.01em;
}

.form-group small {
  color: #94a3b8;
  font-size: 0.75rem;
  margin-top: -0.25rem;
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
  backdrop-filter: blur(8px);
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  background: rgba(15, 23, 42, 0.8);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(15, 23, 42, 0.3);
  border-radius: 0.625rem;
  border: 2px solid rgba(148, 163, 184, 0.1);
}

.checkbox-wrapper input[type="checkbox"] {
  width: 1.25rem;
  height: 1.25rem;
  cursor: pointer;
  accent-color: #3b82f6;
}

.checkbox-wrapper label {
  cursor: pointer;
  font-weight: 500;
  margin: 0;
}
</style>

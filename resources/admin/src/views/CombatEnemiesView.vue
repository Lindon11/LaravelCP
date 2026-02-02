<template>
  <div class="combat-enemies-container">
    <!-- Area Selector -->
    <div class="area-selector">
      <div class="selector-row">
        <div class="selector-group">
          <label>Combat Location:</label>
          <select v-model="selectedLocationId" @change="loadAreas">
            <option :value="null">-- Select Location --</option>
            <option v-for="location in locations" :key="location.id" :value="location.id">
              {{ location.icon }} {{ location.name }}
            </option>
          </select>
        </div>

        <div class="selector-group" v-if="selectedLocationId">
          <label>Combat Area:</label>
          <select v-model="selectedAreaId">
            <option :value="null">-- Select Area --</option>
            <option v-for="area in areas" :key="area.id" :value="area.id">
              {{ area.name }} ({{ '💀'.repeat(area.difficulty_level) }})
            </option>
          </select>
        </div>
      </div>
    </div>

    <ResourceManager
      v-if="selectedAreaId"
      resourceName="Combat Enemy"
      :endpoint="`/admin/combat-enemies?area_id=${selectedAreaId}`"
      :columns="columns"
      :defaultItem="defaultItem"
      :key="selectedAreaId"
    >
      <template #form="{ item }">
        <div class="form-grid">
          <input type="hidden" v-model="item.area_id" :value="selectedAreaId">

          <div class="form-group">
            <label>Name *</label>
            <input v-model="item.name" type="text" required placeholder="e.g. Street Thug">
          </div>

          <div class="form-group">
            <label>Icon (Emoji)</label>
            <input v-model="item.icon" type="text" placeholder="👤" maxlength="10">
          </div>

          <div class="form-group full-width">
            <label>Description</label>
            <textarea v-model="item.description" rows="2" placeholder="Description of the enemy..."></textarea>
          </div>

          <!-- Stats -->
          <div class="form-group">
            <label>Level *</label>
            <input v-model.number="item.level" type="number" min="1" required>
          </div>

          <div class="form-group">
            <label>Health *</label>
            <input v-model.number="item.health" type="number" min="1" required>
          </div>

          <div class="form-group">
            <label>Max Health *</label>
            <input v-model.number="item.max_health" type="number" min="1" required>
          </div>

          <div class="form-group">
            <label>Strength *</label>
            <input v-model.number="item.strength" type="number" min="0" required>
          </div>

          <div class="form-group">
            <label>Defense *</label>
            <input v-model.number="item.defense" type="number" min="0" required>
          </div>

          <div class="form-group">
            <label>Speed *</label>
            <input v-model.number="item.speed" type="number" min="0" required>
          </div>

          <div class="form-group">
            <label>Agility *</label>
            <input v-model.number="item.agility" type="number" min="0" required>
          </div>

          <div class="form-group">
            <label>Weakness</label>
            <input v-model="item.weakness" type="text" placeholder="e.g., Fire, Piercing">
          </div>

          <div class="form-group">
            <label>Difficulty (1-5)</label>
            <input v-model.number="item.difficulty" type="number" min="1" max="5">
          </div>

          <!-- Rewards -->
          <div class="form-group">
            <label>Experience Reward *</label>
            <input v-model.number="item.experience_reward" type="number" min="0" required>
          </div>

          <div class="form-group">
            <label>Min Cash Reward *</label>
            <input v-model.number="item.cash_reward_min" type="number" min="0" required>
          </div>

          <div class="form-group">
            <label>Max Cash Reward *</label>
            <input v-model.number="item.cash_reward_max" type="number" min="0" required>
          </div>

          <!-- Spawn Settings -->
          <div class="form-group">
            <label>Spawn Rate (0.01-1.00) *</label>
            <input v-model.number="item.spawn_rate" type="number" step="0.01" min="0.01" max="1.00" required>
            <small>Higher = more common</small>
          </div>

          <div class="form-group full-width">
            <label>Active</label>
            <div class="checkbox-wrapper">
              <input v-model="item.active" type="checkbox" id="enemy-active-checkbox">
              <label for="enemy-active-checkbox">Enable this enemy</label>
            </div>
          </div>
        </div>
      </template>
    </ResourceManager>

    <div v-else class="no-selection">
      <p>👆 Please select a combat location and area to manage enemies</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import ResourceManager from '@/components/ResourceManager.vue'
import api from '@/services/api'

const selectedLocationId = ref(null)
const selectedAreaId = ref(null)
const locations = ref([])
const areas = ref([])

const columns = [
  { key: 'icon', label: '👤' },
  { key: 'name', label: 'Name', sortable: true },
  { key: 'level', label: 'Level' },
  { key: 'health', label: 'HP' },
  {
    key: 'strength',
    label: 'Strength'
  },
  { key: 'experience_reward', label: 'XP' },
  {
    key: 'cash_reward_min',
    label: 'Cash',
    format: (val, row) => `$${val}-${row.cash_reward_max}`
  },
  { key: 'spawn_rate', label: 'Spawn %', format: (val) => `${(val * 100).toFixed(0)}%` },
  {
    key: 'active',
    label: 'Status',
    format: (val) => val ? '🟢' : '🔴'
  }
]

const defaultItem = {
  area_id: null,
  name: '',
  icon: '👤',
  description: '',
  level: 1,
  health: 100,
  max_health: 100,
  strength: 10,
  defense: 5,
  speed: 10,
  agility: 10,
  weakness: '',
  difficulty: 1,
  experience_reward: 10,
  cash_reward_min: 50,
  cash_reward_max: 100,
  spawn_rate: 1.00,
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

async function loadAreas() {
  selectedAreaId.value = null
  areas.value = []

  if (!selectedLocationId.value) return

  try {
    const response = await api.get(`/admin/combat-areas?location_id=${selectedLocationId.value}`)
    areas.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to load areas:', error)
  }
}

watch(selectedAreaId, (newVal) => {
  if (newVal) {
    defaultItem.area_id = newVal
  }
})
</script>

<style scoped>
.combat-enemies-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.area-selector {
  padding: 1.5rem;
  background: rgba(15, 23, 42, 0.5);
  border-radius: 0.75rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
}

.selector-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.selector-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.selector-group label {
  font-weight: 600;
  color: #f1f5f9;
  font-size: 0.875rem;
}

.selector-group select {
  padding: 0.875rem 1.125rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.625rem;
  background: rgba(15, 23, 42, 0.8);
  color: #f1f5f9;
  font-size: 0.938rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.selector-group select:focus {
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
.form-group textarea {
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
.form-group textarea:focus {
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

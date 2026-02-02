<template>
  <ResourceManager
    resourceName="Crime"
    endpoint="/admin/crimes"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. Rob a Bank">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="3" placeholder="Description of the crime..."></textarea>
        </div>

        <div class="form-group">
          <label>Success Rate (%) *</label>
          <input v-model.number="item.success_rate" type="number" min="1" max="100" required>
        </div>

        <div class="form-group">
          <label>Difficulty *</label>
          <select v-model="item.difficulty" required>
            <option value="easy">Easy</option>
            <option value="medium">Medium</option>
            <option value="hard">Hard</option>
          </select>
        </div>

        <div class="form-group">
          <label>Min Cash Reward *</label>
          <input v-model.number="item.min_cash" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Max Cash Reward *</label>
          <input v-model.number="item.max_cash" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Experience Reward *</label>
          <input v-model.number="item.experience_reward" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Respect Reward *</label>
          <input v-model.number="item.respect_reward" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Cooldown (seconds) *</label>
          <input v-model.number="item.cooldown_seconds" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Energy Cost *</label>
          <input v-model.number="item.energy_cost" type="number" min="1" required>
        </div>

        <div class="form-group">
          <label>Required Level *</label>
          <input v-model.number="item.required_level" type="number" min="1" required>
        </div>

        <div class="form-group">
          <label>Active</label>
          <div class="checkbox-wrapper">
            <input v-model="item.active" type="checkbox" id="active-checkbox">
            <label for="active-checkbox">Enable this crime</label>
          </div>
        </div>
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'cooldown_seconds', label: 'Cooldown', format: (val) => `${val}s` },
  { key: 'min_cash', label: 'Reward', format: (val, row) => `$${val} - $${row.max_cash}` },
  { key: 'required_level', label: 'Level' },
  { key: 'energy_cost', label: 'Energy' }
]

const defaultItem = {
  name: '',
  description: '',
  success_rate: 50,
  min_cash: 0,
  max_cash: 0,
  experience_reward: 0,
  respect_reward: 0,
  cooldown_seconds: 60,
  energy_cost: 10,
  required_level: 1,
  difficulty: 'easy',
  active: true
}
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
  letter-spacing: 0.01em;
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

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #64748b;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
  font-family: inherit;
}

.form-group select {
  cursor: pointer;
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
  accent-color: #3b82f6;
}

.checkbox-wrapper label {
  margin: 0;
  cursor: pointer;
  font-weight: 500;
  color: #cbd5e1;
  user-select: none;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>

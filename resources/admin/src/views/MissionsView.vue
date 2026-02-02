<template>
  <ResourceManager
    resourceName="Mission"
    endpoint="/admin/missions"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. First Blood">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="3" placeholder="What the player needs to do..."></textarea>
        </div>

        <div class="form-group">
          <label>Type *</label>
          <select v-model="item.type" required>
            <option value="one_time">One Time</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="repeatable">Repeatable</option>
          </select>
        </div>

        <div class="form-group">
          <label>Objective Type *</label>
          <select v-model="item.objective_type" required>
            <option value="crimes_committed">Crimes Committed</option>
            <option value="players_attacked">Players Attacked</option>
            <option value="cash_earned">Cash Earned</option>
            <option value="experience_earned">XP Earned</option>
            <option value="gym_trains">Gym Training</option>
            <option value="drugs_sold">Drugs Sold</option>
            <option value="items_purchased">Items Purchased</option>
            <option value="travel">Travel</option>
            <option value="property_purchased">Property Purchased</option>
            <option value="gang_joined">Join Gang</option>
            <option value="races_won">Races Won</option>
          </select>
        </div>

        <div class="form-group">
          <label>Objective Count *</label>
          <input v-model.number="item.objective_count" type="number" min="1" required>
        </div>

        <div class="form-group">
          <label>Cash Reward</label>
          <input v-model.number="item.cash_reward" type="number" min="0">
        </div>

        <div class="form-group">
          <label>XP Reward</label>
          <input v-model.number="item.experience_reward" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Respect Reward</label>
          <input v-model.number="item.respect_reward" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Required Level</label>
          <input v-model.number="item.required_level" type="number" min="1">
        </div>

        <div class="form-group">
          <label>Cooldown (hours)</label>
          <input v-model.number="item.cooldown_hours" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Sort Order</label>
          <input v-model.number="item.order" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Status</label>
          <div class="checkbox-wrapper">
            <input v-model="item.is_active" type="checkbox" id="active-checkbox">
            <label for="active-checkbox">Active</label>
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
  { key: 'type', label: 'Type' },
  { key: 'objective_type', label: 'Objective' },
  { key: 'objective_count', label: 'Target' },
  { key: 'cash_reward', label: 'Cash Reward', format: (val) => `$${val?.toLocaleString() || 0}` },
  { key: 'experience_reward', label: 'XP', format: (val) => val?.toLocaleString() || 0 },
  { key: 'is_active', label: 'Active', format: (val) => val ? '✅' : '❌' }
]

const defaultItem = {
  name: '',
  description: '',
  type: 'one_time',
  objective_type: 'crimes_committed',
  objective_count: 1,
  cash_reward: 0,
  experience_reward: 0,
  respect_reward: 0,
  required_level: 1,
  cooldown_hours: 0,
  order: 0,
  is_active: true
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
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
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

.checkbox-wrapper label {
  margin: 0;
  cursor: pointer;
  color: #cbd5e1;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>

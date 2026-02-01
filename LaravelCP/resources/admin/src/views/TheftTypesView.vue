<template>
  <ResourceManager
    resourceName="Theft Type"
    endpoint="/admin/theft-types"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. Luxury Car">
        </div>

        <div class="form-group full-width">
          <label>Description</label>
          <textarea v-model="item.description" rows="2" placeholder="Description of this theft type..."></textarea>
        </div>

        <div class="form-group">
          <label>Success Rate (%) *</label>
          <input v-model.number="item.success_rate" type="number" min="1" max="100" required>
        </div>

        <div class="form-group">
          <label>Required Level *</label>
          <input v-model.number="item.required_level" type="number" min="1" required>
        </div>

        <div class="form-group">
          <label>Min Car Value *</label>
          <input v-model.number="item.min_car_value" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Max Car Value *</label>
          <input v-model.number="item.max_car_value" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Max Damage %</label>
          <input v-model.number="item.max_damage" type="number" min="0" max="100">
        </div>

        <div class="form-group">
          <label>Jail Multiplier</label>
          <input v-model.number="item.jail_multiplier" type="number" min="0" step="0.1">
        </div>

        <div class="form-group">
          <label>Cooldown (seconds)</label>
          <input v-model.number="item.cooldown" type="number" min="0">
        </div>
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'success_rate', label: 'Success %' },
  { key: 'min_car_value', label: 'Min Value', format: (val) => `$${val?.toLocaleString() || 0}` },
  { key: 'max_car_value', label: 'Max Value', format: (val) => `$${val?.toLocaleString() || 0}` },
  { key: 'required_level', label: 'Req. Level' },
  { key: 'cooldown', label: 'Cooldown' }
]

const defaultItem = {
  name: '',
  description: '',
  success_rate: 50,
  jail_multiplier: 1.0,
  min_car_value: 1000,
  max_car_value: 5000,
  max_damage: 20,
  cooldown: 300,
  required_level: 1
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
.form-group textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 60px;
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

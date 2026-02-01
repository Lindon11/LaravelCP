<template>
  <ResourceManager
    resourceName="Membership"
    endpoint="/admin/memberships"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. VIP Gold">
        </div>

        <div class="form-group full-width">
          <label>Description</label>
          <textarea v-model="item.description" rows="2" placeholder="Membership benefits..."></textarea>
        </div>

        <div class="form-group">
          <label>Price (USD) *</label>
          <input v-model.number="item.price" type="number" min="0" step="0.01" required>
        </div>

        <div class="form-group">
          <label>Duration (days) *</label>
          <input v-model.number="item.duration_days" type="number" min="1" required>
        </div>

        <div class="form-group">
          <label>Energy Bonus (%)</label>
          <input v-model.number="item.energy_bonus" type="number" min="0">
        </div>

        <div class="form-group">
          <label>XP Bonus (%)</label>
          <input v-model.number="item.xp_bonus" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Cash Bonus (%)</label>
          <input v-model.number="item.cash_bonus" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Crime Cooldown Reduction (%)</label>
          <input v-model.number="item.cooldown_reduction" type="number" min="0" max="100">
        </div>

        <div class="form-group">
          <label>Status</label>
          <div class="checkbox-wrapper">
            <input v-model="item.active" type="checkbox" id="active-checkbox">
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
  { key: 'price', label: 'Price', format: (val) => `$${val?.toFixed(2)}` },
  { key: 'duration_days', label: 'Duration', format: (val) => `${val} days` },
  { key: 'xp_bonus', label: 'XP Bonus', format: (val) => val ? `+${val}%` : '-' },
  { key: 'active', label: 'Active', format: (val) => val ? '✅' : '❌' }
]

const defaultItem = {
  name: '',
  description: '',
  price: 9.99,
  duration_days: 30,
  energy_bonus: 0,
  xp_bonus: 0,
  cash_bonus: 0,
  cooldown_reduction: 0,
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

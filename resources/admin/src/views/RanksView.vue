<template>
  <ResourceManager
    resourceName="Rank"
    endpoint="/admin/ranks"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Rank Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. Soldier">
        </div>

        <div class="form-group">
          <label>Required Experience *</label>
          <input v-model.number="item.required_exp" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Max Health *</label>
          <input v-model.number="item.max_health" type="number" min="1" required>
        </div>

        <div class="form-group">
          <label>Cash Reward</label>
          <input v-model.number="item.cash_reward" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Bullet Reward</label>
          <input v-model.number="item.bullet_reward" type="number" min="0">
        </div>

        <div class="form-group full-width">
          <label>User Limit</label>
          <input v-model.number="item.user_limit" type="number" min="0" placeholder="0 = unlimited">
        </div>
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'name', label: 'Rank Name', sortable: true },
  { key: 'required_exp', label: 'Required XP' },
  { key: 'max_health', label: 'Max Health' },
  { key: 'cash_reward', label: 'Cash', format: 'currency' }
]

const defaultItem = {
  name: '',
  required_exp: 0,
  max_health: 100,
  cash_reward: 0,
  bullet_reward: 0,
  user_limit: 0
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

.form-group input {
  padding: 0.875rem 1.125rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.625rem;
  background: rgba(15, 23, 42, 0.5);
  color: #f1f5f9;
  font-size: 0.938rem;
  transition: all 0.2s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>

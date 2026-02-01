<template>
  <ResourceManager
    resourceName="Achievement"
    endpoint="/admin/achievements"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. First Kill">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="3" placeholder="Achievement description..."></textarea>
        </div>

        <div class="form-group">
          <label>Type *</label>
          <select v-model="item.type" required>
            <option value="crime_count">Crime Count</option>
            <option value="kills">Kills</option>
            <option value="cash_earned">Cash Earned</option>
            <option value="level_reached">Level Reached</option>
            <option value="properties_owned">Properties Owned</option>
            <option value="gang_joined">Gang Joined</option>
          </select>
        </div>

        <div class="form-group">
          <label>Requirement *</label>
          <input v-model.number="item.requirement" type="number" min="1" required placeholder="Target number">
        </div>

        <div class="form-group">
          <label>Cash Reward</label>
          <input v-model.number="item.reward_cash" type="number" min="0">
        </div>

        <div class="form-group">
          <label>XP Reward</label>
          <input v-model.number="item.reward_xp" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Icon</label>
          <input v-model="item.icon" type="text" placeholder="🏆">
        </div>

        <div class="form-group">
          <label>Sort Order</label>
          <input v-model.number="item.sort_order" type="number" min="0">
        </div>
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'icon', label: '' },
  { key: 'name', label: 'Name', sortable: true },
  { key: 'type', label: 'Type' },
  { key: 'requirement', label: 'Requirement' },
  { key: 'reward_cash', label: 'Cash', format: (val) => `$${val?.toLocaleString() || 0}` },
  { key: 'reward_xp', label: 'XP' }
]

const defaultItem = {
  name: '',
  description: '',
  type: 'crime_count',
  requirement: 1,
  reward_cash: 0,
  reward_xp: 0,
  icon: '🏆',
  sort_order: 0
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

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>

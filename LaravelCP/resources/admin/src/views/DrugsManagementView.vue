<template>
  <ResourceManager
    resourceName="Drug"
    endpoint="/admin/drugs"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. Cannabis">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="3" placeholder="Drug description..."></textarea>
        </div>

        <div class="form-group">
          <label>Base Price *</label>
          <input v-model.number="item.base_price" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Min Price *</label>
          <input v-model.number="item.min_price" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Max Price *</label>
          <input v-model.number="item.max_price" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Bust Chance (0-1) *</label>
          <input v-model.number="item.bust_chance" type="number" min="0" max="1" step="0.01" required>
        </div>

        <div class="form-group full-width">
          <label>Image URL</label>
          <input v-model="item.image" type="text" placeholder="https://...">
        </div>
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'base_price', label: 'Base Price', format: 'currency' },
  { key: 'min_price', label: 'Min', format: 'currency' },
  { key: 'max_price', label: 'Max', format: 'currency' },
  { key: 'bust_chance', label: 'Bust %', format: (val) => `${(val * 100).toFixed(0)}%` }
]

const defaultItem = {
  name: '',
  description: '',
  base_price: 100,
  min_price: 50,
  max_price: 200,
  bust_chance: 0.1,
  image: ''
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
  font-family: inherit;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>

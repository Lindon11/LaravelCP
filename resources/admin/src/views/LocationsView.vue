<template>
  <ResourceManager
    resourceName="Location"
    endpoint="/admin/locations"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. New York">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="4" placeholder="Location description..."></textarea>
        </div>

        <div class="form-group">
          <label>Travel Cost *</label>
          <input v-model.number="item.travel_cost" type="number" min="0" step="0.01" required>
        </div>

        <div class="form-group">
          <label>Required Level *</label>
          <input v-model.number="item.required_level" type="number" min="1" required>
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
  { key: 'travel_cost', label: 'Travel Cost', format: 'currency' },
  { key: 'required_level', label: 'Required Level' }
]

const defaultItem = {
  name: '',
  description: '',
  travel_cost: 0,
  required_level: 1,
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

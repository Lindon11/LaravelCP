<template>
  <ResourceManager
    resourceName="Combat Location"
    endpoint="/admin/combat-locations"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. ARCADE">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="3" placeholder="Description of the location..."></textarea>
        </div>

        <div class="form-group">
          <label>Icon (Emoji)</label>
          <input v-model="item.icon" type="text" placeholder="🎮" maxlength="10">
        </div>

        <div class="form-group">
          <label>Image URL</label>
          <input v-model="item.image" type="text" placeholder="https://...">
        </div>

        <div class="form-group">
          <label>Minimum Level</label>
          <input v-model.number="item.min_level" type="number" min="1">
        </div>

        <div class="form-group">
          <label>Display Order</label>
          <input v-model.number="item.order" type="number" min="0">
        </div>

        <div class="form-group full-width">
          <label>Active</label>
          <div class="checkbox-wrapper">
            <input v-model="item.active" type="checkbox" id="active-checkbox">
            <label for="active-checkbox">Enable this location</label>
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
  { key: 'min_level', label: 'Min Level' },
  { key: 'order', label: 'Order' },
  {
    key: 'active',
    label: 'Status',
    format: (val) => val ? '🟢' : '🔴'
  }
]

const defaultItem = {
  name: '',
  description: '',
  image: null,
  min_level: 1,
  order: 0,
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

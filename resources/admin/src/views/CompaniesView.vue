<template>
  <ResourceManager
    resourceName="Company"
    endpoint="/admin/employment/companies"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Company Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. TechCorp Industries">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="3" placeholder="Company description..."></textarea>
        </div>

        <div class="form-group">
          <label>Type *</label>
          <select v-model="item.type" required>
            <option value="tech">Technology</option>
            <option value="finance">Finance</option>
            <option value="retail">Retail</option>
            <option value="manufacturing">Manufacturing</option>
            <option value="service">Service</option>
            <option value="healthcare">Healthcare</option>
            <option value="entertainment">Entertainment</option>
          </select>
        </div>

        <div class="form-group">
          <label>Rating (0-100)</label>
          <input v-model.number="item.rating" type="number" min="0" max="100">
        </div>

        <div class="form-group">
          <label>Owner ID</label>
          <input v-model.number="item.owner_id" type="number" min="1" placeholder="Leave empty for NPC">
        </div>

        <div class="form-group">
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
  { key: 'rating', label: 'Rating' },
  { key: 'employees_count', label: 'Employees' },
  { key: 'is_active', label: 'Active', format: 'boolean' }
]

const defaultItem = {
  name: '',
  type: 'tech',
  description: '',
  rating: 50,
  owner_id: null,
  employees_count: 0,
  total_profit: 0,
  is_active: true
}
</script>

<template>
  <ResourceManager
    resourceName="Job Position"
    endpoint="/admin/employment/positions"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Position Title *</label>
          <input v-model="item.title" type="text" required placeholder="e.g. Software Engineer">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="3" placeholder="Position description..."></textarea>
        </div>

        <div class="form-group">
          <label>Company ID *</label>
          <input v-model.number="item.company_id" type="number" required min="1">
        </div>

        <div class="form-group">
          <label>Base Salary *</label>
          <input v-model.number="item.base_salary" type="number" required min="0" placeholder="e.g. 5000">
        </div>

        <div class="form-group">
          <label>Required Level</label>
          <input v-model.number="item.required_level" type="number" min="1" max="100">
        </div>

        <div class="form-group">
          <label>Required Intelligence</label>
          <input v-model.number="item.required_intelligence" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Required Endurance</label>
          <input v-model.number="item.required_endurance" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Max Employees</label>
          <input v-model.number="item.max_employees" type="number" min="1">
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
  { key: 'title', label: 'Title', sortable: true },
  { key: 'company_id', label: 'Company ID' },
  { key: 'base_salary', label: 'Salary', format: 'currency' },
  { key: 'required_level', label: 'Level Req.' },
  { key: 'current_employees', label: 'Employees' },
  { key: 'max_employees', label: 'Max' },
  { key: 'is_active', label: 'Active', format: 'boolean' }
]

const defaultItem = {
  company_id: null,
  title: '',
  description: '',
  required_level: 1,
  required_intelligence: 0,
  required_endurance: 0,
  base_salary: 1000,
  max_employees: 10,
  current_employees: 0,
  perks: null,
  is_active: true
}
</script>

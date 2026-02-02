<template>
  <ResourceManager
    resourceName="Course"
    endpoint="/admin/education/courses"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Course Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. Computer Science 101">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="3" placeholder="Course description..."></textarea>
        </div>

        <div class="form-group">
          <label>Subject *</label>
          <select v-model="item.subject" required>
            <option value="intelligence">Intelligence</option>
            <option value="endurance">Endurance</option>
            <option value="charisma">Charisma</option>
            <option value="technical">Technical</option>
            <option value="business">Business</option>
          </select>
        </div>

        <div class="form-group">
          <label>Difficulty *</label>
          <select v-model="item.difficulty" required>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
            <option value="expert">Expert</option>
          </select>
        </div>

        <div class="form-group">
          <label>Duration (days) *</label>
          <input v-model.number="item.duration_days" type="number" required min="1">
        </div>

        <div class="form-group">
          <label>Cost *</label>
          <input v-model.number="item.cost" type="number" required min="0">
        </div>

        <div class="form-group">
          <label>Intelligence Gain</label>
          <input v-model.number="item.intelligence_gain" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Endurance Gain</label>
          <input v-model.number="item.endurance_gain" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Required Level</label>
          <input v-model.number="item.required_level" type="number" min="1">
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
  { key: 'subject', label: 'Subject' },
  { key: 'difficulty', label: 'Difficulty' },
  { key: 'duration_days', label: 'Duration' },
  { key: 'cost', label: 'Cost', format: 'currency' },
  { key: 'is_active', label: 'Active', format: 'boolean' }
]

const defaultItem = {
  name: '',
  subject: 'intelligence',
  description: '',
  difficulty: 'beginner',
  duration_days: 7,
  cost: 5000,
  intelligence_gain: 0,
  endurance_gain: 0,
  required_level: 1,
  max_students: 50,
  is_active: true
}
</script>

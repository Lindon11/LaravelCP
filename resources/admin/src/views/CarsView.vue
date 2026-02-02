<template>
  <ResourceManager
    title="Cars"
    :columns="columns"
    :form-fields="formFields"
    endpoint="/admin/cars"
  >
    <template #form="{ formData }">
      <div class="grid grid-cols-2 gap-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Car Name</label>
          <input
            id="name"
            v-model="formData.name"
            type="text"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="value" class="block text-sm font-medium text-gray-300 mb-2">Value ($)</label>
          <input
            id="value"
            v-model.number="formData.value"
            type="number"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div class="col-span-2">
          <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
          <textarea
            id="description"
            v-model="formData.description"
            rows="3"
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="theft_chance" class="block text-sm font-medium text-gray-300 mb-2">Theft Success Chance (%)</label>
          <input
            id="theft_chance"
            v-model.number="formData.theft_chance"
            type="number"
            min="0"
            max="100"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="required_level" class="block text-sm font-medium text-gray-300 mb-2">Required Level</label>
          <input
            id="required_level"
            v-model.number="formData.required_level"
            type="number"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'value', label: 'Value', format: (value) => `$${parseInt(value).toLocaleString()}` },
  { key: 'theft_chance', label: 'Theft Chance', format: (value) => `${value}%` },
  { key: 'required_level', label: 'Required Level' },
]

const formFields = {
  name: '',
  description: '',
  value: 0,
  theft_chance: 50,
  required_level: 1,
}
</script>

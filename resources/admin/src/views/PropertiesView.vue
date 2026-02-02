<template>
  <ResourceManager
    title="Properties"
    :columns="columns"
    :form-fields="formFields"
    endpoint="/admin/properties"
  >
    <template #form="{ formData }">
      <div class="grid grid-cols-2 gap-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Property Name</label>
          <input
            id="name"
            v-model="formData.name"
            type="text"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="type" class="block text-sm font-medium text-gray-300 mb-2">Property Type</label>
          <select
            id="type"
            v-model="formData.type"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          >
            <option value="">Select Type</option>
            <option value="house">House</option>
            <option value="apartment">Apartment</option>
            <option value="mansion">Mansion</option>
            <option value="warehouse">Warehouse</option>
            <option value="business">Business</option>
            <option value="casino">Casino</option>
          </select>
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
          <label for="price" class="block text-sm font-medium text-gray-300 mb-2">Purchase Price ($)</label>
          <input
            id="price"
            v-model.number="formData.price"
            type="number"
            step="0.01"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="income_per_day" class="block text-sm font-medium text-gray-300 mb-2">Daily Income ($)</label>
          <input
            id="income_per_day"
            v-model.number="formData.income_per_day"
            type="number"
            step="0.01"
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
  { key: 'type', label: 'Type' },
  { key: 'price', label: 'Price', format: (value) => `$${parseFloat(value).toLocaleString()}` },
  { key: 'income_per_day', label: 'Daily Income', format: (value) => `$${parseFloat(value).toLocaleString()}` },
  { key: 'required_level', label: 'Required Level' },
]

const formFields = {
  name: '',
  type: '',
  description: '',
  price: 0,
  income_per_day: 0,
  required_level: 1,
}
</script>

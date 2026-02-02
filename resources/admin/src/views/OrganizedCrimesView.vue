<template>
  <ResourceManager
    title="Organized Crimes"
    :columns="columns"
    :form-fields="formFields"
    endpoint="/admin/organized-crimes"
  >
    <template #form="{ formData }">
      <div class="grid grid-cols-2 gap-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Crime Name</label>
          <input
            id="name"
            v-model="formData.name"
            type="text"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="success_rate" class="block text-sm font-medium text-gray-300 mb-2">Success Rate (%)</label>
          <input
            id="success_rate"
            v-model.number="formData.success_rate"
            type="number"
            step="0.01"
            min="0"
            max="100"
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
          <label for="min_reward" class="block text-sm font-medium text-gray-300 mb-2">Min Reward ($)</label>
          <input
            id="min_reward"
            v-model.number="formData.min_reward"
            type="number"
            step="0.01"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="max_reward" class="block text-sm font-medium text-gray-300 mb-2">Max Reward ($)</label>
          <input
            id="max_reward"
            v-model.number="formData.max_reward"
            type="number"
            step="0.01"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="required_members" class="block text-sm font-medium text-gray-300 mb-2">Required Members</label>
          <input
            id="required_members"
            v-model.number="formData.required_members"
            type="number"
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

        <div>
          <label for="cooldown" class="block text-sm font-medium text-gray-300 mb-2">Cooldown (seconds)</label>
          <input
            id="cooldown"
            v-model.number="formData.cooldown"
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
  { key: 'required_members', label: 'Members' },
  { key: 'success_rate', label: 'Success Rate', format: (value) => `${parseFloat(value).toFixed(1)}%` },
  { key: 'min_reward', label: 'Min Reward', format: (value) => `$${parseFloat(value).toLocaleString()}` },
  { key: 'max_reward', label: 'Max Reward', format: (value) => `$${parseFloat(value).toLocaleString()}` },
]

const formFields = {
  name: '',
  description: '',
  success_rate: 50,
  min_reward: 0,
  max_reward: 0,
  required_members: 2,
  required_level: 1,
  cooldown: 3600,
}
</script>

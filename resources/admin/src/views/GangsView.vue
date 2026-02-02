<template>
  <ResourceManager
    title="Gangs"
    :columns="columns"
    :form-fields="formFields"
    endpoint="/admin/gangs"
  >
    <template #form="{ formData }">
      <div class="grid grid-cols-2 gap-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Gang Name</label>
          <input
            id="name"
            v-model="formData.name"
            type="text"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="tag" class="block text-sm font-medium text-gray-300 mb-2">Gang Tag</label>
          <input
            id="tag"
            v-model="formData.tag"
            type="text"
            maxlength="10"
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

        <div class="col-span-2">
          <label for="logo" class="block text-sm font-medium text-gray-300 mb-2">Logo URL</label>
          <input
            id="logo"
            v-model="formData.logo"
            type="url"
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="leader_id" class="block text-sm font-medium text-gray-300 mb-2">Leader User ID</label>
          <input
            id="leader_id"
            v-model.number="formData.leader_id"
            type="number"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="max_members" class="block text-sm font-medium text-gray-300 mb-2">Max Members</label>
          <input
            id="max_members"
            v-model.number="formData.max_members"
            type="number"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="bank" class="block text-sm font-medium text-gray-300 mb-2">Gang Bank ($)</label>
          <input
            id="bank"
            v-model.number="formData.bank"
            type="number"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="respect" class="block text-sm font-medium text-gray-300 mb-2">Respect</label>
          <input
            id="respect"
            v-model.number="formData.respect"
            type="number"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="level" class="block text-sm font-medium text-gray-300 mb-2">Gang Level</label>
          <input
            id="level"
            v-model.number="formData.level"
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
  { key: 'tag', label: 'Tag' },
  { key: 'respect', label: 'Respect', format: (value) => parseInt(value).toLocaleString() },
  { key: 'level', label: 'Level' },
  { key: 'max_members', label: 'Max Members' },
]

const formFields = {
  name: '',
  tag: '',
  description: '',
  logo: '',
  leader_id: null,
  bank: 0,
  respect: 0,
  max_members: 10,
  level: 1,
}
</script>

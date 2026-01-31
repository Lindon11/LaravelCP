<template>
  <ResourceManager
    title="Announcements"
    :columns="columns"
    :form-fields="formFields"
    endpoint="/admin/announcements"
  >
    <template #form="{ formData }">
      <div class="grid grid-cols-2 gap-6">
        <div>
          <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title</label>
          <input
            id="title"
            v-model="formData.title"
            type="text"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="type" class="block text-sm font-medium text-gray-300 mb-2">Type</label>
          <select
            id="type"
            v-model="formData.type"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          >
            <option value="news">News</option>
            <option value="event">Event</option>
            <option value="maintenance">Maintenance</option>
            <option value="update">Update</option>
            <option value="alert">Alert</option>
          </select>
        </div>

        <div class="col-span-2">
          <label for="message" class="block text-sm font-medium text-gray-300 mb-2">Message</label>
          <textarea
            id="message"
            v-model="formData.message"
            rows="4"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="created_by" class="block text-sm font-medium text-gray-300 mb-2">Created By (User ID)</label>
          <input
            id="created_by"
            v-model.number="formData.created_by"
            type="number"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="target" class="block text-sm font-medium text-gray-300 mb-2">Target Audience</label>
          <select
            id="target"
            v-model="formData.target"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          >
            <option value="all">All Users</option>
            <option value="level_range">Level Range</option>
            <option value="location">Specific Location</option>
          </select>
        </div>

        <div>
          <label for="min_level" class="block text-sm font-medium text-gray-300 mb-2">Min Level (optional)</label>
          <input
            id="min_level"
            v-model.number="formData.min_level"
            type="number"
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="max_level" class="block text-sm font-medium text-gray-300 mb-2">Max Level (optional)</label>
          <input
            id="max_level"
            v-model.number="formData.max_level"
            type="number"
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="published_at" class="block text-sm font-medium text-gray-300 mb-2">Published At</label>
          <input
            id="published_at"
            v-model="formData.published_at"
            type="datetime-local"
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="expires_at" class="block text-sm font-medium text-gray-300 mb-2">Expires At</label>
          <input
            id="expires_at"
            v-model="formData.expires_at"
            type="datetime-local"
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label class="flex items-center space-x-2 cursor-pointer">
            <input
              v-model="formData.is_active"
              type="checkbox"
              class="w-5 h-5 rounded border-slate-600/50 bg-slate-700/50 text-blue-500 focus:ring-2 focus:ring-blue-500"
            />
            <span class="text-sm text-gray-300">Active</span>
          </label>
        </div>

        <div>
          <label class="flex items-center space-x-2 cursor-pointer">
            <input
              v-model="formData.is_sticky"
              type="checkbox"
              class="w-5 h-5 rounded border-slate-600/50 bg-slate-700/50 text-blue-500 focus:ring-2 focus:ring-blue-500"
            />
            <span class="text-sm text-gray-300">Sticky (Pin to Top)</span>
          </label>
        </div>
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'title', label: 'Title' },
  { key: 'type', label: 'Type' },
  { key: 'is_active', label: 'Active', format: (value) => value ? '✓' : '✗' },
  { key: 'is_sticky', label: 'Sticky', format: (value) => value ? '📌' : '' },
  { key: 'published_at', label: 'Published' },
]

const formFields = {
  created_by: null,
  title: '',
  message: '',
  type: 'news',
  target: 'all',
  min_level: null,
  max_level: null,
  location_id: null,
  published_at: null,
  expires_at: null,
  is_active: true,
  is_sticky: false,
}
</script>

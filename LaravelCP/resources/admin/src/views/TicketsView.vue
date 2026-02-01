<template>
  <ResourceManager
    title="Tickets"
    :columns="columns"
    :form-fields="formFields"
    endpoint="/admin/support/tickets"
  >
    <template #form="{ formData }">
      <div class="grid grid-cols-2 gap-6">
        <div class="col-span-2">
          <label for="subject" class="block text-sm font-medium text-gray-300 mb-2">Subject</label>
          <input
            id="subject"
            v-model="formData.subject"
            type="text"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div class="col-span-2">
          <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
          <textarea
            id="description"
            v-model="formData.description"
            rows="4"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="user_id" class="block text-sm font-medium text-gray-300 mb-2">User ID</label>
          <input
            id="user_id"
            v-model.number="formData.user_id"
            type="number"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="ticket_category_id" class="block text-sm font-medium text-gray-300 mb-2">Category ID</label>
          <input
            id="ticket_category_id"
            v-model.number="formData.ticket_category_id"
            type="number"
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
          <select
            id="status"
            v-model="formData.status"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          >
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="pending">Pending</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
          </select>
        </div>

        <div>
          <label for="priority" class="block text-sm font-medium text-gray-300 mb-2">Priority</label>
          <select
            id="priority"
            v-model="formData.priority"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>

        <div>
          <label for="assigned_to" class="block text-sm font-medium text-gray-300 mb-2">Assigned To (User ID)</label>
          <input
            id="assigned_to"
            v-model.number="formData.assigned_to"
            type="number"
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
  { key: 'subject', label: 'Subject' },
  { key: 'status', label: 'Status' },
  { key: 'priority', label: 'Priority' },
  { key: 'user_id', label: 'User ID' },
]

const formFields = {
  user_id: null,
  ticket_category_id: null,
  subject: '',
  description: '',
  status: 'open',
  priority: 'medium',
  assigned_to: null,
}
</script>

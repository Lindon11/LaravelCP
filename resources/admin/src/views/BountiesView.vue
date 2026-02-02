<template>
  <ResourceManager
    title="Bounties"
    :columns="columns"
    :form-fields="formFields"
    endpoint="/admin/bounties"
  >
    <template #form="{ formData }">
      <div class="grid grid-cols-2 gap-6">
        <div>
          <label for="target_id" class="block text-sm font-medium text-gray-300 mb-2">Target User ID</label>
          <input
            id="target_id"
            v-model.number="formData.target_id"
            type="number"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="placed_by" class="block text-sm font-medium text-gray-300 mb-2">Placed By User ID</label>
          <input
            id="placed_by"
            v-model.number="formData.placed_by"
            type="number"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="amount" class="block text-sm font-medium text-gray-300 mb-2">Bounty Amount ($)</label>
          <input
            id="amount"
            v-model.number="formData.amount"
            type="number"
            step="0.01"
            required
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
            <option value="active">Active</option>
            <option value="claimed">Claimed</option>
            <option value="expired">Expired</option>
          </select>
        </div>

        <div class="col-span-2">
          <label for="reason" class="block text-sm font-medium text-gray-300 mb-2">Reason</label>
          <textarea
            id="reason"
            v-model="formData.reason"
            rows="3"
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
  { key: 'target_id', label: 'Target ID' },
  { key: 'amount', label: 'Amount', format: (value) => `$${parseFloat(value).toLocaleString()}` },
  { key: 'status', label: 'Status' },
  { key: 'placed_by', label: 'Placed By' },
]

const formFields = {
  target_id: null,
  placed_by: null,
  amount: 0,
  reason: '',
  status: 'active',
}
</script>

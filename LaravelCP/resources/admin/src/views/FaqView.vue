<template>
  <ResourceManager
    title="FAQ"
    :columns="columns"
    :form-fields="formFields"
    endpoint="/admin/faq"
  >
    <template #form="{ formData }">
      <div class="grid grid-cols-2 gap-6">
        <div class="col-span-2">
          <label for="question" class="block text-sm font-medium text-gray-300 mb-2">Question</label>
          <input
            id="question"
            v-model="formData.question"
            type="text"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div class="col-span-2">
          <label for="answer" class="block text-sm font-medium text-gray-300 mb-2">Answer</label>
          <textarea
            id="answer"
            v-model="formData.answer"
            rows="5"
            required
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="category_id" class="block text-sm font-medium text-gray-300 mb-2">Category ID (optional)</label>
          <input
            id="category_id"
            v-model.number="formData.category_id"
            type="number"
            class="w-full px-4 py-2.5 bg-slate-700/50 border-2 border-slate-600/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <div>
          <label for="order" class="block text-sm font-medium text-gray-300 mb-2">Display Order</label>
          <input
            id="order"
            v-model.number="formData.order"
            type="number"
            required
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
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'question', label: 'Question' },
  { key: 'order', label: 'Order' },
  { key: 'is_active', label: 'Active', format: (value) => value ? '✓' : '✗' },
]

const formFields = {
  category_id: null,
  question: '',
  answer: '',
  order: 0,
  is_active: true,
}
</script>

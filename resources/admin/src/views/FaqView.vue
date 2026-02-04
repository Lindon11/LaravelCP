<template>
  <div class="space-y-6">
    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="relative w-full sm:w-80">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search FAQs..."
          @input="debouncedSearch"
          class="w-full pl-10 pr-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
        />
      </div>
      <button
        @click="showCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all"
      >
        <PlusIcon class="w-5 h-5" />
        Add FAQ
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-12 h-12 border-4 border-amber-500/30 border-t-amber-500 rounded-full animate-spin mb-4"></div>
        <p class="text-slate-400">Loading FAQs...</p>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="faqs.length === 0" class="rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-12">
      <div class="flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-700/30 flex items-center justify-center mb-4">
          <QuestionMarkCircleIcon class="w-8 h-8 text-slate-500" />
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">No FAQs yet</h3>
        <p class="text-slate-400 text-center max-w-sm mb-4">Create your first FAQ to help users find answers</p>
        <button @click="showCreateModal" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium transition-colors">
          Add FAQ
        </button>
      </div>
    </div>

    <!-- FAQ Accordion -->
    <div v-else class="space-y-3">
      <TransitionGroup name="list">
        <div
          v-for="(faq, index) in faqs"
          :key="faq.id"
          :class="[
            'group bg-slate-800/50 hover:bg-slate-800 backdrop-blur-sm rounded-2xl border transition-all overflow-hidden',
            expandedId === faq.id ? 'border-amber-500/50' : 'border-slate-700/50 hover:border-slate-600/50'
          ]"
        >
          <div class="flex items-center gap-4 p-5 cursor-pointer" @click="toggleExpand(faq.id)">
            <div class="w-8 h-8 rounded-lg bg-slate-700/50 flex items-center justify-center text-sm font-semibold text-slate-400">
              {{ index + 1 }}
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-medium text-white group-hover:text-amber-400 transition-colors">{{ faq.question }}</h3>
            </div>
            <div class="flex items-center gap-3">
              <span :class="['inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium', faq.is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-600/50 text-slate-400']">
                {{ faq.is_active ? 'Active' : 'Inactive' }}
              </span>
              <button @click.stop="editFaq(faq)" class="p-2 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-700/50 transition-colors">
                <PencilIcon class="w-4 h-4" />
              </button>
              <button @click.stop="deleteFaq(faq)" class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                <TrashIcon class="w-4 h-4" />
              </button>
              <ChevronDownIcon :class="['w-5 h-5 text-slate-400 transition-transform duration-200', expandedId === faq.id && 'rotate-180']" />
            </div>
          </div>
          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-96"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 max-h-96"
            leave-to-class="opacity-0 max-h-0"
          >
            <div v-if="expandedId === faq.id" class="overflow-hidden">
              <div class="px-5 pb-5 pt-0 pl-16">
                <div class="p-4 rounded-xl bg-slate-900/50 border border-slate-700/50">
                  <p class="text-slate-300 whitespace-pre-wrap">{{ faq.answer }}</p>
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </TransitionGroup>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
          <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-2xl">
            <div class="flex items-center justify-between p-6 border-b border-slate-700">
              <h2 class="text-lg font-bold text-white">{{ editingItem ? 'Edit FAQ' : 'Add FAQ' }}</h2>
              <button @click="closeModal" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>
            <div class="p-6 space-y-5">
              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Question *</label>
                <input v-model="formData.question" type="text" placeholder="What is the question?" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all" />
              </div>
              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Answer *</label>
                <textarea v-model="formData.answer" rows="6" placeholder="Write the answer..." class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all resize-none"></textarea>
              </div>
              <div class="flex items-center gap-6 pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="formData.is_active" type="checkbox" class="w-5 h-5 rounded border-slate-600 text-amber-500 focus:ring-amber-500 focus:ring-offset-0 bg-slate-700" />
                  <span class="text-sm text-slate-300">Active</span>
                </label>
              </div>
            </div>
            <div class="flex items-center justify-end gap-3 p-6 border-t border-slate-700 bg-slate-800/50">
              <button @click="closeModal" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl font-medium transition-colors">Cancel</button>
              <button @click="saveFaq" :disabled="saving" class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50">
                {{ saving ? 'Saving...' : (editingItem ? 'Save Changes' : 'Add FAQ') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'
import { MagnifyingGlassIcon, PlusIcon, XMarkIcon, PencilIcon, TrashIcon, QuestionMarkCircleIcon, ChevronDownIcon } from '@heroicons/vue/24/outline'

const toast = useToast()
const faqs = ref([])
const loading = ref(false)
const searchQuery = ref('')
const expandedId = ref(null)
const showModal = ref(false)
const editingItem = ref(null)
const saving = ref(false)
let searchTimeout = null

const formData = ref({ question: '', answer: '', order: 0, is_active: true, category_id: null })

onMounted(() => fetchFaqs())

const fetchFaqs = async () => {
  loading.value = true
  try {
    const params = { search: searchQuery.value }
    const response = await api.get('/admin/content/faqs', { params })
    faqs.value = (response.data.data || response.data).sort((a, b) => a.order - b.order)
  } catch (err) {
    toast.error('Failed to load FAQs')
  } finally {
    loading.value = false
  }
}

const debouncedSearch = () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(fetchFaqs, 300) }
const toggleExpand = (id) => { expandedId.value = expandedId.value === id ? null : id }
const showCreateModal = () => { editingItem.value = null; formData.value = { question: '', answer: '', order: faqs.value.length, is_active: true, category_id: null }; showModal.value = true }
const editFaq = (item) => { editingItem.value = item; formData.value = { ...item }; showModal.value = true }
const closeModal = () => { showModal.value = false; editingItem.value = null }

const saveFaq = async () => {
  if (!formData.value.question || !formData.value.answer) { toast.error('Question and answer are required'); return }
  saving.value = true
  try {
    if (editingItem.value) {
      await api.patch(`/admin/content/faqs/${editingItem.value.id}`, formData.value)
      toast.success('FAQ updated')
    } else {
      await api.post('/admin/content/faqs', formData.value)
      toast.success('FAQ created')
    }
    closeModal(); fetchFaqs()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save FAQ')
  } finally {
    saving.value = false
  }
}

const deleteFaq = async (item) => {
  if (!confirm(`Delete this FAQ?`)) return
  try {
    await api.delete(`/admin/content/faqs/${item.id}`)
    toast.success('FAQ deleted')
    fetchFaqs()
  } catch (err) {
    toast.error('Failed to delete FAQ')
  }
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.list-enter-active, .list-leave-active { transition: all 0.3s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(-10px); }
</style>

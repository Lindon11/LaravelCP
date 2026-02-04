<template>
  <div class="p-6 space-y-6">
    <!-- Action Buttons -->
    <div class="flex items-center justify-end gap-3">
      <button @click="showTagsManagement = true" class="flex items-center gap-2 px-4 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 hover:text-white rounded-xl border border-slate-700/50 font-medium transition-all">
        <TagIcon class="w-5 h-5" />
        Manage Tags
      </button>
      <button @click="showEventModal" class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all">
        <PlusIcon class="w-5 h-5" />
        Add Event
      </button>
    </div>

    <!-- Month Navigation -->
    <div class="flex items-center justify-center gap-4 bg-slate-800/30 backdrop-blur-sm p-4 rounded-xl border border-slate-700/50">
      <button @click="previousMonth" class="p-2 hover:bg-slate-700/50 rounded-lg text-slate-400 hover:text-white transition-all">
        <ChevronLeftIcon class="w-6 h-6" />
      </button>
      <h3 class="text-xl font-bold text-white min-w-[200px] text-center">{{ monthYear }}</h3>
      <button @click="nextMonth" class="p-2 hover:bg-slate-700/50 rounded-lg text-slate-400 hover:text-white transition-all">
        <ChevronRightIcon class="w-6 h-6" />
      </button>
      <button @click="goToToday" class="ml-4 px-4 py-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 rounded-lg font-medium transition-all border border-blue-500/30">
        Today
      </button>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-slate-800/30 backdrop-blur-sm rounded-2xl border border-slate-700/50 overflow-hidden">
      <!-- Weekday Headers -->
      <div class="grid grid-cols-7 bg-slate-800/50 border-b border-slate-700/50">
        <div v-for="day in weekdays" :key="day" class="px-4 py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">
          {{ day }}
        </div>
      </div>

      <!-- Calendar Days -->
      <div class="grid grid-cols-7 gap-px bg-slate-700/30">
        <div
          v-for="(day, index) in calendarDays"
          :key="index"
          :class="[
            'min-h-[120px] bg-slate-900/40 p-3 cursor-pointer transition-all hover:bg-slate-800/60',
            !day.isCurrentMonth && 'opacity-40',
            day.isToday && 'bg-blue-500/10 ring-2 ring-blue-500/50 ring-inset'
          ]"
          @click="selectDay(day)"
        >
          <div :class="[
            'text-sm font-semibold mb-2',
            day.isToday ? 'text-blue-400' : 'text-slate-300'
          ]">
            {{ day.dayNumber }}
          </div>

          <div class="space-y-1">
            <div
              v-for="event in day.events.slice(0, 3)"
              :key="event.id"
              :style="{ backgroundColor: getTagColor(event.tag) }"
              class="text-xs px-2 py-1 rounded text-white font-medium truncate cursor-pointer hover:opacity-80 transition-opacity"
              @click.stop="editEvent(event)"
            >
              {{ event.title }}
            </div>
            <div v-if="day.events.length > 3" class="text-xs text-slate-500 pl-2">
              +{{ day.events.length - 3 }} more
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Event Modal -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="closeModal">
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div class="bg-slate-800 rounded-2xl border border-slate-700/50 shadow-2xl w-full max-w-md">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-700/50">
              <h3 class="text-xl font-bold text-white">{{ editingEvent ? 'Edit Event' : 'New Event' }}</h3>
              <button @click="closeModal" class="p-1 hover:bg-slate-700/50 rounded-lg text-slate-400 hover:text-white transition-colors">
                <XMarkIcon class="w-6 h-6" />
              </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Event Title</label>
                <input
                  v-model="formData.title"
                  type="text"
                  required
                  class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                  placeholder="Enter event title"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Description</label>
                <textarea
                  v-model="formData.description"
                  rows="3"
                  class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all resize-none"
                  placeholder="Add event description"
                ></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-2">Date</label>
                  <input
                    v-model="formData.date"
                    type="date"
                    required
                    class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-300 mb-2">Time</label>
                  <input
                    v-model="formData.time"
                    type="time"
                    class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Tag</label>
                <select
                  v-model="formData.tag"
                  class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
                >
                  <option v-for="tag in tags" :key="tag.id" :value="tag.id">
                    {{ tag.name }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between p-6 border-t border-slate-700/50">
              <button
                v-if="editingEvent"
                @click="deleteEvent"
                class="px-4 py-2.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-xl font-medium border border-red-500/30 transition-all"
              >
                Delete
              </button>
              <div :class="['flex gap-3', !editingEvent && 'ml-auto']">
                <button
                  @click="closeModal"
                  class="px-4 py-2.5 bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl font-medium transition-all"
                >
                  Cancel
                </button>
                <button
                  @click="saveEvent"
                  class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all"
                >
                  Save Event
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- Tags Management Modal -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showTagsManagement" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showTagsManagement = false">
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div class="bg-slate-800 rounded-2xl border border-slate-700/50 shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-700/50">
              <h3 class="text-xl font-bold text-white">Manage Tags</h3>
              <button @click="showTagsManagement = false" class="p-1 hover:bg-slate-700/50 rounded-lg text-slate-400 hover:text-white transition-colors">
                <XMarkIcon class="w-6 h-6" />
              </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-3 overflow-y-auto flex-1">
              <div v-for="tag in tags" :key="tag.id" class="flex items-center gap-3 p-4 bg-slate-900/50 rounded-xl border border-slate-700/50">
                <input
                  v-model="tag.name"
                  type="text"
                  class="flex-1 px-4 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all"
                  placeholder="Tag name"
                />
                <input
                  v-model="tag.color"
                  type="color"
                  class="w-12 h-10 rounded-lg border border-slate-700/50 cursor-pointer bg-slate-800/50"
                />
                <div
                  class="px-4 py-2 rounded-lg text-white font-medium whitespace-nowrap"
                  :style="{ backgroundColor: tag.color }"
                >
                  {{ tag.name }}
                </div>
                <button
                  @click="deleteTag(tag.id)"
                  class="p-2 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg transition-all border border-red-500/30"
                >
                  <TrashIcon class="w-5 h-5" />
                </button>
              </div>

              <button
                @click="addNewTag"
                class="w-full p-4 bg-blue-500/10 hover:bg-blue-500/20 border-2 border-dashed border-blue-500/30 hover:border-blue-500/50 rounded-xl text-blue-400 font-medium transition-all flex items-center justify-center gap-2"
              >
                <PlusIcon class="w-5 h-5" />
                Add New Tag
              </button>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end p-6 border-t border-slate-700/50">
              <button
                @click="saveTags"
                class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all"
              >
                Save Tags
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import {
  TagIcon,
  PlusIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  XMarkIcon,
  TrashIcon,
  CalendarIcon
} from '@heroicons/vue/24/outline'

const toast = useToast()

const currentDate = ref(new Date())
const events = ref([])
const tags = ref([])
const showModal = ref(false)
const showTagsManagement = ref(false)
const editingEvent = ref(null)
const formData = ref({
  title: '',
  description: '',
  date: '',
  time: '',
  tag: null
})

const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const formattedCurrentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const monthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()

  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const prevLastDay = new Date(year, month, 0)

  const firstDayOfWeek = firstDay.getDay()
  const lastDateOfMonth = lastDay.getDate()
  const prevLastDate = prevLastDay.getDate()

  const days = []
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  // Previous month days (grayed out)
  for (let i = firstDayOfWeek - 1; i >= 0; i--) {
    const date = new Date(year, month - 1, prevLastDate - i)
    days.push({
      dayNumber: prevLastDate - i,
      date: date.toISOString().split('T')[0],
      isCurrentMonth: false,
      isToday: false,
      events: getEventsForDate(date)
    })
  }

  // Current month days
  for (let i = 1; i <= lastDateOfMonth; i++) {
    const date = new Date(year, month, i)
    const dateStr = date.toISOString().split('T')[0]
    const isToday = date.getTime() === today.getTime()

    days.push({
      dayNumber: i,
      date: dateStr,
      isCurrentMonth: true,
      isToday,
      events: getEventsForDate(date)
    })
  }

  // Next month days (grayed out) to complete 6-week grid
  const remainingDays = 42 - days.length
  for (let i = 1; i <= remainingDays; i++) {
    const date = new Date(year, month + 1, i)
    days.push({
      dayNumber: i,
      date: date.toISOString().split('T')[0],
      isCurrentMonth: false,
      isToday: false,
      events: getEventsForDate(date)
    })
  }

  return days
})

const getEventsForDate = (date) => {
  const dateStr = date.toISOString().split('T')[0]
  return events.value.filter(event => event.date === dateStr)
}

const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
}

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
}

const goToToday = () => {
  currentDate.value = new Date()
}

const selectDay = (day) => {
  formData.value.date = day.date
  showEventModal()
}

const showEventModal = () => {
  editingEvent.value = null
  formData.value = {
    title: '',
    description: '',
    date: formData.value.date || new Date().toISOString().split('T')[0],
    time: '',
    tag: tags.value.length > 0 ? tags.value[0].id : null
  }
  showModal.value = true
}

const editEvent = (event) => {
  editingEvent.value = event
  formData.value = { ...event }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingEvent.value = null
}

const saveEvent = () => {
  if (!formData.value.title || !formData.value.date) {
    toast.error('Title and date are required')
    return
  }

  if (editingEvent.value) {
    Object.assign(editingEvent.value, formData.value)
    toast.success('Event updated!')
  } else {
    events.value.push({
      id: Date.now(),
      ...formData.value
    })
    toast.success('Event created!')
  }

  closeModal()
}

const deleteEvent = () => {
  events.value = events.value.filter(e => e.id !== editingEvent.value.id)
  toast.success('Event deleted!')
  closeModal()
}

const getTagColor = (tagId) => {
  const tag = tags.value.find(t => t.id === tagId)
  return tag ? tag.color : '#3b82f6'
}

const addNewTag = () => {
  const newTag = {
    id: Date.now(),
    name: 'New Tag',
    color: '#3b82f6'
  }
  tags.value.push(newTag)
}

const deleteTag = (tagId) => {
  if (tags.value.length === 1) {
    toast.error('You must have at least one tag')
    return
  }
  tags.value = tags.value.filter(t => t.id !== tagId)
  // Update events that had this tag
  events.value.forEach(event => {
    if (event.tag === tagId) {
      event.tag = tags.value[0].id
    }
  })
}

const saveTags = () => {
  localStorage.setItem('calendar_tags', JSON.stringify(tags.value))
  showTagsManagement.value = false
  toast.success('Tags saved!')
}

const loadTags = () => {
  const saved = localStorage.getItem('calendar_tags')
  if (saved) {
    tags.value = JSON.parse(saved)
  } else {
    // Default tags
    tags.value = [
      { id: 1, name: 'Meeting', color: '#3b82f6' },
      { id: 2, name: 'Deadline', color: '#ef4444' },
      { id: 3, name: 'Event', color: '#10b981' },
      { id: 4, name: 'Reminder', color: '#f59e0b' }
    ]
  }
}

onMounted(() => {
  loadTags()

  // Mock events for demo
  events.value = [
    { id: 1, title: 'Team Meeting', description: 'Weekly sync', date: new Date().toISOString().split('T')[0], time: '10:00', tag: 1 },
    { id: 2, title: 'Product Launch', description: 'Version 2.0', date: new Date(Date.now() + 86400000 * 3).toISOString().split('T')[0], time: '14:00', tag: 3 },
    { id: 3, title: 'Server Maintenance', description: 'Scheduled downtime', date: new Date(Date.now() + 86400000 * 7).toISOString().split('T')[0], time: '22:00', tag: 4 }
  ]
})
</script>

<style scoped>
/* Minimal custom styles - using Tailwind CSS */
</style>

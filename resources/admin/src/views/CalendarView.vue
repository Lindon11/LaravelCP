<template>
  <div class="calendar-view">
    <div class="calendar-header">
      <div class="calendar-title">
        <h1>📅 Calendar</h1>
        <div class="month-nav">
          <button @click="previousMonth" class="btn-nav">‹</button>
          <span class="current-month">{{ monthYear }}</span>
          <button @click="nextMonth" class="btn-nav">›</button>
          <button @click="goToToday" class="btn-today">Today</button>
        </div>
      </div>
      <div class="header-actions">
        <button @click="showTagsManagement = true" class="btn-secondary">
          <span>🏷️</span> Manage Tags
        </button>
        <button @click="showEventModal" class="btn-primary">
          <span>➕</span> Add Event
        </button>
      </div>
    </div>

    <div class="calendar-grid">
      <div class="weekday-header" v-for="day in weekdays" :key="day">{{ day }}</div>
      <div 
        v-for="(day, index) in calendarDays" 
        :key="index"
        :class="['calendar-day', { 
          'other-month': !day.isCurrentMonth,
          'today': day.isToday,
          'has-events': day.events.length > 0
        }]"
        @click="selectDay(day)"
      >
        <div class="day-number">{{ day.dayNumber }}</div>
        <div class="day-events">
          <div 
            v-for="event in day.events.slice(0, 3)" 
            :key="event.id"
            class="event-chip"
            :style="{ backgroundColor: getTagColor(event.tag), color: '#ffffff' }"
            @click.stop="editEvent(event)"
          >
            {{ event.title }}
          </div>
          <div v-if="day.events.length > 3" class="more-events">
            +{{ day.events.length - 3 }} more
          </div>
        </div>
      </div>
    </div>

    <!-- Event Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ editingEvent ? 'Edit Event' : 'New Event' }}</h2>
          <button @click="closeModal" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Event Title</label>
            <input v-model="formData.title" type="text" required />
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="formData.description" rows="3"></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Date</label>
              <input v-model="formData.date" type="date" required />
            </div>
            <div class="form-group">
              <label>Time</label>
              <input v-model="formData.time" type="time" />
            </div>
          </div>
          <div class="form-group">
            <label>Tag</label>
            <select v-model="formData.tag">
              <option v-for="tag in tags" :key="tag.id" :value="tag.id">
                {{ tag.name }}
              </option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button v-if="editingEvent" @click="deleteEvent" class="btn-danger">Delete</button>
          <button @click="closeModal" class="btn-secondary-modal">Cancel</button>
          <button @click="saveEvent" class="btn-primary">Save</button>
        </div>
      </div>
    </div>

    <!-- Tags Management Modal -->
    <div v-if="showTagsManagement" class="modal-overlay" @click.self="showTagsManagement = false">
      <div class="modal modal-wide">
        <div class="modal-header">
          <h2>Manage Tags</h2>
          <button @click="showTagsManagement = false" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="tags-list">
            <div v-for="tag in tags" :key="tag.id" class="tag-item">
              <input v-model="tag.name" type="text" class="tag-name-input" placeholder="Tag name" />
              <input v-model="tag.color" type="color" class="tag-color-input" />
              <div class="tag-preview" :style="{ backgroundColor: tag.color }">
                {{ tag.name }}
              </div>
              <button @click="deleteTag(tag.id)" class="btn-delete-tag">🗑️</button>
            </div>
          </div>
          <button @click="addNewTag" class="btn-add-tag">
            <span>➕</span> Add New Tag
          </button>
        </div>
        <div class="modal-footer">
          <button @click="saveTags" class="btn-primary">Done</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'

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
.calendar-view {
  width: 100%;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.calendar-title {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.calendar-title h1 {
  margin: 0;
  font-size: 1.375rem;
  color: #ffffff;
}

.month-nav {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-nav {
  width: 32px;
  height: 32px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  background: rgba(30, 41, 59, 0.5);
  color: #ffffff;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 1.25rem;
  transition: all 0.2s;
}

.btn-nav:hover {
  background: rgba(59, 130, 246, 0.2);
  border-color: #3b82f6;
}

.current-month {
  color: #ffffff;
  font-weight: 600;
  font-size: 1rem;
  min-width: 150px;
  text-align: center;
}

.btn-today {
  padding: 0.5rem 0.875rem;
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.2);
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-today:hover {
  background: rgba(59, 130, 246, 0.2);
}

.btn-primary {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-secondary {
  padding: 0.625rem 1.25rem;
  background: rgba(51, 65, 85, 0.5);
  color: #e2e8f0;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background: rgba(148, 163, 184, 0.1);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.5rem;
  overflow: hidden;
}

.weekday-header {
  background: rgba(51, 65, 85, 0.5);
  padding: 0.75rem;
  text-align: center;
  font-weight: 600;
  font-size: 0.75rem;
  color: #cbd5e1;
  text-transform: uppercase;
}

.calendar-day {
  background: rgba(30, 41, 59, 0.4);
  min-height: 100px;
  padding: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.calendar-day:hover {
  background: rgba(30, 41, 59, 0.8);
}

.calendar-day.other-month {
  opacity: 0.3;
}

.calendar-day.other-month .day-number {
  color: #64748b;
}

.calendar-day.today {
  background: rgba(59, 130, 246, 0.1);
  border: 2px solid #3b82f6;
}

.day-number {
  font-weight: 600;
  font-size: 0.875rem;
  color: #e2e8f0;
  margin-bottom: 0.25rem;
}

.day-events {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.event-chip {
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
  transition: all 0.2s;
}

.event-chip:hover {
  transform: translateX(2px);
  opacity: 0.9;
}

.more-events {
  font-size: 0.7rem;
  color: #94a3b8;
  margin-top: 0.125rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: #1e293b;
  border: 1px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.75rem;
  width: 90%;
  max-width: 500px;
}

.modal-wide {
  max-width: 600px;
}

.modal-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(51, 65, 85, 0.5);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  color: #f1f5f9;
  font-size: 1.125rem;
}

.close-btn {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 1.5rem;
  cursor: pointer;
  line-height: 1;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-body {
  padding: 1.25rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.375rem;
  color: #cbd5e1;
  font-size: 0.8125rem;
  font-weight: 600;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.625rem 0.875rem;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 0.375rem;
  color: #fff;
  font-size: 0.875rem;
}

.form-group textarea {
  resize: vertical;
  font-family: inherit;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.modal-footer {
  padding: 1rem 1.25rem;
  border-top: 1px solid rgba(51, 65, 85, 0.5);
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.btn-secondary-modal {
  padding: 0.625rem 1.25rem;
  background: rgba(51, 65, 85, 0.5);
  color: #e2e8f0;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  font-size: 0.875rem;
  cursor: pointer;
}

.btn-danger {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
}

/* Tags Management */
.tags-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.tag-item {
  display: grid;
  grid-template-columns: 1fr auto auto auto;
  gap: 0.75rem;
  align-items: center;
  padding: 0.75rem;
  background: rgba(15, 23, 42, 0.5);
  border-radius: 0.5rem;
}

.tag-name-input {
  padding: 0.5rem 0.75rem;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 0.375rem;
  color: #fff;
  font-size: 0.875rem;
}

.tag-color-input {
  width: 50px;
  height: 38px;
  border: 1px solid #334155;
  border-radius: 0.375rem;
  cursor: pointer;
  background: transparent;
}

.tag-preview {
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.8125rem;
  font-weight: 600;
  color: white;
  white-space: nowrap;
}

.btn-delete-tag {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #ef4444;
  padding: 0.5rem;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.2s;
}

.btn-delete-tag:hover {
  background: rgba(239, 68, 68, 0.2);
}

.btn-add-tag {
  width: 100%;
  padding: 0.75rem;
  background: rgba(59, 130, 246, 0.1);
  border: 2px dashed rgba(59, 130, 246, 0.3);
  border-radius: 0.5rem;
  color: #3b82f6;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.btn-add-tag:hover {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.5);
}
</style>

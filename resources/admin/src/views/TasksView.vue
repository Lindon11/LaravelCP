<template>
  <div class="tasks-view">
    <div class="tasks-header">
      <h1>📋 Tasks</h1>
      <button @click="showTaskModal" class="btn-primary">
        <span>➕</span> Add Task
      </button>
    </div>

    <div class="kanban-board">
      <div 
        v-for="column in columns" 
        :key="column.id"
        class="kanban-column"
      >
        <div class="column-header">
          <div class="column-title">
            <span :class="`status-dot status-${column.id}`"></span>
            {{ column.title }}
            <span class="task-count">{{ getColumnTasks(column.id).length }}</span>
          </div>
        </div>
        
        <div 
          class="column-tasks"
          @drop="onDrop($event, column.id)"
          @dragover.prevent
          @dragenter.prevent
        >
          <div 
            v-for="task in getColumnTasks(column.id)" 
            :key="task.id"
            :class="['task-card', `priority-${task.priority}`]"
            draggable="true"
            @dragstart="onDragStart($event, task)"
            @click="editTask(task)"
          >
            <div class="task-header">
              <div class="task-priority">
                <span :class="`priority-badge priority-${task.priority}`">
                  {{ task.priority }}
                </span>
              </div>
              <span v-if="task.dueDate" class="task-due">📅 {{ formatDate(task.dueDate) }}</span>
            </div>
            <h3 class="task-title">{{ task.title }}</h3>
            <p v-if="task.description" class="task-description">{{ task.description }}</p>
            <div v-if="task.assignee" class="task-footer">
              <div class="task-assignee">
                <span class="avatar">{{ getInitials(task.assignee) }}</span>
                {{ task.assignee }}
              </div>
            </div>
          </div>
          
          <div v-if="getColumnTasks(column.id).length === 0" class="empty-column">
            No tasks
          </div>
        </div>
      </div>
    </div>

    <!-- Task Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ editingTask ? 'Edit Task' : 'New Task' }}</h2>
          <button @click="closeModal" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Task Title</label>
            <input v-model="formData.title" type="text" required />
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="formData.description" rows="3"></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Status</label>
              <select v-model="formData.status">
                <option value="todo">To Do</option>
                <option value="in-progress">In Progress</option>
                <option value="review">Review</option>
                <option value="done">Done</option>
              </select>
            </div>
            <div class="form-group">
              <label>Priority</label>
              <select v-model="formData.priority">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Assignee</label>
              <input v-model="formData.assignee" type="text" placeholder="John Doe" />
            </div>
            <div class="form-group">
              <label>Due Date</label>
              <input v-model="formData.dueDate" type="date" />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button v-if="editingTask" @click="deleteTask" class="btn-danger">Delete</button>
          <button @click="closeModal" class="btn-secondary">Cancel</button>
          <button @click="saveTask" class="btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()

const columns = [
  { id: 'todo', title: 'To Do' },
  { id: 'in-progress', title: 'In Progress' },
  { id: 'review', title: 'Review' },
  { id: 'done', title: 'Done' }
]

const tasks = ref([])
const showModal = ref(false)
const editingTask = ref(null)
const draggedTask = ref(null)
const formData = ref({
  title: '',
  description: '',
  status: 'todo',
  priority: 'medium',
  assignee: '',
  dueDate: ''
})

const getColumnTasks = (columnId) => {
  return tasks.value.filter(task => task.status === columnId)
}

const onDragStart = (event, task) => {
  draggedTask.value = task
  event.dataTransfer.effectAllowed = 'move'
}

const onDrop = (event, newStatus) => {
  if (draggedTask.value) {
    draggedTask.value.status = newStatus
    draggedTask.value = null
    toast.success('Task moved!')
  }
}

const showTaskModal = () => {
  editingTask.value = null
  formData.value = {
    title: '',
    description: '',
    status: 'todo',
    priority: 'medium',
    assignee: '',
    dueDate: ''
  }
  showModal.value = true
}

const editTask = (task) => {
  editingTask.value = task
  formData.value = { ...task }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingTask.value = null
}

const saveTask = () => {
  if (!formData.value.title) {
    toast.error('Title is required')
    return
  }
  
  if (editingTask.value) {
    Object.assign(editingTask.value, formData.value)
    toast.success('Task updated!')
  } else {
    tasks.value.push({
      id: Date.now(),
      ...formData.value
    })
    toast.success('Task created!')
  }
  
  closeModal()
}

const deleteTask = () => {
  tasks.value = tasks.value.filter(t => t.id !== editingTask.value.id)
  toast.success('Task deleted!')
  closeModal()
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

onMounted(() => {
  // Mock tasks for demo
  tasks.value = [
    { id: 1, title: 'Fix login bug', description: 'Users unable to login with special characters', status: 'todo', priority: 'high', assignee: 'John Doe', dueDate: '2026-02-05' },
    { id: 2, title: 'Update documentation', description: 'Add API endpoints documentation', status: 'todo', priority: 'low', assignee: 'Jane Smith', dueDate: '' },
    { id: 3, title: 'Implement dark mode', description: 'Add dark mode toggle to settings', status: 'in-progress', priority: 'medium', assignee: 'Mike Johnson', dueDate: '2026-02-10' },
    { id: 4, title: 'Code review PR #123', description: 'Review payment integration changes', status: 'review', priority: 'high', assignee: 'Sarah Williams', dueDate: '2026-02-03' },
    { id: 5, title: 'Deploy to production', description: 'Release version 2.1.0', status: 'done', priority: 'high', assignee: 'John Doe', dueDate: '2026-02-01' }
  ]
})
</script>

<style scoped>
.tasks-view {
  width: 100%;
}

.tasks-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.tasks-header h1 {
  margin: 0;
  font-size: 1.375rem;
  color: #ffffff;
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

.kanban-board {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  height: calc(100vh - 200px);
}

.kanban-column {
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.5rem;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.column-header {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  background: rgba(51, 65, 85, 0.5);
}

.column-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  color: #e2e8f0;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.status-dot.status-todo {
  background: #94a3b8;
}

.status-dot.status-in-progress {
  background: #3b82f6;
}

.status-dot.status-review {
  background: #f59e0b;
}

.status-dot.status-done {
  background: #10b981;
}

.task-count {
  margin-left: auto;
  background: rgba(148, 163, 184, 0.2);
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
}

.column-tasks {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.column-tasks::-webkit-scrollbar {
  width: 6px;
}

.column-tasks::-webkit-scrollbar-track {
  background: transparent;
}

.column-tasks::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.3);
  border-radius: 3px;
}

.task-card {
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-left: 3px solid;
  border-radius: 0.5rem;
  padding: 0.75rem;
  cursor: grab;
  transition: all 0.2s;
}

.task-card:hover {
  background: rgba(30, 41, 59, 1);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.task-card:active {
  cursor: grabbing;
}

.task-card.priority-low {
  border-left-color: #94a3b8;
}

.task-card.priority-medium {
  border-left-color: #f59e0b;
}

.task-card.priority-high {
  border-left-color: #ef4444;
}

.task-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.priority-badge {
  padding: 0.125rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.priority-badge.priority-low {
  background: rgba(148, 163, 184, 0.2);
  color: #94a3b8;
}

.priority-badge.priority-medium {
  background: rgba(245, 158, 11, 0.2);
  color: #fbbf24;
}

.priority-badge.priority-high {
  background: rgba(239, 68, 68, 0.2);
  color: #f87171;
}

.task-due {
  font-size: 0.7rem;
  color: #94a3b8;
}

.task-title {
  margin: 0 0 0.375rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #f1f5f9;
}

.task-description {
  margin: 0 0 0.75rem 0;
  font-size: 0.8125rem;
  color: #94a3b8;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.task-footer {
  border-top: 1px solid rgba(148, 163, 184, 0.1);
  padding-top: 0.5rem;
  margin-top: 0.5rem;
}

.task-assignee {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #cbd5e1;
}

.avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.625rem;
  font-weight: 600;
  color: white;
}

.empty-column {
  text-align: center;
  padding: 2rem 1rem;
  color: #64748b;
  font-size: 0.875rem;
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

.btn-secondary {
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

@media (max-width: 1400px) {
  .kanban-board {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .kanban-board {
    grid-template-columns: 1fr;
  }
}
</style>

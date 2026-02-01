<template>
  <ResourceManager
    resource-name="Announcement"
    endpoint="/admin/announcements"
    :columns="columns"
    :default-item="defaultItem"
    edit-route="/announcements/:id/edit"
    create-route="/announcements/create"
  />
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

// Get logged-in user from localStorage
const getLoggedInUserId = () => {
  const userData = localStorage.getItem('admin_user')
  if (userData) {
    const user = JSON.parse(userData)
    return user.id
  }
  return 1 // Fallback
}

const defaultItem = {
  created_by: getLoggedInUserId(),
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

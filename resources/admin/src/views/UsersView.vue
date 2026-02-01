<template>
  <ResourceManager
    resource-name="User"
    endpoint="/admin/users"
    :columns="columns"
    :default-item="defaultUser"
    edit-route="/users/:id/edit"
    create-route="/users/create"
  />
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'username', label: 'Username' },
  { key: 'email', label: 'Email' },
  { key: 'experience', label: 'XP' },
  { key: 'cash', label: 'Cash', format: 'currency' },
  { key: 'currentRank.name', label: 'Rank' },
  { key: 'last_active', label: 'Last Active', format: 'datetime' },
]

const defaultUser = {
  username: '',
  email: '',
  name: '',
  password: '',
  cash: 1000,
  bank: 0,
  experience: 0,
}
</script>

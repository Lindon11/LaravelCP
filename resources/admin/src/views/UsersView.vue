<template>
  <ResourceManager
    resource-name="User"
    endpoint="/admin/users"
    :columns="columns"
    :default-item="defaultUser"
  >
    <template #form="{ item }">
      <div class="form-group">
        <label>Username</label>
        <input v-model="item.username" type="text" required />
      </div>
      <div class="form-group">
        <label>Email</label>
        <input v-model="item.email" type="email" required />
      </div>
      <div class="form-group">
        <label>Name</label>
        <input v-model="item.name" type="text" required />
      </div>
      <div class="form-group">
        <label>Password</label>
        <input v-model="item.password" type="password" placeholder="Leave blank to keep current" />
      </div>
      <div class="form-group">
        <label>Cash</label>
        <input v-model.number="item.cash" type="number" />
      </div>
      <div class="form-group">
        <label>Bank</label>
        <input v-model.number="item.bank" type="number" />
      </div>
      <div class="form-group">
        <label>Level</label>
        <input v-model.number="item.level" type="number" />
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'username', label: 'Username' },
  { key: 'email', label: 'Email' },
  { key: 'level', label: 'Level' },
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
  level: 1,
}
</script>

<style scoped>
.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.375rem;
  color: #cbd5e1;
  font-size: 0.8125rem;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 0.625rem 0.875rem;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 0.375rem;
  color: #fff;
  font-size: 0.875rem;
}
</style>

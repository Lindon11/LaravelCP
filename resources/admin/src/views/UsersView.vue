<template>
  <ResourceManager
    resource-name="User"
    endpoint="/admin/users"
    :columns="columns"
    :default-item="defaultUser"
  >
    <template #form="{ item }">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-300">Username</label>
          <input
            v-model="item.username"
            type="text"
            required
            class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
            placeholder="Enter username"
          />
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-300">Email</label>
          <input
            v-model="item.email"
            type="email"
            required
            class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
            placeholder="Enter email address"
          />
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-300">Full Name</label>
          <input
            v-model="item.name"
            type="text"
            required
            class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
            placeholder="Enter full name"
          />
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-300">Password</label>
          <input
            v-model="item.password"
            type="password"
            class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
            placeholder="Leave blank to keep current"
          />
          <p class="text-xs text-slate-400">Leave blank when editing to keep current password</p>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-300">Cash</label>
          <div class="relative">
            <span class="absolute left-4 top-3.5 text-slate-400">$</span>
            <input
              v-model.number="item.cash"
              type="number"
              min="0"
              class="w-full pl-8 pr-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
              placeholder="0"
            />
          </div>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-300">Bank Balance</label>
          <div class="relative">
            <span class="absolute left-4 top-3.5 text-slate-400">$</span>
            <input
              v-model.number="item.bank"
              type="number"
              min="0"
              class="w-full pl-8 pr-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
              placeholder="0"
            />
          </div>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-300">Level</label>
          <input
            v-model.number="item.level"
            type="number"
            min="1"
            class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all"
            placeholder="1"
          />
        </div>
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

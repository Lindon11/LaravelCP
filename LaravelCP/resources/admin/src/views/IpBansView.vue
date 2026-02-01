<template>
  <ResourceManager
    resourceName="IP Ban"
    endpoint="/admin/ip-bans"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>IP Address *</label>
          <input v-model="item.ip_address" type="text" required placeholder="e.g. 192.168.1.1 or 192.168.1.*">
        </div>

        <div class="form-group full-width">
          <label>Reason *</label>
          <textarea v-model="item.reason" required rows="3" placeholder="Reason for banning this IP..."></textarea>
        </div>

        <div class="form-group">
          <label>Expires At</label>
          <input v-model="item.expires_at" type="datetime-local" placeholder="Leave empty for permanent ban">
        </div>

        <div class="form-group">
          <label>Banned By</label>
          <input v-model="item.banned_by" type="text" disabled placeholder="Auto-filled">
        </div>
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'ip_address', label: 'IP Address', sortable: true },
  { key: 'reason', label: 'Reason' },
  { key: 'expires_at', label: 'Expires', format: (val) => val ? new Date(val).toLocaleDateString() : 'Permanent' },
  { key: 'created_at', label: 'Banned At', format: (val) => new Date(val).toLocaleDateString() }
]

const defaultItem = {
  ip_address: '',
  reason: '',
  expires_at: null,
  banned_by: null
}
</script>

<style scoped>
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 600;
  color: #f1f5f9;
  font-size: 0.875rem;
}

.form-group input,
.form-group textarea {
  padding: 0.875rem 1.125rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.625rem;
  background: rgba(15, 23, 42, 0.5);
  color: #f1f5f9;
  font-size: 0.938rem;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>

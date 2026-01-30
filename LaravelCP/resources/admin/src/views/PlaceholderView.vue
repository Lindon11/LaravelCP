<template>
  <div class="placeholder-view">
    <div class="icon">🚧</div>
    <h2>{{ title }} - Coming Soon</h2>
    <p>This admin section is being built. The API endpoint is ready at <code>{{ endpoint }}</code></p>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

defineProps({
  title: { type: String, required: true },
  endpoint: { type: String, required: true }
})
</script>

<style scoped>
.placeholder-view {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  color: #94a3b8;
}

.icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

h2 {
  color: #cbd5e1;
  margin-bottom: 0.5rem;
}

code {
  background: #334155;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  color: #3b82f6;
}
</style>

<template>
  <div class="table-skeleton">
    <div class="table-header-skeleton">
      <div class="skeleton-cell" v-for="i in columns" :key="'h'+i"></div>
    </div>
    <div class="table-row-skeleton" v-for="row in rows" :key="'r'+row">
      <div class="skeleton-cell" v-for="col in columns" :key="'c'+col" :style="{ width: getCellWidth(col) }"></div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  rows: {
    type: Number,
    default: 5
  },
  columns: {
    type: Number,
    default: 5
  }
})

const getCellWidth = (col) => {
  const widths = ['15%', '25%', '20%', '20%', '15%', '15%']
  return widths[(col - 1) % widths.length]
}
</script>

<style scoped>
.table-skeleton {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 0.75rem;
  overflow: hidden;
}

.table-header-skeleton {
  display: flex;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: rgba(15, 23, 42, 0.5);
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.table-row-skeleton {
  display: flex;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.05);
}

.table-row-skeleton:last-child {
  border-bottom: none;
}

.skeleton-cell {
  height: 1rem;
  border-radius: 0.25rem;
  background: linear-gradient(90deg, rgba(148, 163, 184, 0.1) 0%, rgba(148, 163, 184, 0.2) 50%, rgba(148, 163, 184, 0.1) 100%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.table-header-skeleton .skeleton-cell {
  height: 0.875rem;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

@media (max-width: 768px) {
  .table-skeleton {
    overflow-x: auto;
  }
  
  .table-header-skeleton,
  .table-row-skeleton {
    min-width: 600px;
  }
}
</style>

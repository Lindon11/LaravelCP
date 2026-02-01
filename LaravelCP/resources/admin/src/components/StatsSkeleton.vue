<template>
  <div class="stats-skeleton">
    <div class="stat-card-skeleton" v-for="i in 4" :key="i">
      <div class="skeleton-icon"></div>
      <div class="skeleton-content">
        <div class="skeleton-label"></div>
        <div class="skeleton-value"></div>
        <div class="skeleton-change"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.stats-skeleton {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card-skeleton {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.1);
  border-radius: 1rem;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
}

.skeleton-icon {
  width: 60px;
  height: 60px;
  border-radius: 0.75rem;
  background: linear-gradient(90deg, rgba(148, 163, 184, 0.1) 0%, rgba(148, 163, 184, 0.2) 50%, rgba(148, 163, 184, 0.1) 100%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  flex-shrink: 0;
}

.skeleton-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.skeleton-label {
  height: 0.875rem;
  width: 60%;
  border-radius: 0.25rem;
  background: linear-gradient(90deg, rgba(148, 163, 184, 0.1) 0%, rgba(148, 163, 184, 0.2) 50%, rgba(148, 163, 184, 0.1) 100%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-value {
  height: 2rem;
  width: 80%;
  border-radius: 0.25rem;
  background: linear-gradient(90deg, rgba(148, 163, 184, 0.1) 0%, rgba(148, 163, 184, 0.2) 50%, rgba(148, 163, 184, 0.1) 100%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-change {
  height: 0.875rem;
  width: 40%;
  border-radius: 0.25rem;
  background: linear-gradient(90deg, rgba(148, 163, 184, 0.1) 0%, rgba(148, 163, 184, 0.2) 50%, rgba(148, 163, 184, 0.1) 100%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

@media (max-width: 768px) {
  .stats-skeleton {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .skeleton-icon {
    width: 50px;
    height: 50px;
  }
}
</style>

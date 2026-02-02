<template>
  <div class="skeleton" :class="[variant, { 'animate': animate }]" :style="customStyle">
    <slot></slot>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'text', // text, circle, rect, card
    validator: (v) => ['text', 'circle', 'rect', 'card', 'stat'].includes(v)
  },
  width: {
    type: String,
    default: null
  },
  height: {
    type: String,
    default: null
  },
  animate: {
    type: Boolean,
    default: true
  }
})

const customStyle = computed(() => ({
  width: props.width,
  height: props.height
}))
</script>

<style scoped>
.skeleton {
  background: linear-gradient(
    90deg,
    rgba(148, 163, 184, 0.1) 0%,
    rgba(148, 163, 184, 0.2) 50%,
    rgba(148, 163, 184, 0.1) 100%
  );
  background-size: 200% 100%;
  border-radius: 0.25rem;
}

.skeleton.animate {
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Text skeleton */
.skeleton.text {
  height: 1rem;
  width: 100%;
  margin: 0.25rem 0;
}

/* Circle skeleton (avatar) */
.skeleton.circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
}

/* Rectangle skeleton */
.skeleton.rect {
  width: 100%;
  height: 100px;
  border-radius: 0.5rem;
}

/* Card skeleton */
.skeleton.card {
  width: 100%;
  height: 150px;
  border-radius: 1rem;
  border: 1px solid rgba(148, 163, 184, 0.1);
}

/* Stat card skeleton */
.skeleton.stat {
  width: 100%;
  height: 120px;
  border-radius: 1rem;
  border: 1px solid rgba(148, 163, 184, 0.1);
}
</style>

<template>
  <RouterView />
  <ToastContainer />
  <ConfirmModal />
</template>

<script setup>
import { RouterView } from 'vue-router'
import ToastContainer from '@/components/ToastContainer.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'
</script>

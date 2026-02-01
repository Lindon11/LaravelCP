<template>
  <teleport to="body">
    <transition name="modal">
      <div v-if="isOpen" class="confirm-overlay" @click.self="config.onCancel">
        <div :class="['confirm-modal', `confirm-${config.type}`]">
          <div class="confirm-icon">
            <span v-if="config.type === 'danger'">🗑️</span>
            <span v-else-if="config.type === 'warning'">⚠️</span>
            <span v-else>ℹ️</span>
          </div>
          
          <h3 class="confirm-title">{{ config.title }}</h3>
          <p class="confirm-message">{{ config.message }}</p>
          
          <div class="confirm-actions">
            <button class="btn btn-cancel" @click="config.onCancel">
              {{ config.cancelText }}
            </button>
            <button :class="['btn', `btn-${config.type}`]" @click="config.onConfirm">
              {{ config.confirmText }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { useConfirm } from '@/composables/useConfirm'

const { isOpen, config } = useConfirm()
</script>

<style scoped>
.confirm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 1rem;
}

.confirm-modal {
  background: rgba(30, 41, 59, 0.98);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 1rem;
  padding: 2rem;
  max-width: 400px;
  width: 100%;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.confirm-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.confirm-title {
  color: #ffffff;
  font-size: 1.5rem;
  margin: 0 0 0.75rem 0;
}

.confirm-message {
  color: #94a3b8;
  margin: 0 0 1.5rem 0;
  line-height: 1.6;
}

.confirm-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  min-width: 100px;
}

.btn-cancel {
  background: rgba(148, 163, 184, 0.1);
  color: #94a3b8;
  border: 1px solid rgba(148, 163, 184, 0.2);
}

.btn-cancel:hover {
  background: rgba(148, 163, 184, 0.2);
  color: #ffffff;
}

.btn-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: #ffffff;
}

.btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

.btn-warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: #ffffff;
}

.btn-warning:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
}

.btn-info {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: #ffffff;
}

.btn-info:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}

/* Modal animation */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-active .confirm-modal,
.modal-leave-active .confirm-modal {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .confirm-modal,
.modal-leave-to .confirm-modal {
  transform: scale(0.9);
  opacity: 0;
}

/* Mobile */
@media (max-width: 480px) {
  .confirm-modal {
    padding: 1.5rem;
  }
  
  .confirm-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
  }
}
</style>

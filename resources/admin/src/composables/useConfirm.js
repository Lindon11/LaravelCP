import { ref } from 'vue'

const isOpen = ref(false)
const config = ref({
  title: '',
  message: '',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  type: 'danger', // danger, warning, info
  onConfirm: null,
  onCancel: null
})

export function useConfirm() {
  const show = (options) => {
    return new Promise((resolve) => {
      config.value = {
        title: options.title || 'Confirm Action',
        message: options.message || 'Are you sure?',
        confirmText: options.confirmText || 'Confirm',
        cancelText: options.cancelText || 'Cancel',
        type: options.type || 'danger',
        onConfirm: () => {
          isOpen.value = false
          resolve(true)
        },
        onCancel: () => {
          isOpen.value = false
          resolve(false)
        }
      }
      isOpen.value = true
    })
  }
  
  const confirm = (message, title) => {
    return show({ message, title, type: 'danger' })
  }
  
  const warning = (message, title) => {
    return show({ message, title, type: 'warning' })
  }
  
  const info = (message, title) => {
    return show({ message, title, type: 'info' })
  }
  
  return {
    isOpen,
    config,
    show,
    confirm,
    warning,
    info
  }
}

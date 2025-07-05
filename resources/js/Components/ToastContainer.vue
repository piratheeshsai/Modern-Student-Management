<template>
  <div class="toast-container">
    <div v-for="toast in toasts" :key="toast.id" :class="[
      'toast-item',
      toast.show ? 'toast-show' : '',
      toast.type === 'success' ? 'toast-success' : 'toast-error',
    ]">
      <div class="toast-content">
        <div class="toast-icon">
          <i v-if="toast.type === 'success'" class="fas fa-check-circle"></i>
          <i v-else class="fas fa-exclamation-circle"></i>
        </div>
        <span class="toast-message">{{ toast.message }}</span>
        <button class="toast-close" @click="removeToast(toast.id)" aria-label="Close">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue'

interface Toast {
  id: number
  message: string
  type: 'success' | 'error'
  show: boolean
}

const props = defineProps<{
  toasts: Toast[]
  removeToast: (id: number) => void
}>()
</script>

<style scoped>
/* (Copy your toast styles here) */
</style>

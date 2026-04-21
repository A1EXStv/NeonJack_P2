<template>
  <button
    :class="buttonClasses"
    :type="type"
    :disabled="disabled"
    @click="$emit('click')"
  >
  {{ label }}
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: { type: String, default: 'button' },
  label: { type: String, default: 'Text' },

  variant: { type: String, default: 'primary' },
  classes: { type: String, default: '' },
  disabled: { type: Boolean, default: false }
})

const buttonClasses = computed(() => {
  const base = 'font-bold text-white w-full text-center transition-colors duration-200'
  const variants = {
    primary: 'bg-gradient-to-r from-[#9C5CCB] via-[#818AC8] to-[#3BC3DB] rounded-full px-6 py-3 uppercase',
    secondary: 'bg-gray-500 rounded px-4 py-2 hover:bg-gray-600',
    success: 'bg-green-500 rounded px-4 py-2 hover:bg-green-600',
    danger: 'bg-red-500 rounded px-4 py-2 hover:bg-red-600'
  }
  const disabledClass = props.disabled ? 'opacity-50 cursor-not-allowed' : ''
  return [base, variants[props.variant] || '', props.classes, disabledClass].join(' ')
})
</script>
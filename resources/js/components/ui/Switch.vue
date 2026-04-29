<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits(['update:modelValue'])

const toggle = () => {
  emit('update:modelValue', !props.modelValue)
}

const isOn = computed(() => props.modelValue)
</script>

<template>
  <button
    @click="toggle"
    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
    :class="isOn ? 'bg-red-600' : 'bg-gray-300 dark:bg-gray-600'"
  >
    <span
      class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
      :class="isOn ? 'translate-x-6' : 'translate-x-1'"
    />
  </button>
</template>

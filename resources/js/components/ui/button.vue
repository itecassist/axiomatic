<script setup lang="ts">
import { cn } from "@/lib/utils"

defineProps<{
  variant?: 'default' | 'outline'
}>()
</script>

<template>
  <button
    :class="cn(
      'inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition cursor-pointer',
      variant === 'outline'
        ? 'border border-gray-300 dark:border-gray-800'
        : 'bg-red-600 text-white hover:bg-red-700'
    )"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
import { cn } from "@/lib/utils"

defineProps<{
  variant?: 'default' | 'outline'
}>()
</script>

<template>
  <button
        :class="cn(
        'btn-base',
        variant === 'outline' ? 'btn-outline' : 'btn-default'
        )"
    >
    <slot /> 
</button>
</template>

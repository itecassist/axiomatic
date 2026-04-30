<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { PaginationLink } from '@/types/inertia'

defineProps<{
  links: PaginationLink[]
  from: number | null
  to: number | null
  total: number
}>()
</script>

<template>
  <div class="flex items-center justify-between mt-4 text-sm text-gray-600 dark:text-gray-400">
    <div>
      <span v-if="from && to">
        Showing {{ from }}–{{ to }} of {{ total }}
      </span>
      <span v-else>No results</span>
    </div>

    <div class="flex gap-1">
      <template v-for="link in links" :key="link.label">
        <Link
          v-if="link.url"
          :href="link.url"
          preserve-scroll
          preserve-state
          class="px-2.5 py-1 rounded border text-xs"
          :class="link.active
            ? 'bg-blue-600 text-white border-blue-600 dark:bg-blue-500 dark:border-blue-500'
            : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300'"
          v-html="link.label"
        />
        <span
          v-else
          class="px-2.5 py-1 rounded border border-gray-200 dark:border-gray-700 text-xs opacity-40"
          v-html="link.label"
        />
      </template>
    </div>
  </div>
</template>

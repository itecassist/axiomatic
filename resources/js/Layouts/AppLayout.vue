<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, onUnmounted } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth.user)
const flash = computed(() => page.props.flash)

onUnmounted(() => {
    setTimeout(() => {
        if (flash.value.success) {
            page.props.flash.success = null
        }
    }, 3000)
})
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                <Link :href="route('commission-notes.index')" class="font-semibold text-gray-800">Commission Notes</Link>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">{{ user?.name }}</span>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-sm text-gray-500 hover:text-gray-800"
                    >Logout</Link>
                </div>
            </div>
        </nav>

        <div v-if="flash.success" class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
            <div class="bg-green-100 border border-green-300 text-green-800 rounded px-4 py-2 text-sm">
                {{ flash.success }}
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>
    </div>
</template>

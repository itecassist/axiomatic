<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { route as ziggyRoute } from 'ziggy-js'
import { Company } from '@/types/models'

const props = defineProps<{
    companies: Company[]
    preselectedCompany: number | null
}>()

const page = usePage()
function route(name: string, params?: any, absolute = true) {
    return ziggyRoute(name, params, absolute, page.props.ziggy)
}

const form = useForm({
    company_id: props.preselectedCompany ?? '',
    name: '',
})

function submit() {
    form.post(route('branches.store'))
}
</script>

<template>
    <AppLayout>
        <div class="max-w-xl">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('branches.index')" class="text-blue-600 hover:underline dark:text-blue-400 dark:hover:text-blue-300 text-sm">&larr; Back</Link>
                <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">New Branch</h1>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                    <select
                        v-model="form.company_id"
                        required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Select company</option>
                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <p v-if="form.errors.company_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.company_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        maxlength="255"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="form.errors.name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 disabled:opacity-50"
                    >
                        Create Branch
                    </button>
                    <Link :href="route('branches.index')" class="px-4 py-2 rounded text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

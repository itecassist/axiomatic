<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { route as ziggyRoute } from 'ziggy-js'
import { Company } from '@/types/models'

defineProps<{
    companies: (Company & {
        branches: { id: number; name: string }[];
        branches_count: number })[]
    can: { manageCompanies: boolean }
}>()

const page = usePage()
function route(name: string, params?: any, absolute = true) {
    return ziggyRoute(name, params, absolute, page.props.ziggy)
}

function destroy(id: number) {
    if (confirm('Delete this company? All branches and their data will also be removed.')) {
        router.delete(route('companies.destroy', id))
    }
}
</script>

<template>
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">Companies</h1>
            <Link
                v-if="can.manageCompanies"
                :href="route('companies.create')"
                class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
            >
                New Company
            </Link>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Name</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Branches</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-if="companies.length === 0">
                        <td colspan="3" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500">No companies found.</td>
                    </tr>
                    <tr v-for="company in companies" :key="company.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ company.name }}</td>
                        <td class="px-4 py-3">
                            <span
                                v-for="branch in company.branches"
                                :key="branch.id"
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mr-1 mb-1"
                            >{{ branch.name }}</span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <Link
                                :href="route('branches.index', { company_id: company.id })"
                                class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 text-xs"
                            >
                                Branches
                            </Link>
                            <template v-if="can.manageCompanies">
                                <Link
                                    :href="route('companies.edit', company.id)"
                                    class="text-blue-600 hover:underline dark:text-blue-400 dark:hover:text-blue-300 text-xs"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="text-red-600 hover:underline dark:text-red-400 dark:hover:text-red-300 text-xs"
                                    @click="destroy(company.id)"
                                >
                                    Delete
                                </button>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

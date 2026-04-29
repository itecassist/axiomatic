<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { watch } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'
import { Company, Branch } from '@/types/models'
import { ref } from 'vue'

const props = defineProps<{
    branches: (Branch & { employees_count: number; company: Company })[]
    companies: Company[]
    filters: { company_id?: string | number }
    can: { manageBranches: boolean }
}>()

const page = usePage()
function route(name: string, params?: any, absolute = true) {
    return ziggyRoute(name, params, absolute, page.props.ziggy)
}

const selectedCompany = ref<number | null>(
    props.filters.company_id ? Number(props.filters.company_id) : null
)

watch(selectedCompany, (val) => {
    router.get(route('branches.index'), { company_id: val || undefined }, { preserveState: true, replace: true })
})

function destroy(id: number) {
    if (confirm('Delete this branch? All employees and their data will also be removed.')) {
        router.delete(route('branches.destroy', id))
    }
}
</script>

<template>
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Branches</h1>
            <Link
                v-if="can.manageBranches"
                :href="route('branches.create', selectedCompany ? { company_id: selectedCompany } : undefined)"
                class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700"
            >
                New Branch
            </Link>
        </div>

        <div class="flex gap-3 mb-6">
            <select v-model="selectedCompany" class="border rounded px-3 py-1.5 text-sm bg-white">
                <option :value="null">All companies</option>
                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left text-gray-600">Company</th>
                        <th class="px-4 py-3 text-left text-gray-600">Employees</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-if="branches.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">No branches found.</td>
                    </tr>
                    <tr v-for="branch in branches" :key="branch.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ branch.name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ branch.company?.name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ branch.employees_count }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <template v-if="can.manageBranches">
                                <Link
                                    :href="route('branches.edit', branch.id)"
                                    class="text-blue-600 hover:underline text-xs"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="text-red-600 hover:underline text-xs"
                                    @click="destroy(branch.id)"
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

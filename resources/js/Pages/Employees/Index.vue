<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'
import { Company, Branch, Employee } from '@/types/models'

const props = defineProps<{
    employees: {
        data: (Employee & { branch: Branch & { company: Company } })[]
        links: { url: string | null; label: string; active: boolean }[]
        from: number | null
        to: number | null
        total: number
    }
    companies: Company[]
    branches: Branch[]
    filters: { company_id?: string | number; branch_id?: string | number }
    can: { manageEmployees: boolean }
}>()

const page = usePage()
function route(name: string, params?: any, absolute = true) {
    return ziggyRoute(name, params, absolute, page.props.ziggy)
}

const selectedCompany = ref<number | null>(
    props.filters.company_id ? Number(props.filters.company_id) : null
)
const selectedBranch = ref<number | null>(
    props.filters.branch_id ? Number(props.filters.branch_id) : null
)

const filteredBranches = computed(() =>
    selectedCompany.value
        ? props.branches.filter(b => b.company_id === selectedCompany.value)
        : props.branches
)

watch(selectedCompany, () => {
    selectedBranch.value = null
    applyFilter()
})
watch(selectedBranch, applyFilter)

function applyFilter() {
    router.get(route('employees.index'), {
        company_id: selectedCompany.value || undefined,
        branch_id: selectedBranch.value || undefined,
    }, { preserveState: true, replace: true })
}

function destroy(id: number) {
    if (confirm('Delete this employee? All their commission notes will also be removed.')) {
        router.delete(route('employees.destroy', id))
    }
}
</script>

<template>
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Employees</h1>
            <Link
                v-if="can.manageEmployees"
                :href="route('employees.create', selectedBranch ? { branch_id: selectedBranch } : undefined)"
                class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700"
            >
                New Employee
            </Link>
        </div>

        <div class="flex gap-3 mb-6">
            <select v-model="selectedCompany" class="border rounded px-3 py-1.5 text-sm bg-white">
                <option :value="null">All companies</option>
                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <select v-model="selectedBranch" class="border rounded px-3 py-1.5 text-sm bg-white">
                <option :value="null">All branches</option>
                <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600">Number</th>
                        <th class="px-4 py-3 text-left text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left text-gray-600">Branch</th>
                        <th class="px-4 py-3 text-left text-gray-600">Company</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-if="employees.data.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">No employees found.</td>
                    </tr>
                    <tr v-for="emp in employees.data" :key="emp.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-gray-600">{{ emp.employee_number }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">
                            <div class="flex items-center gap-2">
                                <img
                                    v-if="emp.avatar"
                                    :src="emp.avatar"
                                    :alt="emp.employee_display"
                                    class="w-7 h-7 rounded-full object-cover"
                                />
                                {{ emp.employee_display }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ emp.branch?.name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ emp.branch?.company?.name }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <template v-if="can.manageEmployees">
                                <Link
                                    :href="route('employees.edit', emp.id)"
                                    class="text-blue-600 hover:underline text-xs"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="text-red-600 hover:underline text-xs"
                                    @click="destroy(emp.id)"
                                >
                                    Delete
                                </button>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-if="employees.links.length > 3"
                class="flex items-center justify-between gap-4 border-t border-gray-200 px-4 py-3"
            >
                <p class="text-sm text-gray-600">
                    Showing {{ employees.from ?? 0 }} to {{ employees.to ?? 0 }} of {{ employees.total }} employees
                </p>

                <div class="flex items-center gap-1">
                    <Link
                        v-for="link in employees.links"
                        :key="`${link.label}-${link.url ?? 'null'}`"
                        :href="link.url || ''"
                        :disabled="!link.url"
                        class="rounded px-3 py-1.5 text-sm transition"
                        :class="[
                            link.active
                                ? 'bg-blue-600 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-100',
                            !link.url ? 'pointer-events-none opacity-50' : '',
                        ]"
                    >
                        <span v-html="link.label" />
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

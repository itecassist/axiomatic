<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'
import type { Branch, CommissionNote, Company, Employee, User } from '@/types/models'

type EmployeeShow = Employee & {
    branch?: (Branch & { company?: Company }) | null
    commission_notes?: CommissionNoteWithRelations[]
}

type CommissionNoteWithRelations = CommissionNote & {
    date?: string
    created_at?: string
    author?: User
    company?: Company
    branch?: Branch
}

const props = defineProps<{
    employee: EmployeeShow
}>()

const page = usePage()
const activeTab = ref<'info' | 'commission' | 'audit'>('info')

function route(name: string, params?: any, absolute = true) {
    return ziggyRoute(name, params, absolute, page.props.ziggy)
}

const tabs = [
    { key: 'info', label: 'Info' },
    { key: 'commission', label: 'Commission Notes' },
    { key: 'audit', label: 'Audit Trail' },
] as const

const commissionNotes = computed(() => props.employee.commission_notes ?? [])

function formatAmount(amount: string | number) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(amount) || 0)
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('employees.index')" class="back-link">&larr; Back</Link>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                        {{ employee.employee_display ?? `${employee.first_name} ${employee.last_name}` }}
                    </h1>
                </div>
                <Link :href="route('employees.edit', employee.id)" class="btn btn-outline">Edit</Link>
            </div>

            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex flex-wrap gap-2">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="rounded-t-md px-4 py-2 text-sm font-medium transition"
                        :class="activeTab === tab.key
                            ? 'bg-white text-red-700 border border-gray-200 border-b-white dark:bg-gray-900 dark:text-red-400 dark:border-gray-700'
                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <div class="rounded-lg bg-white dark:bg-gray-800 shadow p-6">
                <div v-if="activeTab === 'info'" class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Employee Number</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ employee.employee_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">First Name</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ employee.first_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Last Name</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ employee.last_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Branch</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ employee.branch?.name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Company</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ employee.branch?.company?.name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Avatar</p>
                        <div class="mt-1">
                            <img
                                v-if="employee.avatar"
                                :src="employee.avatar"
                                :alt="employee.employee_display"
                                class="h-12 w-12 rounded-full object-cover border border-gray-200 dark:border-gray-600"
                            />
                            <p v-else class="font-medium text-gray-900 dark:text-gray-100">-</p>
                        </div>
                    </div>
                </div>

                <div v-else-if="activeTab === 'commission'" class="space-y-4">
                    <div v-if="commissionNotes.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                        No commission notes found for this employee.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/30">
                                <tr>
                                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Date</th>
                                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Description</th>
                                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Branch</th>
                                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Company</th>
                                    <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Author</th>
                                    <th class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="note in commissionNotes" :key="note.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ note.date ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ note.description }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ note.branch?.name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ note.company?.name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ note.author?.name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">{{ formatAmount(note.amount) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-900/30">
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Total:</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-gray-100">
                                        {{ formatAmount(commissionNotes.reduce((sum, note) => sum + Number(note.amount || 0), 0)) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                    Audit trail will be implemented later.
                </div>
            </div>
        </div>
    </AppLayout>
</template>

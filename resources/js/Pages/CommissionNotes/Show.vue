<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'
import type { CommissionNote, Company, Branch, Employee } from '@/types/models'

const props = defineProps<{
    note: CommissionNote
    company: Company
    branch: Branch
    employee: Employee
}>()

const page = usePage()

function route(name: string, params: any = undefined, absolute: boolean = true) {
    return ziggyRoute(name, params, absolute, page.props.ziggy)
}

const amountFormatter = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
})
function formatAmount(amount: number) {
    return amountFormatter.format(Number(amount) || 0)
}
</script>

<template>
    <AppLayout>
        <div class="max-w-xl">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('commission-notes.index')" class="text-blue-600 hover:underline text-sm">&larr; Back</Link>
                <h1 class="text-xl font-bold text-gray-800">Show Commission Note # {{ props.note.id }}</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <div class="grid grid-cols-2 gap-2">
                    <div class="block text-sm font-medium text-gray-700 mb-1">Company</div>
                    <div class="text-gray-900">{{ props.note.company.name }}</div>
                    <hr class="col-span-2 my-2 text-gray-200" />

                    <div class="block text-sm font-medium text-gray-700 mb-1">Branch</div>
                    <div class="text-gray-900">{{ props.note.branch.name }}</div>
                    <hr class="col-span-2 my-2 text-gray-200" />

                    <div class="block text-sm font-medium text-gray-700 mb-1">Employee Number</div>
                    <div class="text-gray-900">{{ props.note.employee.employee_number }}</div>
                    <hr class="col-span-2 my-2 text-gray-200" />

                    <div class="block text-sm font-medium text-gray-700 mb-1">Employee</div>
                    <div class="text-gray-900">{{ props.note.employee.first_name }} {{ props.note.employee.last_name }}</div>
                    <hr class="col-span-2 my-2 text-gray-200" />

                    <div class="block text-sm font-medium text-gray-700 mb-1">Description</div>
                    <div class="text-gray-900">{{ props.note.description }}</div>
                    <hr class="col-span-2 my-2 text-gray-200" />

                    <div class="block text-sm font-medium text-gray-700 mb-1">Amount</div>
                    <div class="text-gray-900">{{ formatAmount(props.note.amount) }}</div>
                </div>


            </div>
        </div>
    </AppLayout>
</template>

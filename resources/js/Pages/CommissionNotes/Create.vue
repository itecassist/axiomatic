<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'
import type { Company, Branch, Employee } from '@/types/models'

const props = defineProps<{
    companies: Company[]
    branches:  Branch[]
    employees: Employee[]
}>()

const form = useForm({
    company_id:  '',
    branch_id:   '',
    employee_id: '',
    description: '',
    amount:      '',
})

const selectedCompany = ref('')
const selectedBranch  = ref('')
const page = usePage()

function route(name: string, params: any = undefined, absolute: boolean = true) {
    return ziggyRoute(name, params, absolute, page.props.ziggy)
}

const availableBranches = computed(() =>
    selectedCompany.value
        ? props.branches.filter(b => b.company_id == selectedCompany.value)
        : []
)

const availableEmployees = computed(() =>
    selectedBranch.value
        ? props.employees.filter(e => e.branch_id == selectedBranch.value)
        : []
)

function onCompanyChange() {
    selectedBranch.value  = ''
    form.company_id  = selectedCompany.value
    form.branch_id   = ''
    form.employee_id = ''
}

function onBranchChange() {
    form.branch_id   = selectedBranch.value
    form.employee_id = ''
}

function submit() {
    form.post(route('commission-notes.store'))
}
</script>

<template>
    <AppLayout>
        <div class="max-w-xl">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('commission-notes.index')" class="text-blue-600 hover:underline text-sm">&larr; Back</Link>
                <h1 class="text-xl font-bold text-gray-800">New Commission Note</h1>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                    <select
                        v-model="selectedCompany"
                        @change="onCompanyChange"
                        required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                    >
                        <option value="">Select company</option>
                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <p v-if="form.errors.company_id" class="text-red-600 text-xs mt-1">{{ form.errors.company_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select
                        v-model="selectedBranch"
                        @change="onBranchChange"
                        :disabled="!selectedCompany"
                        required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm disabled:bg-gray-50"
                    >
                        <option value="">Select branch</option>
                        <option v-for="b in availableBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                    <p v-if="form.errors.branch_id" class="text-red-600 text-xs mt-1">{{ form.errors.branch_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <select
                        v-model="form.employee_id"
                        :disabled="!selectedBranch"
                        required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm disabled:bg-gray-50"
                    >
                        <option value="">Select employee</option>
                        <option v-for="e in availableEmployees" :key="e.id" :value="e.id">{{ e.employee_number }} - {{ e.employee_display }}</option>
                    </select>
                    <p v-if="form.errors.employee_id" class="text-red-600 text-xs mt-1">{{ form.errors.employee_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        required
                        maxlength="1000"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                    />
                    <p v-if="form.errors.description" class="text-red-600 text-xs mt-1">{{ form.errors.description }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <input
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                    />
                    <p v-if="form.errors.amount" class="text-red-600 text-xs mt-1">{{ form.errors.amount }}</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
                    >Save Note</button>
                    <Link :href="route('commission-notes.index')" class="px-4 py-2 rounded text-sm text-gray-600 hover:bg-gray-100">Cancel</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'
import { Company, Branch, Employee } from '@/types/models'

const props = defineProps<{
    employee: Employee
    companies: Company[]
    branches: Branch[]
}>()

const page = usePage()
function route(name: string, params?: any, absolute = true) {
    return ziggyRoute(name, params, absolute, page.props.ziggy)
}

const currentBranch = props.branches.find(b => b.id === props.employee.branch_id)
const selectedCompany = ref<string | number>(currentBranch?.company_id ?? '')

const availableBranches = computed(() =>
    selectedCompany.value
        ? props.branches.filter(b => b.company_id == selectedCompany.value)
        : props.branches
)

const form = useForm({
    branch_id:       props.employee.branch_id,
    first_name:      props.employee.first_name,
    last_name:       props.employee.last_name,
    employee_number: props.employee.employee_number,
    avatar:          props.employee.avatar ?? '',
})

function onCompanyChange() {
    form.branch_id = ''
}

function submit() {
    form.put(route('employees.update', props.employee.id), { preserveScroll: true })
}
</script>

<template>
    <AppLayout>
        <div class="max-w-xl">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('employees.index')" class="text-blue-600 hover:underline dark:text-blue-400 dark:hover:text-blue-300 text-sm">&larr; Back</Link>
                <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">Edit Employee</h1>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                    <select
                        v-model="selectedCompany"
                        @change="onCompanyChange"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Select company</option>
                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
                    <select
                        v-model="form.branch_id"
                        required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Select branch</option>
                        <option v-for="b in availableBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                    <p v-if="form.errors.branch_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.branch_id }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                        <input
                            v-model="form.first_name"
                            type="text"
                            required
                            maxlength="255"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p v-if="form.errors.first_name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.first_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                        <input
                            v-model="form.last_name"
                            type="text"
                            required
                            maxlength="255"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p v-if="form.errors.last_name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.last_name }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employee Number</label>
                    <input
                        v-model="form.employee_number"
                        type="text"
                        required
                        maxlength="255"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="form.errors.employee_number" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.employee_number }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Avatar URL <span class="text-gray-400 dark:text-gray-500">(optional)</span></label>
                    <div class="flex items-center gap-3">
                        <img
                            v-if="form.avatar"
                            :src="form.avatar"
                            :alt="employee.employee_display"
                            class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-600"
                        />
                        <input
                            v-model="form.avatar"
                            type="url"
                            maxlength="2048"
                            placeholder="https://..."
                            class="flex-1 border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <p v-if="form.errors.avatar" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ form.errors.avatar }}</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 disabled:opacity-50"
                    >
                        Save Changes
                    </button>
                    <Link :href="route('employees.index')" class="px-4 py-2 rounded text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

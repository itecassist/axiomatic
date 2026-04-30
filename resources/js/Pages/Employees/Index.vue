<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
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
    filters: { company_id?: string | number; branch_id?: string | number; search?: string }
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
const searchTerm = ref(props.filters.search ?? '')
const filtersOpen = ref(false)
const exportDropdownOpen = ref(false)
let searchDebounce: ReturnType<typeof setTimeout> | undefined

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
watch(searchTerm, () => {
    clearTimeout(searchDebounce)
    searchDebounce = setTimeout(() => {
        applyFilter()
    }, 300)
})

function applyFilter() {
    router.get(route('employees.index'), {
        company_id: selectedCompany.value || undefined,
        branch_id: selectedBranch.value || undefined,
        search: searchTerm.value.trim() || undefined,
    }, { preserveState: true, replace: true })
}

function clearFilters() {
    selectedCompany.value = null
    selectedBranch.value = null
    searchTerm.value = ''
    filtersOpen.value = false
    router.get(route('employees.index'), {}, { preserveState: true, replace: true })
}

function destroy(id: number) {
    if (confirm('Delete this employee? All their commission notes will also be removed.')) {
        router.delete(route('employees.destroy', id))
    }
}

function exportEmployees(format: 'csv' | 'excel' | 'pdf') {
    const exportUrl = new URL(route('employees.export'), window.location.origin)
    exportUrl.searchParams.append('format', format)
    if (selectedCompany.value) exportUrl.searchParams.append('company_id', selectedCompany.value.toString())
    if (selectedBranch.value) exportUrl.searchParams.append('branch_id', selectedBranch.value.toString())
    if (searchTerm.value.trim()) exportUrl.searchParams.append('search', searchTerm.value.trim())

    window.location.href = exportUrl.toString()
    exportDropdownOpen.value = false
}

// ── Create dialog ────────────────────────────────────────────────
const showCreateDialog = ref(false)
const createCompany = ref<string | number>('')
const createAvailableBranches = computed(() =>
    createCompany.value
        ? props.branches.filter(b => b.company_id == createCompany.value)
        : []
)
const createForm = useForm({
    branch_id: '' as string | number,
    first_name: '',
    last_name: '',
    employee_number: '',
    avatar: '',
})

function openCreate() {
    createCompany.value = ''
    createForm.reset()
    createForm.clearErrors()
    showCreateDialog.value = true
}

function submitCreate() {
    createForm.post(route('employees.store'), {
        onSuccess: () => { showCreateDialog.value = false },
    })
}

// ── Edit dialog ──────────────────────────────────────────────────
const showEditDialog = ref(false)
const editingEmployee = ref<(Employee & { branch?: { company_id?: number } }) | null>(null)
const editCompany = ref<string | number>('')
const editAvailableBranches = computed(() =>
    editCompany.value
        ? props.branches.filter(b => b.company_id == editCompany.value)
        : props.branches
)
const editForm = useForm({
    branch_id: '' as string | number,
    first_name: '',
    last_name: '',
    employee_number: '',
    avatar: '',
})

function openEdit(emp: Employee & { branch?: { company_id?: number } }) {
    editingEmployee.value = emp
    editCompany.value = emp.branch?.company_id ?? ''
    editForm.branch_id = emp.branch_id
    editForm.first_name = emp.first_name
    editForm.last_name = emp.last_name
    editForm.employee_number = emp.employee_number
    editForm.avatar = emp.avatar ?? ''
    editForm.clearErrors()
    showEditDialog.value = true
}

function submitEdit() {
    if (!editingEmployee.value) return
    editForm.put(route('employees.update', editingEmployee.value.id), {
        preserveScroll: true,
        onSuccess: () => { showEditDialog.value = false },
    })
}

</script>

<template>
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">Employees</h1>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
                        @click="exportDropdownOpen = !exportDropdownOpen"
                    >
                        Export
                        <svg class="ml-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div v-if="exportDropdownOpen" class="absolute right-0 z-10 mt-2 w-48 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg">
                        <button
                            type="button"
                            class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                            @click="exportEmployees('csv')"
                        >
                            CSV
                        </button>
                        <button
                            type="button"
                            class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                            @click="exportEmployees('excel')"
                        >
                            Excel
                        </button>
                        <button
                            type="button"
                            class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                            @click="exportEmployees('pdf')"
                        >
                            PDF
                        </button>
                    </div>
                </div>
                <button
                    v-if="can.manageEmployees"
                    type="button"
                    class="btn btn-default"
                    @click="openCreate"
                >
                    New Employee
                </button>
            </div>
        </div>

        <div class="mb-6 space-y-3">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <input
                    v-model="searchTerm"
                    type="search"
                    placeholder="Search by employee name or number"
                    class="w-full sm:max-w-sm border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                />

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
                    @click="filtersOpen = !filtersOpen"
                >
                    {{ filtersOpen ? 'Hide Filters' : 'Filters' }}
                </button>
            </div>

            <div v-if="filtersOpen" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                        <select v-model="selectedCompany" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                            <option :value="null">All companies</option>
                            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Branch</label>
                        <select v-model="selectedBranch" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                            <option :value="null">All branches</option>
                            <option v-for="b in filteredBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        class="rounded border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-700"
                        @click="clearFilters"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Number</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Name</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Branch</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Company</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-if="employees.data.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500">No employees found.</td>
                    </tr>
                    <tr v-for="emp in employees.data" :key="emp.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-mono text-gray-600 dark:text-gray-400">{{ emp.employee_number }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
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
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ emp.branch?.name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ emp.branch?.company?.name }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <Link
                                :href="route('employees.show', emp.id)"
                                class="text-gray-700 dark:text-gray-300 hover:underline text-xs"
                            >
                                View
                            </Link>
                            <template v-if="can.manageEmployees">
                                <button
                                    type="button"
                                    class="text-blue-600 dark:text-blue-400 hover:underline text-xs"
                                    @click="openEdit(emp)"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="text-red-600 dark:text-red-400 hover:underline text-xs"
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
                class="flex items-center justify-between gap-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3"
            >
                <p class="text-sm text-gray-600 dark:text-gray-400">
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
                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                            !link.url ? 'pointer-events-none opacity-50' : '',
                        ]"
                    >
                        <span v-html="link.label" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- ── Create Dialog ─────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showCreateDialog" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/50" @click="showCreateDialog = false" />
                <div class="relative z-10 w-full max-w-lg mx-4 rounded-lg bg-white dark:bg-gray-800 shadow-xl">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">New Employee</h2>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click="showCreateDialog = false">&times;</button>
                    </div>
                    <form @submit.prevent="submitCreate" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                            <select v-model="createCompany" @change="createForm.branch_id = ''" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select company</option>
                                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
                            <select v-model="createForm.branch_id" required :disabled="!createCompany" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                                <option value="">Select branch</option>
                                <option v-for="b in createAvailableBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                            <p v-if="createForm.errors.branch_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.branch_id }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                                <input v-model="createForm.first_name" type="text" required maxlength="255" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <p v-if="createForm.errors.first_name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.first_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                                <input v-model="createForm.last_name" type="text" required maxlength="255" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <p v-if="createForm.errors.last_name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.last_name }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employee Number</label>
                            <input v-model="createForm.employee_number" type="text" required maxlength="255" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <p v-if="createForm.errors.employee_number" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.employee_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Avatar URL <span class="text-gray-400">(optional)</span></label>
                            <input v-model="createForm.avatar" type="url" maxlength="2048" placeholder="https://..." class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <p v-if="createForm.errors.avatar" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.avatar }}</p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" class="btn btn-outline" @click="showCreateDialog = false">Cancel</button>
                            <button type="submit" :disabled="createForm.processing" class="btn btn-default">Create Employee</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- ── Edit Dialog ───────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showEditDialog" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/50" @click="showEditDialog = false" />
                <div class="relative z-10 w-full max-w-lg mx-4 rounded-lg bg-white dark:bg-gray-800 shadow-xl">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Edit Employee</h2>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click="showEditDialog = false">&times;</button>
                    </div>
                    <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                            <select v-model="editCompany" @change="editForm.branch_id = ''" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select company</option>
                                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
                            <select v-model="editForm.branch_id" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select branch</option>
                                <option v-for="b in editAvailableBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                            <p v-if="editForm.errors.branch_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.branch_id }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                                <input v-model="editForm.first_name" type="text" required maxlength="255" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <p v-if="editForm.errors.first_name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.first_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                                <input v-model="editForm.last_name" type="text" required maxlength="255" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <p v-if="editForm.errors.last_name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.last_name }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employee Number</label>
                            <input v-model="editForm.employee_number" type="text" required maxlength="255" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <p v-if="editForm.errors.employee_number" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.employee_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Avatar URL <span class="text-gray-400">(optional)</span></label>
                            <div class="flex items-center gap-3">
                                <img v-if="editForm.avatar" :src="editForm.avatar" :alt="editingEmployee?.employee_display" class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" />
                                <input v-model="editForm.avatar" type="url" maxlength="2048" placeholder="https://..." class="flex-1 border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <p v-if="editForm.errors.avatar" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.avatar }}</p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" class="btn btn-outline" @click="showEditDialog = false">Cancel</button>
                            <button type="submit" :disabled="editForm.processing" class="btn btn-default">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>

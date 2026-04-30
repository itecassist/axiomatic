<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'
import { Company, Branch } from '@/types/models'

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

// ── Create dialog ──────────────────────────────────────────────
const showCreateDialog = ref(false)
const createForm = useForm({ company_id: '' as string | number, name: '' })

function openCreate() {
    createForm.reset()
    createForm.clearErrors()
    if (selectedCompany.value) createForm.company_id = selectedCompany.value
    showCreateDialog.value = true
}

function submitCreate() {
    createForm.post(route('branches.store'), {
        onSuccess: () => { showCreateDialog.value = false },
    })
}

// ── Edit dialog ────────────────────────────────────────────────
const showEditDialog = ref(false)
const editingBranch = ref<{ id: number; name: string; company_id: number } | null>(null)
const editForm = useForm({ company_id: '' as string | number, name: '' })

function openEdit(branch: { id: number; name: string; company_id: number }) {
    editingBranch.value = branch
    editForm.company_id = branch.company_id
    editForm.name = branch.name
    editForm.clearErrors()
    showEditDialog.value = true
}

function submitEdit() {
    if (!editingBranch.value) return
    editForm.put(route('branches.update', editingBranch.value.id), {
        preserveScroll: true,
        onSuccess: () => { showEditDialog.value = false },
    })
}

</script>

<template>
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">Branches</h1>
            <button
                v-if="can.manageBranches"
                type="button"
                class="btn btn-default"
                @click="openCreate"
            >
                New Branch
            </button>
        </div>

        <div class="flex gap-3 mb-6">
            <select v-model="selectedCompany" class="border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                <option :value="null">All companies</option>
                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Name</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Company</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Employees</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-if="branches.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500">No branches found.</td>
                    </tr>
                    <tr v-for="branch in branches" :key="branch.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ branch.name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ branch.company?.name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ branch.employees_count }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <template v-if="can.manageBranches">
                                <button
                                    type="button"
                                    class="text-blue-600 dark:text-blue-400 hover:underline text-xs"
                                    @click="openEdit(branch)"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="text-red-600 dark:text-red-400 hover:underline text-xs"
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

        <!-- ── Create Dialog ──────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showCreateDialog" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/50" @click="showCreateDialog = false" />
                <div class="relative z-10 w-full max-w-md mx-4 rounded-lg bg-white dark:bg-gray-800 shadow-xl">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">New Branch</h2>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none" @click="showCreateDialog = false">&times;</button>
                    </div>
                    <form @submit.prevent="submitCreate" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                            <select v-model="createForm.company_id" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select company</option>
                                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="createForm.errors.company_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.company_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                            <input v-model="createForm.name" type="text" required maxlength="255" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <p v-if="createForm.errors.name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.name }}</p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" class="btn btn-outline" @click="showCreateDialog = false">Cancel</button>
                            <button type="submit" :disabled="createForm.processing" class="btn btn-default">Create Branch</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- ── Edit Dialog ────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showEditDialog" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/50" @click="showEditDialog = false" />
                <div class="relative z-10 w-full max-w-md mx-4 rounded-lg bg-white dark:bg-gray-800 shadow-xl">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Edit Branch</h2>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none" @click="showEditDialog = false">&times;</button>
                    </div>
                    <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                            <select v-model="editForm.company_id" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select company</option>
                                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="editForm.errors.company_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.company_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                            <input v-model="editForm.name" type="text" required maxlength="255" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <p v-if="editForm.errors.name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.name }}</p>
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

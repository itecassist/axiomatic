<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'
import { Company } from '@/types/models'

const props = defineProps<{
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

// ── Create dialog ──────────────────────────────────────────────
const showCreateDialog = ref(false)
const createForm = useForm({ name: '' })

function openCreate() {
    createForm.reset()
    createForm.clearErrors()
    showCreateDialog.value = true
}

function submitCreate() {
    createForm.post(route('companies.store'), {
        onSuccess: () => { showCreateDialog.value = false },
    })
}

// ── Edit dialog ────────────────────────────────────────────────
const showEditDialog = ref(false)
const editingCompany = ref<{ id: number; name: string } | null>(null)
const editForm = useForm({ name: '' })

function openEdit(company: { id: number; name: string }) {
    editingCompany.value = company
    editForm.name = company.name
    editForm.clearErrors()
    showEditDialog.value = true
}

function submitEdit() {
    if (!editingCompany.value) return
    editForm.put(route('companies.update', editingCompany.value.id), {
        preserveScroll: true,
        onSuccess: () => { showEditDialog.value = false },
    })
}
</script>

<template>
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">Companies</h1>
            <button
                v-if="can.manageCompanies"
                type="button"
                class="btn btn-default"
                @click="openCreate"
            >
                New Company
            </button>
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
                                <button
                                    type="button"
                                    class="text-blue-600 hover:underline dark:text-blue-400 dark:hover:text-blue-300 text-xs"
                                    @click="openEdit(company)"
                                >
                                    Edit
                                </button>
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

        <!-- ── Create Dialog ──────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showCreateDialog" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/50" @click="showCreateDialog = false" />
                <div class="relative z-10 w-full max-w-md mx-4 rounded-lg bg-white dark:bg-gray-800 shadow-xl">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">New Company</h2>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none" @click="showCreateDialog = false">&times;</button>
                    </div>
                    <form @submit.prevent="submitCreate" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                            <input v-model="createForm.name" type="text" required maxlength="255" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <p v-if="createForm.errors.name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.name }}</p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" class="btn btn-outline" @click="showCreateDialog = false">Cancel</button>
                            <button type="submit" :disabled="createForm.processing" class="btn btn-default">Create Company</button>
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
                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Edit Company</h2>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none" @click="showEditDialog = false">&times;</button>
                    </div>
                    <form @submit.prevent="submitEdit" class="p-6 space-y-4">
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

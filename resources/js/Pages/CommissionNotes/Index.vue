<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'
import { useFilterStore } from '@/stores/useFilterStore'
import { Company, Branch, CommissionNote, Employee } from '@/types/models'
import { Filters, Permissions, Paginated } from '@/types/inertia'

interface Props {
  notes: Paginated<CommissionNote>
  companies: Company[]
  branches: Branch[]
  employees: Employee[]
  filters: Filters
  can?: Permissions
}

const props = defineProps<Props>()

// ---- Pinia (meaningful UI state) ----
const store = useFilterStore()

// Initialize from backend filters
store.companyId = props.filters?.company_id ?? null
store.branchId = props.filters?.branch_id ?? null
const searchTerm = ref(props.filters?.search ?? '')
const showAdvancedFilters = ref(false)
const amountMin = ref<number | null>(props.filters?.amount_min ?? null)
const amountMax = ref<number | null>(props.filters?.amount_max ?? null)
const dateFrom = ref<string>(props.filters?.date_from ?? '')
const dateTo = ref<string>(props.filters?.date_to ?? '')

let filterDebounceTimer: ReturnType<typeof setTimeout> | null = null

const page = usePage()

function route(name: string, params?: any, absolute = true) {
  return ziggyRoute(name, params, absolute, page.props.ziggy)
}

// ---- Watchers ----
watch(() => store.companyId, () => {
  store.branchId = null
  scheduleApplyFilter()
})

watch(() => store.branchId, () => {
  scheduleApplyFilter()
})

watch(searchTerm, () => {
  scheduleApplyFilter()
})

onBeforeUnmount(() => {
  if (filterDebounceTimer) clearTimeout(filterDebounceTimer)
})

// ---- Filtering ----
function scheduleApplyFilter(delay = 400) {
  if (filterDebounceTimer) clearTimeout(filterDebounceTimer)
  filterDebounceTimer = setTimeout(() => {
    applyFilter()
  }, delay)
}

function applyFilter() {
  router.get(route('commission-notes.index'), {
    company_id: store.companyId || undefined,
    branch_id: store.branchId || undefined,
    search: searchTerm.value?.trim() || undefined,
    amount_min: amountMin.value ?? undefined,
    amount_max: amountMax.value ?? undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
  }, { preserveState: true, replace: true })
}

function resetAdvancedFilters() {
  amountMin.value = null
  amountMax.value = null
  dateFrom.value = ''
  dateTo.value = ''
  applyFilter()
}

// ---- Computed ----
const filteredBranches = computed(() => {
  return store.companyId
    ? props.branches.filter(b => b.company_id === store.companyId)
    : props.branches
})

// ---- Formatting ----
const amountFormatter = new Intl.NumberFormat('en-US', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
})

function formatAmount(amount: string | number) {
  return amountFormatter.format(Number(amount) || 0)
}

// ── Create dialog ──────────────────────────────────────────────
const showCreateDialog = ref(false)
const createSelectedCompany = ref('')
const createSelectedBranch = ref('')
const createForm = useForm({
  company_id: '',
  branch_id: '',
  employee_id: '',
  description: '',
  date: '',
  amount: '',
})

const createAvailableBranches = computed(() =>
  createSelectedCompany.value
    ? props.branches.filter(b => b.company_id == createSelectedCompany.value)
    : []
)

const createAvailableEmployees = computed(() =>
  createSelectedBranch.value
    ? props.employees.filter(e => e.branch_id == createSelectedBranch.value)
    : []
)

function openCreate() {
  createSelectedCompany.value = ''
  createSelectedBranch.value = ''
  createForm.reset()
  createForm.clearErrors()
  showCreateDialog.value = true
}

function onCreateCompanyChange() {
  createSelectedBranch.value = ''
  createForm.company_id = createSelectedCompany.value
  createForm.branch_id = ''
  createForm.employee_id = ''
}

function onCreateBranchChange() {
  createForm.branch_id = createSelectedBranch.value
  createForm.employee_id = ''
}

function submitCreate() {
  createForm.post(route('commission-notes.store'), {
    onSuccess: () => { showCreateDialog.value = false },
  })
}

// ── Edit dialog ────────────────────────────────────────────────
const showEditDialog = ref(false)
const editSelectedCompany = ref('')
const editSelectedBranch = ref('')
const editingNoteId = ref<number | null>(null)
const editForm = useForm({
  company_id: '' as any,
  branch_id: '' as any,
  employee_id: '' as any,
  description: '',
  date: '',
  amount: '' as any,
})

const editAvailableBranches = computed(() =>
  editSelectedCompany.value
    ? props.branches.filter(b => b.company_id == editSelectedCompany.value)
    : props.branches
)

const editAvailableEmployees = computed(() =>
  editSelectedBranch.value
    ? props.employees.filter(e => e.branch_id == editSelectedBranch.value)
    : props.employees
)

function openEdit(note: any) {
  editingNoteId.value = note.id
  editSelectedCompany.value = String(note.company_id ?? note.company?.id ?? '')
  editSelectedBranch.value = String(note.branch_id ?? note.branch?.id ?? '')
  editForm.company_id = editSelectedCompany.value
  editForm.branch_id = editSelectedBranch.value
  editForm.date = note.date ?? ''
  editForm.employee_id = note.employee_id ?? note.employee?.id ?? ''
  editForm.description = note.description
  editForm.amount = note.amount
  editForm.clearErrors()
  showEditDialog.value = true
}

function onEditCompanyChange() {
  editSelectedBranch.value = ''
  editForm.company_id = editSelectedCompany.value
  editForm.branch_id = ''
  editForm.employee_id = ''
}

function onEditBranchChange() {
  editForm.branch_id = editSelectedBranch.value
  editForm.employee_id = ''
}

function submitEdit() {
  if (!editingNoteId.value) return
  editForm.put(route('commission-notes.update', editingNoteId.value), {
    preserveScroll: true,
    onSuccess: () => { showEditDialog.value = false },
  })
}
</script>

<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">Commission Notes</h1>

      <button
        v-if="props.can?.manage"
        type="button"
        class="btn btn-default"
        @click="openCreate"
      >
        New Note
      </button>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 mb-3">
      <input
        v-model="searchTerm"
        type="text"
        placeholder="Search reference, description, or employee"
        class="w-full md:w-80 border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />

      <select v-model="store.companyId" class="border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option :value="null">All companies</option>
        <option v-for="c in companies" :key="c.id" :value="c.id">
          {{ c.name }}
        </option>
      </select>

      <select v-model="store.branchId" class="border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option :value="null">All branches</option>
        <option v-for="b in filteredBranches" :key="b.id" :value="b.id">
          {{ b.name }}
        </option>
      </select>

      <button type="button" class="btn btn-default" @click="showAdvancedFilters = !showAdvancedFilters">Filters</button>
    </div>

    <div v-if="showAdvancedFilters" class="mb-6 p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Amount Min</label>
          <input
            v-model.number="amountMin"
            type="number"
            step="0.01"
            min="0"
            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Amount Max</label>
          <input
            v-model.number="amountMax"
            type="number"
            step="0.01"
            min="0"
            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Date From</label>
          <input
            v-model="dateFrom"
            type="date"
            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Date To</label>
          <input
            v-model="dateTo"
            type="date"
            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>
      <div class="flex justify-end gap-2 mt-4">
        <button type="button" class="btn btn-outline" @click="resetAdvancedFilters">Clear</button>
        <button type="button" class="btn btn-default" @click="applyFilter">Apply Filters</button>
      </div>
    </div>

    <!-- Notes table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Reference</th>
            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Company / Branch</th>
            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Description</th>
            <th class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">Date</th>
            <th class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">Amount</th>
            <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Author</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="notes.data.length === 0">
            <td colspan="7" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500">
              No notes found.
            </td>
          </tr>

          <tr v-for="note in notes.data" :key="note.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-100">
            <td class="px-4 py-3 font-mono">
              {{ note.reference }}
            </td>

            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
              {{ note.company?.name }} / {{ note.branch?.name }}
            </td>

            <td class="px-4 py-3">
              {{ note.description }}
            </td>
            <td class="px-4 py-3">
              {{ note.date }}
            </td>

            <td class="px-4 py-3 text-right font-mono">
              {{ formatAmount(note.amount) }}
            </td>

            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
              {{ note.author?.name }}
            </td>

            <td class="px-4 py-3 text-right">
              <button
                v-if="props.can?.manage"
                type="button"
                class="text-blue-600 dark:text-blue-400 hover:underline mr-3 text-sm"
                @click="openEdit(note)"
              >
                Edit
              </button>

              <Link
                :href="route('commission-notes.show', note.id)"
                class="text-gray-600 dark:text-gray-400 hover:underline text-sm"
              >
                View
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination
      :links="notes.links"
      :from="notes.from"
      :to="notes.to"
      :total="notes.total"
    />

    <!-- ── Create Dialog ───────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showCreateDialog" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showCreateDialog = false" />
        <div class="relative z-10 w-full max-w-lg mx-4 rounded-lg bg-white dark:bg-gray-800 shadow-xl">
          <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">New Commission Note</h2>
            <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none" @click="showCreateDialog = false">&times;</button>
          </div>
          <form @submit.prevent="submitCreate" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                <input
                    type="date"
                    v-model="createForm.date"
                    required
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                >
                </input>
                <p v-if="createForm.errors.date" class="text-red-600 text-xs mt-1">{{ createForm.errors.date }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
              <select v-model="createSelectedCompany" @change="onCreateCompanyChange" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select company</option>
                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <p v-if="createForm.errors.company_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.company_id }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
              <select v-model="createSelectedBranch" @change="onCreateBranchChange" :disabled="!createSelectedCompany" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                <option value="">Select branch</option>
                <option v-for="b in createAvailableBranches" :key="b.id" :value="b.id">{{ b.name }}</option>
              </select>
              <p v-if="createForm.errors.branch_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.branch_id }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employee</label>
              <select v-model="createForm.employee_id" :disabled="!createSelectedBranch" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                <option value="">Select employee</option>
                <option v-for="e in createAvailableEmployees" :key="e.id" :value="e.id">{{ e.employee_number }} - {{ e.employee_display }}</option>
              </select>
              <p v-if="createForm.errors.employee_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.employee_id }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
              <textarea v-model="createForm.description" rows="3" required maxlength="1000" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              <p v-if="createForm.errors.description" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.description }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
              <input v-model="createForm.amount" type="number" step="0.01" min="0" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              <p v-if="createForm.errors.amount" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.amount }}</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" class="btn btn-outline" @click="showCreateDialog = false">Cancel</button>
              <button type="submit" :disabled="createForm.processing" class="btn btn-default">Save Note</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Edit Dialog ─────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showEditDialog" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showEditDialog = false" />
        <div class="relative z-10 w-full max-w-lg mx-4 rounded-lg bg-white dark:bg-gray-800 shadow-xl">
          <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Edit Commission Note</h2>
            <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none" @click="showEditDialog = false">&times;</button>
          </div>
          <form @submit.prevent="submitEdit" class="p-6 space-y-4">
            <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                    <input
                        type="date"
                        v-model="editForm.date"
                        required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                    >
                    </input>
                    <p v-if="editForm.errors.date" class="text-red-600 text-xs mt-1">{{ editForm.errors.date }}</p>
                </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
              <select v-model="editSelectedCompany" @change="onEditCompanyChange" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select company</option>
                <option v-for="c in companies" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
              </select>
              <p v-if="editForm.errors.company_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.company_id }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
              <select v-model="editSelectedBranch" @change="onEditBranchChange" :disabled="!editSelectedCompany" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                <option value="">Select branch</option>
                <option v-for="b in editAvailableBranches" :key="b.id" :value="String(b.id)">{{ b.name }}</option>
              </select>
              <p v-if="editForm.errors.branch_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.branch_id }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employee</label>
              <select v-model="editForm.employee_id" :disabled="!editSelectedBranch" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                <option value="">Select employee</option>
                <option v-for="e in editAvailableEmployees" :key="e.id" :value="e.id">{{ e.employee_number }} - {{ e.employee_display }}</option>
              </select>
              <p v-if="editForm.errors.employee_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.employee_id }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
              <textarea v-model="editForm.description" rows="3" required maxlength="1000" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              <p v-if="editForm.errors.description" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.description }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
              <input v-model="editForm.amount" type="number" step="0.01" min="0" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              <p v-if="editForm.errors.amount" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ editForm.errors.amount }}</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" class="btn btn-outline" @click="showEditDialog = false">Cancel</button>
              <button type="submit" :disabled="editForm.processing" class="btn btn-default">Update Note</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

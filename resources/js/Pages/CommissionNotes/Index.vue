<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'
import { useFilterStore } from '@/stores/useFilterStore'
import { Company, Branch, CommissionNote } from '@/types/models'
import { Filters, Permissions } from '@/types/inertia'

interface Props {
  notes: CommissionNote[]
  companies: Company[]
  branches: Branch[]
  filters: Filters
  can?: Permissions
}

const props = defineProps<Props>()

// ---- Pinia (meaningful UI state) ----
const store = useFilterStore()

// Initialize from backend filters
store.companyId = props.filters?.company_id ?? null
store.branchId = props.filters?.branch_id ?? null

const page = usePage()

function route(name: string, params?: any, absolute = true) {
  return ziggyRoute(name, params, absolute, page.props.ziggy)
}

// ---- Watchers ----
watch(() => store.companyId, () => {
  store.branchId = null
  applyFilter()
})

watch(() => store.branchId, applyFilter)

// ---- Filtering ----
function applyFilter() {
  router.get(route('commission-notes.index'), {
    company_id: store.companyId || undefined,
    branch_id: store.branchId || undefined,
  }, { preserveState: true, replace: true })
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
</script>

<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-800">Commission Notes</h1>

      <Link
        v-if="props.can?.manage"
        :href="route('commission-notes.create')"
        class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700"
      >
        New Note
      </Link>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 mb-6">
      <select v-model="store.companyId" class="border rounded px-3 py-1.5 text-sm bg-white">
        <option :value="null">All companies</option>
        <option v-for="c in companies" :key="c.id" :value="c.id">
          {{ c.name }}
        </option>
      </select>

      <select v-model="store.branchId" class="border rounded px-3 py-1.5 text-sm bg-white">
        <option :value="null">All branches</option>
        <option v-for="b in filteredBranches" :key="b.id" :value="b.id">
          {{ b.name }}
        </option>
      </select>
    </div>

    <!-- Notes table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-gray-600">Employee</th>
            <th class="px-4 py-3 text-left text-gray-600">Company / Branch</th>
            <th class="px-4 py-3 text-left text-gray-600">Description</th>
            <th class="px-4 py-3 text-right text-gray-600">Amount</th>
            <th class="px-4 py-3 text-left text-gray-600">Author</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          <tr v-if="notes.length === 0">
            <td colspan="6" class="px-4 py-6 text-center text-gray-400">
              No notes found.
            </td>
          </tr>

          <tr v-for="note in notes" :key="note.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono">
              {{ note.employee?.employee_display }}
            </td>

            <td class="px-4 py-3 text-gray-600">
              {{ note.company?.name }} / {{ note.branch?.name }}
            </td>

            <td class="px-4 py-3">
              {{ note.description }}
            </td>

            <td class="px-4 py-3 text-right font-mono">
              {{ formatAmount(note.amount) }}
            </td>

            <td class="px-4 py-3 text-gray-600">
              {{ note.author?.name }}
            </td>

            <td class="px-4 py-3 text-right">
              <Link
                v-if="props.can?.manage"
                :href="route('commission-notes.edit', note.id)"
                class="text-blue-600 hover:underline mr-3"
              >
                Edit
              </Link>

              <Link
                :href="route('commission-notes.show', note.id)"
                class="text-gray-600 hover:underline"
              >
                View
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

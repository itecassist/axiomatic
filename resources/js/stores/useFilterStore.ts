import { defineStore } from 'pinia'

export const useFilterStore = defineStore('filters', {
  state: () => ({
    companyId: null as number | null,
    branchId: null as number | null,
  }),
})

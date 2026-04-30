export interface Filters {
  company_id?: number | null
  branch_id?: number | null
  search?: string | null
  amount_min?: number | null
  amount_max?: number | null
  date_from?: string | null
  date_to?: string | null
}

export interface Permissions {
  manage?: boolean
  edit?: boolean
}

export interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

export interface Paginated<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
  links: PaginationLink[]
}

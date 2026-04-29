export interface Auth {
    user: User,
    remember: boolean
}

export interface Company {
  id: number
  name: string
}

export interface Branch {
  id: number
  name: string
  company_id: number
}

export interface Employee {
  id: number
  branch_id: number
  first_name: string
  last_name: string
  employee_number: string
  employee_display?: string
  avatar?: string
}

export interface User {
  id: number
  name: string
  email?: string
}

export interface CommissionNote {
  id: number
  description: string
  amount: string | number
  company: Company
  branch: Branch
  employee: Employee
  author: User
}

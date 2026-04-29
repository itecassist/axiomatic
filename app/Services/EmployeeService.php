<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeService
{
    public function list(?int $branchId = null, ?int $companyId = null): LengthAwarePaginator
    {
        return Employee::with('branch.company')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($companyId, fn ($q) => $q->whereHas('branch', fn ($b) => $b->where('company_id', $companyId)))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Employee $employee) => tap($employee)->append('employee_display'));
    }

    public function create(array $data): Employee
    {
        abort_unless(Auth::user()?->can('manage employees'), 403);

        return Employee::create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        abort_unless(Auth::user()?->can('manage employees'), 403);

        $employee->update($data);

        return $employee->fresh();
    }

    public function delete(Employee $employee): void
    {
        abort_unless(Auth::user()?->can('manage employees'), 403);

        $employee->delete();
    }
}

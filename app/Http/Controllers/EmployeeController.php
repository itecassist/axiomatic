<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $service) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Employees/Index', [
            'employees' => $this->service->list(
                $request->integer('branch_id') ?: null,
                $request->integer('company_id') ?: null,
            ),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'branches'  => Branch::orderBy('name')->get(['id', 'company_id', 'name']),
            'filters'   => $request->only('company_id', 'branch_id'),
            'can'       => ['manageEmployees' => auth()->user()->can('manage employees')],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Employees/Create', [
            'companies'        => Company::orderBy('name')->get(['id', 'name']),
            'branches'         => Branch::orderBy('name')->get(['id', 'company_id', 'name']),
            'preselectedBranch' => $request->integer('branch_id') ?: null,
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('employees.index')->with('success', 'Employee created.');
    }

    public function edit(Employee $employee): Response
    {
        return Inertia::render('Employees/Edit', [
            'employee'  => $employee->append('employee_display'),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'branches'  => Branch::orderBy('name')->get(['id', 'company_id', 'name']),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->service->update($employee, $request->validated());

        return redirect()->route('employees.index')->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->service->delete($employee);

        return redirect()->route('employees.index')->with('success', 'Employee deleted.');
    }
}

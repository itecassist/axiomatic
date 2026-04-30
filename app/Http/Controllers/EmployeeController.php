<?php

namespace App\Http\Controllers;

use App\Exports\EmployeesExport;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Services\EmployeeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $service) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Employees/Index', [
            'employees' => $this->service->list(
                $request->integer('branch_id') ?: null,
                $request->integer('company_id') ?: null,
                $request->string('search')->toString() ?: null,
            ),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'branches'  => Branch::orderBy('name')->get(['id', 'company_id', 'name']),
            'filters'   => $request->only('company_id', 'branch_id', 'search'),
            'can'       => ['manageEmployees' => (bool) $request->user()?->can('manage employees')],
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

    public function show(Employee $employee): Response
    {
        $employee->load([
            'branch.company',
            'commission_notes.author:id,name',
            'commission_notes.company:id,name',
            'commission_notes.branch:id,name,company_id',
        ]);

        $employee->append('employee_display');

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
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

    public function export(Request $request)
    {
        $format = $request->string('format')->toString();
        $employees = $this->service->export(
            $request->integer('branch_id') ?: null,
            $request->integer('company_id') ?: null,
            $request->string('search')->toString() ?: null,
        );

        return match ($format) {
            'csv' => $this->exportCsv($employees),
            'excel' => Excel::download(new EmployeesExport($employees), 'employees.xlsx'),
            'pdf' => $this->exportPdf($employees),
            default => abort(400, 'Invalid export format'),
        };
    }

    private function exportCsv($employees)
    {
        $csv = "Employee Number,Name,Branch,Company\n";

        foreach ($employees as $emp) {
            $csv .= sprintf(
                '"%s","%s","%s","%s"' . "\n",
                $emp->employee_number,
                $emp->employee_display,
                $emp->branch?->name ?? '',
                $emp->branch?->company?->name ?? ''
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=employees.csv',
        ]);
    }

    private function exportPdf($employees)
    {
        $html = view('exports.employees-pdf', ['employees' => $employees])->render();

        return Pdf::loadHTML($html)
            ->download('employees.pdf');
    }
}

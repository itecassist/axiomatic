<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $branches = Branch::query()
            ->withCount(['employees', 'commissionNotes'])
            ->withSum('commissionNotes', 'amount')
            ->with([
                'employees' => fn ($query) => $query
                    ->withSum('commission_notes', 'amount')
                    ->orderBy('last_name')
                    ->orderBy('first_name'),
            ])
            ->orderBy('name')
            ->get();

        return Inertia::render('Dashboard', [
            'can' => [
                'viewCommissionNotes' => $request->user()->can('view commission notes'),
                'manageCommissionNotes' => $request->user()->can('manage commission notes'),
            ],
            'charts' => [
                'branchEmployees' => $branches->map(fn (Branch $branch) => [
                    'label' => $branch->name,
                    'value' => $branch->employees_count,
                ])->values(),
                'branchCommissionNotes' => $branches->map(fn (Branch $branch) => [
                    'label' => $branch->name,
                    'value' => $branch->commission_notes_count,
                ])->values(),
                'branchCommissionAmounts' => $branches->map(fn (Branch $branch) => [
                    'label' => $branch->name,
                    'value' => (float) ($branch->commission_notes_sum_amount ?? 0),
                ])->values(),
                'employeeCommissionAmountsByBranch' => $branches->map(fn (Branch $branch) => [
                    'branch' => $branch->name,
                    'employees' => $branch->employees->map(fn ($employee) => [
                        'label' => $employee->employee_display,
                        'value' => (float) ($employee->commission_notes_sum_amount ?? 0),
                    ])->values(),
                ])->values(),
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\CommissionNote;
use App\Services\CommissionNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommissionNoteController extends Controller
{
    public function __construct(private CommissionNoteService $service) {}

    public function index(Request $request): Response
    {

        $filters = $request->only(['company_id', 'branch_id', 'search', 'amount_min', 'amount_max', 'date_from', 'date_to']);
        $perPage  = max(5, min(100, (int) $request->input('per_page', 20)));

        return Inertia::render('CommissionNotes/Index', [
            'notes'     => $this->service->list(
                isset($filters['company_id']) ? (int) $filters['company_id'] : null,
                isset($filters['branch_id'])  ? (int) $filters['branch_id']  : null,
                isset($filters['search']) && $filters['search'] !== '' ? (string) $filters['search'] : null,
                isset($filters['amount_min']) && is_numeric($filters['amount_min']) ? (float) $filters['amount_min'] : null,
                isset($filters['amount_max']) && is_numeric($filters['amount_max']) ? (float) $filters['amount_max'] : null,
                isset($filters['date_from']) && $filters['date_from'] !== '' ? (string) $filters['date_from'] : null,
                isset($filters['date_to']) && $filters['date_to'] !== '' ? (string) $filters['date_to'] : null,
                $perPage,
            ),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'branches'  => Branch::orderBy('name')->get(['id', 'company_id', 'name']),
            'employees' => Employee::orderBy('last_name')->get()->each(fn (Employee $emp) => $emp->append('employee_display')),
            'filters'   => $filters,
            'can'       => [
                'manage' => $request->user()->can('manage commission notes'),
            ],
        ]);
    }

    public function create(): Response
    {

        return Inertia::render('CommissionNotes/Create', [
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'branches'  => Branch::orderBy('name')->get(['id', 'company_id', 'name']),
            'employees' => Employee::orderBy('last_name')->get()->each(fn (Employee $emp) => $emp->append('employee_display')),

        ]);
    }

    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $this->service->create($request->user(), $request->validated());

        return redirect()->route('commission-notes.index')->with('success', 'Note created.');
    }

    public function edit(Request $request, CommissionNote $note): Response
    {
        $user = $request->user();

        if ($note->author_id !== $user->id && !$user->can('manage commission notes')) {
            abort(403);
        }
        return Inertia::render('CommissionNotes/Edit', [
            'note'      => $note->load(['company', 'branch', 'employee']),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'branches'  => Branch::orderBy('name')->get(['id', 'company_id', 'name']),
            'employees' => Employee::orderBy('last_name')->get(['id', 'branch_id', 'first_name', 'last_name', 'employee_number']),
        ]);
    }

    public function update(UpdateNoteRequest $request, CommissionNote $note): RedirectResponse
    {
        $this->service->update($request->user(), $note, $request->validated());

        return redirect()->route('commission-notes.index')->with('success', 'Note updated.');
    }

    public function show(CommissionNote $note): Response
    {
        return Inertia::render('CommissionNotes/Show', [
            'note' => $note->load(['company', 'branch', 'employee', 'author']),
        ]);
    }
}

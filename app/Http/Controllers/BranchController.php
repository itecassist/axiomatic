<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Services\BranchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function __construct(private BranchService $service) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Branches/Index', [
            'branches'  => $this->service->list($request->integer('company_id') ?: null),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'filters'   => $request->only('company_id'),
            'can'       => ['manageBranches' => auth()->user()->can('manage branches')],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Branches/Create', [
            'companies'         => Company::orderBy('name')->get(['id', 'name']),
            'preselectedCompany' => $request->integer('company_id') ?: null,
        ]);
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('branches.index')->with('success', 'Branch created.');
    }

    public function edit(Branch $branch): Response
    {
        return Inertia::render('Branches/Edit', [
            'branch'    => $branch,
            'companies' => Company::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->service->update($branch, $request->validated());

        return redirect()->route('branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->service->delete($branch);

        return redirect()->route('branches.index')->with('success', 'Branch deleted.');
    }
}

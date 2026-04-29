<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct(private CompanyService $service) {}

    public function index(): Response
    {
        return Inertia::render('Companies/Index', [
            'companies' => $this->service->list(),
            'can'       => ['manageCompanies' => auth()->user()->can('manage companies')],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Companies/Create');
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('companies.index')->with('success', 'Company created.');
    }

    public function edit(Company $company): Response
    {
        return Inertia::render('Companies/Edit', [
            'company' => $company,
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $this->service->update($company, $request->validated());

        return redirect()->route('companies.index')->with('success', 'Company updated.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->service->delete($company);

        return redirect()->route('companies.index')->with('success', 'Company deleted.');
    }
}

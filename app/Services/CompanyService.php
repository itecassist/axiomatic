<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;

class CompanyService
{
    public function list(): Collection
    {
        return Company::with('branches')->withCount('branches')->orderBy('name')->get();
    }

    public function create(array $data): Company
    {
        abort_unless(auth()->user()->can('manage companies'), 403);

        return Company::create($data);
    }

    public function update(Company $company, array $data): Company
    {
        abort_unless(auth()->user()->can('manage companies'), 403);

        $company->update($data);

        return $company->fresh();
    }

    public function delete(Company $company): void
    {
        abort_unless(auth()->user()->can('manage companies'), 403);

        $company->delete();
    }
}

<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

class BranchService
{
    public function list(?int $companyId = null): Collection
    {
        return Branch::with('company')
            ->withCount('employees')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Branch
    {
        abort_unless(auth()->user()->can('manage branches'), 403);

        return Branch::create($data);
    }

    public function update(Branch $branch, array $data): Branch
    {
        abort_unless(auth()->user()->can('manage branches'), 403);

        $branch->update($data);

        return $branch->fresh();
    }

    public function delete(Branch $branch): void
    {
        abort_unless(auth()->user()->can('manage branches'), 403);

        $branch->delete();
    }
}

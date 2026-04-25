<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

class CommissionNoteService
{
    public function list(?int $companyId = null, ?int $branchId = null): Collection
    {
        return CommissionNote::query()
            ->with(['company', 'branch', 'employee', 'author'])
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->get()
            ->each(fn (CommissionNote $note) => $note->employee?->append('employee_display'));
    }

    public function create(User $user, array $data): CommissionNote
    {
        $company = Company::findOrFail($data['company_id']);
        $branch = Branch::findOrFail($data['branch_id']);
        $employee = Employee::findOrFail($data['employee_id']);

        // Integrity checks
        if ($branch->company_id !== $company->id) {
            throw new \InvalidArgumentException('Branch does not belong to the specified company.');
        }
        if ($employee->branch_id !== $branch->id) {
            throw new \InvalidArgumentException('Employee does not belong to the specified branch.');
        }

        // Auth rule
        if (!$user->can('manage commission notes')) {
            throw new AuthorizationException('You do not have permission to create commission notes.');
        }

        return CommissionNote::create([
            'company_id'  => $data['company_id'],
            'branch_id'   => $data['branch_id'],
            'employee_id' => $data['employee_id'],
            'author_id'   => $user->id,
            'description' => $data['description'],
            'amount'      => $data['amount'],
        ]);
    }

    public function update(User $user, CommissionNote $note, array $data): CommissionNote
    {
        // Auth rule
        if ($note->author_id !== $user->id && !$user->can('manage commission notes')) {
            throw new AuthorizationException('You may not edit this note.');
        }

        $note->update([
            'company_id'  => $data['company_id'],
            'branch_id'   => $data['branch_id'],
            'employee_id' => $data['employee_id'],
            'description' => $data['description'],
            'amount'      => $data['amount'],
        ]);

        return $note->fresh();
    }
}

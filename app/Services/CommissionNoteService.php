<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommissionNoteService
{
    public function list(
        ?int $companyId = null,
        ?int $branchId = null,
        ?string $search = null,
        ?float $amountMin = null,
        ?float $amountMax = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator
    {
        $search = $search ? trim($search) : null;

        return CommissionNote::query()
            ->with(['company', 'branch', 'employee', 'author'])
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('reference', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($eq) use ($search) {
                            $eq->where('employee_number', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($amountMin !== null, fn ($q) => $q->where('amount', '>=', $amountMin))
            ->when($amountMax !== null, fn ($q) => $q->where('amount', '<=', $amountMax))
            ->when($dateFrom, fn ($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('date', '<=', $dateTo))
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (CommissionNote $note) => tap($note, fn () => $note->employee?->append('employee_display')));
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
            'reference'   => $data['reference'] ?? strtoupper(uniqid()),
            'company_id'  => $data['company_id'],
            'branch_id'   => $data['branch_id'],
            'employee_id' => $data['employee_id'],
            'author_id'   => $user->id,
            'date'        => $data['date'] ?? now()->toDateString(),
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
            'date'        => $data['date'] ?? $note->date,
            'description' => $data['description'],
            'amount'      => $data['amount'],
        ]);

        return $note->fresh();
    }
}

<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionNoteFactory extends Factory
{
    public function definition(): array
    {
        $company  = Company::factory()->create();
        $branch   = Branch::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['branch_id' => $branch->id]);

        return [
            'reference'   => strtoupper(uniqid()),
            'company_id'  => $company->id,
            'branch_id'   => $branch->id,
            'employee_id' => $employee->id,
            'author_id'   => User::factory(),
            'date'        => fake()->dateTimeThisYear()->format('Y-m-d'),
            'description' => fake()->sentence(),
            'amount'      => fake()->randomFloat(2, 10, 5000),
        ];
    }
}

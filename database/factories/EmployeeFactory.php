<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'employee_number' => fake()->unique()->numerify('EMP###'),
            'avatar' => fake()->imageUrl(200, 200, 'people'),
        ];
    }
}

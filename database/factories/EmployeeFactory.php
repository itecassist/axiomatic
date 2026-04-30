<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'branch_id' => Branch::factory(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'employee_number' => fake()->unique()->numerify('EMP###'),
            'avatar' => sprintf(
                'https://ui-avatars.com/api/?name=%s&size=200&background=random&color=ffffff',
                urlencode("{$firstName} {$lastName}")
            ),
        ];
    }
}

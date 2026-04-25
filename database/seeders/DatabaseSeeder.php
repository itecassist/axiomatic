<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Services\CommissionNoteService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Permission seeding should be done first to ensure roles can be assigned permissions
        $this->call(PermissionSeeder::class);

        // Create test users with specific roles
        $viewer = User::create([
            'name'  => 'Viewer User',
            'email' => 'viewer@example.com',
            'password' => bcrypt('password'),
        ]);
        $viewer->assignRole('viewer');

        $manager = User::create([
            'name'  => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
        ]);
        $manager->assignRole('manager');

        // Create Company, Branch, Employee and CommissionNote data
        $company = Company::create(['name' => 'Spar']);

        // first branch, employee and note for testing
        $branch = $company->branches()->create([
            'name' => 'Spar Bellville',
        ]);
        $employee = $branch->employees()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employee_number' => 'EMP001',
        ]);

        // Second branch, employee and note for testing
        $branch2 = $company->branches()->create([
            'name' => 'Spar Century City',
        ]);
        $employee2 = $branch2->employees()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'employee_number' => 'EMP002',
        ]);

        // Create commission notes using the service to ensure proper authorization and business logic is applied
        $service = app(CommissionNoteService::class);

        $notesData = [
            [
                'branch' => $branch,
                'employee' => $employee,
                'amount' => 10000.00,
                'description' => 'Initial commission note for testing.',
            ],
            [
                'branch' => $branch2,
                'employee' => $employee2,
                'amount' => 20000.00,
                'description' => 'Second commission note for testing.',
            ],
        ];

        foreach ($notesData as $data) {
            $service->create($manager, [
                'company_id' => $company->id,
                'branch_id' => $data['branch']->id,
                'employee_id' => $data['employee']->id,
                'description' => $data['description'],
                'amount' => $data['amount'],
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
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
            'email' => 'viewer@axiomatic.co.za',
            'password' => bcrypt('password'),
        ]);
        $viewer->assignRole('viewer');

        $manager = User::create([
            'name'  => 'Manager User',
            'email' => 'manager@axiomatic.co.za',
            'password' => bcrypt('password'),
        ]);
        $manager->assignRole('manager');

        $admin = User::create([
            'name'  => 'Admin User',
            'email' => 'admin@axiomatic.co.za',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        // Create Company, Branch, Employee and CommissionNote data
        $company = Company::create(['name' => 'Spar']);

        // first branch, employee and note for testing
        $branch = $company->branches()->create([
            'name' => 'Spar Bellville',
        ]);
        Employee::factory()->count(20)->for($branch)->create();

        // Second branch, employee and note for testing
        $branch2 = $company->branches()->create([
            'name' => 'Spar Century City',
        ]);
        Employee::factory()->count(20)->for($branch2)->create();

        // Create commission notes using the service to ensure proper authorization and business logic is applied
        $service = app(CommissionNoteService::class);

        // create 12 commission notes per employee for all branches
        $notesData = [];
        foreach ($company->branches as $branch) {
            foreach ($branch->employees as $employee) {
                $randomEmployeeCount = rand(5, 15);
                for ($i = 0; $i < $randomEmployeeCount; $i++) {
                    $notesData[] = [
                        'branch' => $branch,
                        'employee' => $employee,
                        'description' => " for {$employee->employee_number} at {$branch->name}",
                        'amount' => rand(1500, 23000),
                        'date' => now()->subDays(rand(0, 365)),
                    ];
                }
            }
        }

        foreach ($notesData as $data) {
            $ref = strtoupper(uniqid());
            $service->create($manager, [
                'reference' => $ref,
                'company_id' => $company->id,
                'branch_id' => $data['branch']->id,
                'employee_id' => $data['employee']->id,
                'date' => $data['date'],
                'description' => 'Note #'.$ref.' '.$data['description'],
                'amount' => $data['amount'],
            ]);
        }
    }
}

<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'manage commission notes']);
    Permission::firstOrCreate(['name' => 'manage employees']);
});

// --- Guest ---

test('guest is redirected from employees export', function () {
    $this->get(route('employees.export', ['format' => 'csv']))
        ->assertRedirect(route('login'));
});

// --- Permission checks ---

test('user without manage permission gets 403 on export', function () {
    $user = makeUser();
    $this->actingAs($user)->get(route('employees.export', ['format' => 'csv']))
        ->assertForbidden();
});

test('manager can export as csv', function () {
    $user = makeUser(['manage commission notes', 'manage employees']);
    $company = Company::factory()->create();
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    Employee::factory()->create(['branch_id' => $branch->id, 'employee_number' => 'EMP001', 'first_name' => 'John', 'last_name' => 'Doe']);
    Employee::factory()->create(['branch_id' => $branch->id, 'employee_number' => 'EMP002', 'first_name' => 'Jane', 'last_name' => 'Smith']);

    $response = $this->actingAs($user)->get(route('employees.export', ['format' => 'csv']));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    $response->assertHeader('Content-Disposition', 'attachment; filename=employees.csv');
});

test('manager can export as excel', function () {
    $user = makeUser(['manage commission notes', 'manage employees']);
    $company = Company::factory()->create();
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    Employee::factory()->create(['branch_id' => $branch->id, 'employee_number' => 'EMP001', 'first_name' => 'John', 'last_name' => 'Doe']);

    $response = $this->actingAs($user)->get(route('employees.export', ['format' => 'excel']));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('manager can export as pdf', function () {
    $user = makeUser(['manage commission notes', 'manage employees']);
    $company = Company::factory()->create();
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    Employee::factory()->create(['branch_id' => $branch->id, 'employee_number' => 'EMP001', 'first_name' => 'John', 'last_name' => 'Doe']);

    $response = $this->actingAs($user)->get(route('employees.export', ['format' => 'pdf']));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('export respects company filter', function () {
    $user = makeUser(['manage commission notes', 'manage employees']);
    $company1 = Company::factory()->create(['name' => 'Company A']);
    $company2 = Company::factory()->create(['name' => 'Company B']);
    $branch1 = Branch::factory()->create(['company_id' => $company1->id]);
    $branch2 = Branch::factory()->create(['company_id' => $company2->id]);
    $emp1 = Employee::factory()->create(['branch_id' => $branch1->id, 'employee_number' => 'EMP001']);
    $emp2 = Employee::factory()->create(['branch_id' => $branch2->id, 'employee_number' => 'EMP002']);

    // Export all employees
    $responseAll = $this->actingAs($user)->get(route('employees.export', ['format' => 'csv']));
    $contentAll = $responseAll->getContent();

    // Verify both are in full export
    expect(str_contains($contentAll, 'EMP001'))->toBeTrue();
    expect(str_contains($contentAll, 'EMP002'))->toBeTrue();

    // Export filtered by company
    $responseFiltered = $this->actingAs($user)->get(route('employees.export', ['format' => 'csv', 'company_id' => $company1->id]));
    $contentFiltered = $responseFiltered->getContent();

    // Verify only company1 employee is in filtered export
    expect(str_contains($contentFiltered, 'EMP001'))->toBeTrue();
    expect(str_contains($contentFiltered, 'EMP002'))->toBeFalse();
});

test('export respects branch filter', function () {
    $user = makeUser(['manage commission notes', 'manage employees']);
    $company = Company::factory()->create();
    $branch1 = Branch::factory()->create(['company_id' => $company->id, 'name' => 'Branch A']);
    $branch2 = Branch::factory()->create(['company_id' => $company->id, 'name' => 'Branch B']);
    $emp1 = Employee::factory()->create(['branch_id' => $branch1->id, 'employee_number' => 'EMP001']);
    $emp2 = Employee::factory()->create(['branch_id' => $branch2->id, 'employee_number' => 'EMP002']);

    $response = $this->actingAs($user)->get(route('employees.export', ['format' => 'csv', 'branch_id' => $branch1->id]));
    $content = $response->getContent();

    expect(str_contains($content, 'EMP001'))->toBeTrue();
    expect(str_contains($content, 'EMP002'))->toBeFalse();
});

test('export respects search filter', function () {
    $user = makeUser(['manage commission notes', 'manage employees']);
    $company = Company::factory()->create();
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    $emp1 = Employee::factory()->create([
        'branch_id' => $branch->id,
        'employee_number' => 'EMP-SEARCH-TEST',
        'first_name' => 'Searchable',
        'last_name' => 'Employee'
    ]);
    Employee::factory()->create(['branch_id' => $branch->id]);

    // Search for specific employee
    $response = $this->actingAs($user)->get(route('employees.export', ['format' => 'csv', 'search' => 'SEARCH']));
    $content = $response->getContent();

    // Check that the searched employee is in the export
    expect(str_contains($content, 'EMP-SEARCH-TEST'))->toBeTrue();
});

test('export with invalid format returns 400', function () {
    $user = makeUser(['manage commission notes', 'manage employees']);

    $this->actingAs($user)->get(route('employees.export', ['format' => 'invalid']))
        ->assertStatus(400);
});

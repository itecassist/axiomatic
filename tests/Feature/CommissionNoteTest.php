<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\CommissionNote;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'view commission notes']);
    Permission::firstOrCreate(['name' => 'manage commission notes']);
});

function makeUser(array $permissions = []): User
{
    $user = User::factory()->create();
    foreach ($permissions as $p) {
        $user->givePermissionTo($p);
    }
    return $user;
}

function makeNoteFor(User $author): array
{
    $company  = Company::factory()->create();
    $branch   = Branch::factory()->create(['company_id' => $company->id]);
    $employee = Employee::factory()->create(['branch_id' => $branch->id]);
    $note     = CommissionNote::factory()->create([
        'author_id'   => $author->id,
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
    ]);
    return [$note, $company, $branch, $employee];
}

// --- Guest ---

test('guest is redirected from notes index', function () {
    $this->get(route('commission-notes.index'))->assertRedirect(route('login'));
});

test('guest is redirected from notes create', function () {
    $this->get(route('commission-notes.create'))->assertRedirect(route('login'));
});

// --- View permission ---

test('viewer can access notes index', function () {
    $user = makeUser(['view commission notes']);
    $this->actingAs($user)->get(route('commission-notes.index'))->assertOk();
});

test('user without any permission gets 403 on notes index', function () {
    $user = makeUser();
    $this->actingAs($user)->get(route('commission-notes.index'))->assertForbidden();
});

test('viewer cannot access notes create', function () {
    $user = makeUser(['view commission notes']);
    $this->actingAs($user)->get(route('commission-notes.create'))->assertForbidden();
});

// --- Manage / create permission ---

test('manager can access notes create', function () {
    $user = makeUser(['view commission notes', 'manage commission notes']);
    $this->actingAs($user)->get(route('commission-notes.create'))->assertOk();
});

test('manager can store a note', function () {
    $manager  = makeUser(['view commission notes', 'manage commission notes']);
    $company  = Company::factory()->create();
    $branch   = Branch::factory()->create(['company_id' => $company->id]);
    $employee = Employee::factory()->create(['branch_id' => $branch->id]);

    $this->actingAs($manager)->post(route('commission-notes.store'), [
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
        'description' => 'Commission for Q1',
        'amount'      => '250.00',
    ])->assertRedirect(route('commission-notes.index'));

    $this->assertDatabaseHas('commission_notes', [
        'description' => 'Commission for Q1',
        'author_id'   => $manager->id,
    ]);
});

test('viewer cannot store a note', function () {
    $viewer   = makeUser(['view commission notes']);
    $company  = Company::factory()->create();
    $branch   = Branch::factory()->create(['company_id' => $company->id]);
    $employee = Employee::factory()->create(['branch_id' => $branch->id]);

    $this->actingAs($viewer)->post(route('commission-notes.store'), [
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
        'description' => 'Should be blocked',
        'amount'      => '100.00',
    ])->assertForbidden();
});

// --- Edit permission ---

test('author without manage permission can access edit page for their own note', function () {
    $author = makeUser(['view commission notes']); // NOT manager
    [$note] = makeNoteFor($author);

    $this->actingAs($author)->get(route('commission-notes.edit', $note))->assertOk();
});

test('manager can access edit page for any note', function () {
    $author  = makeUser([]);
    [$note]  = makeNoteFor($author);
    $manager = makeUser(['view commission notes', 'manage commission notes']);

    $this->actingAs($manager)->get(route('commission-notes.edit', $note))->assertOk();
});

test('viewer who is not the author cannot access edit page', function () {
    $author = makeUser(['manage commission notes']);
    [$note] = makeNoteFor($author);
    $viewer = makeUser(['view commission notes']);

    $this->actingAs($viewer)->get(route('commission-notes.edit', $note))->assertForbidden();
});

// --- Update permission (backend enforcement) ---

test('manager can update any note', function () {
    $author  = makeUser([]);
    [$note, $company, $branch, $employee] = makeNoteFor($author);
    $manager = makeUser(['view commission notes', 'manage commission notes']);

    $this->actingAs($manager)->put(route('commission-notes.update', $note), [
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
        'description' => 'Updated by manager',
        'amount'      => '999.00',
    ])->assertRedirect(route('commission-notes.index'));

    $this->assertDatabaseHas('commission_notes', ['description' => 'Updated by manager']);
});

test('author can update their own note', function () {
    $author = makeUser(['view commission notes']);
    [$note, $company, $branch, $employee] = makeNoteFor($author);

    $this->actingAs($author)->put(route('commission-notes.update', $note), [
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
        'description' => 'Self-updated',
        'amount'      => '50.00',
    ])->assertRedirect(route('commission-notes.index'));

    $this->assertDatabaseHas('commission_notes', ['description' => 'Self-updated']);
});

test('viewer who is not the author cannot update a note via PUT', function () {
    $author = makeUser(['manage commission notes']);
    [$note, $company, $branch, $employee] = makeNoteFor($author);
    $viewer = makeUser(['view commission notes']);

    $this->actingAs($viewer)->put(route('commission-notes.update', $note), [
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
        'description' => 'Hacked',
        'amount'      => '1.00',
    ])->assertForbidden();

    $this->assertDatabaseMissing('commission_notes', ['description' => 'Hacked']);
});

test('author without any permission cannot update note', function () {
    $author = makeUser(); // no permissions
    [$note, $company, $branch, $employee] = makeNoteFor($author);

    $this->actingAs($author)->put(route('commission-notes.update', $note), [
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
        'description' => 'Should fail',
        'amount'      => '10.00',
    ])->assertForbidden();
});

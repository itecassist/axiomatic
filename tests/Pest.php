<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function makeUser(array $permissions = []): User
{
    $user = User::factory()->create();
    foreach ($permissions as $p) {
        $user->givePermissionTo($p);
    }
    return $user;
}

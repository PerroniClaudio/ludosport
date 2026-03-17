<?php

use App\Models\Nation;
use App\Models\Rank;
use App\Models\Role;
use App\Models\User;

test('users with multiple roles are redirected to role selection even when an intended url exists', function () {
    Rank::create(['name' => 'Novice']);

    $rectorRole = Role::create([
        'name' => 'rector',
        'prefix' => 'rector',
        'label' => 'rector',
    ]);

    $deanRole = Role::create([
        'name' => 'dean',
        'prefix' => 'dean',
        'label' => 'dean',
    ]);

    $nation = Nation::create([
        'name' => 'Italy',
        'code' => 'IT',
    ]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'nation_id' => $nation->id,
    ]);

    $user->roles()->sync([$rectorRole->id, $deanRole->id]);

    $this->get('/dashboard')->assertRedirect('/login');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('role-selector', absolute: false));
});

<?php

use App\Models\Academy;
use App\Models\Nation;
use App\Models\Rank;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Role::query()->create([
        'name' => 'admin',
        'prefix' => 'admin',
        'label' => 'admin',
    ]);

    Role::query()->create([
        'name' => 'athlete',
        'prefix' => 'athlete',
        'label' => 'athlete',
    ]);

    $this->nation = Nation::query()->create([
        'name' => 'Italy',
        'code' => 'IT',
    ]);

    $this->academy = Academy::query()->create([
        'name' => 'Academy Test',
        'slug' => 'academy-test-gender-optional',
        'nation_id' => $this->nation->id,
    ]);

    $this->rank = Rank::query()->create([
        'name' => 'Novice',
    ]);

    $this->admin = User::factory()->create([
        'nation_id' => $this->nation->id,
        'academy_id' => $this->academy->id,
        'rank_id' => $this->rank->id,
    ]);

    $adminRoleId = Role::query()->where('name', 'admin')->value('id');
    $this->admin->roles()->sync([$adminRoleId]);

    $this->actingAs($this->admin);
    session(['role' => 'admin']);
});

it('creates a user without gender from users store route', function () {
    Mail::fake();

    $response = $this->post(route('users.store'), [
        'name' => 'Mario',
        'surname' => 'Rossi',
        'email' => 'gender-optional-create@test.local',
        'year' => date('Y'),
        'nationality' => $this->nation->name,
        'academy_id' => $this->academy->id,
        'roles' => 'athlete',
        'birthday' => '1990-01-01',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'gender-optional-create@test.local',
        'gender' => null,
    ]);
});

it('updates a user without requiring gender', function () {
    $athleteRoleId = Role::query()->where('name', 'athlete')->value('id');

    $user = User::factory()->create([
        'email' => 'gender-optional-update@test.local',
        'nation_id' => $this->nation->id,
        'academy_id' => $this->academy->id,
        'rank_id' => $this->rank->id,
        'gender' => 'male',
    ]);
    $user->roles()->sync([$athleteRoleId]);

    $response = $this->post(route('users.update', $user), [
        'name' => 'Mario Updated',
        'surname' => $user->surname,
        'email' => $user->email,
        'year' => date('Y'),
        'nationality' => $this->nation->id,
        'birthday' => '1990-01-01',
        'gender' => null,
        'rank' => $this->rank->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Mario Updated',
        'gender' => null,
    ]);
});

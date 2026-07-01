<?php

use App\Models\Academy;
use App\Models\Clan;
use App\Models\Nation;
use App\Models\Rank;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function createAdminUser(): User
{
    Rank::create(['name' => 'Novice']);

    $adminRole = Role::create([
        'name' => 'admin',
        'prefix' => 'admin',
        'label' => 'admin',
    ]);

    Role::create([
        'name' => 'manager',
        'prefix' => 'manager',
        'label' => 'manager',
    ]);

    $admin = User::factory()->create();
    $admin->roles()->syncWithoutDetaching($adminRole->id);

    return $admin;
}

function createInstitutionTree(): array
{
    $nation = Nation::create([
        'name' => 'Italy',
        'code' => 'IT',
    ]);

    $academy = Academy::create([
        'name' => 'Academy Test',
        'slug' => 'academy-test',
        'nation_id' => $nation->id,
    ]);

    $school = School::create([
        'name' => 'School Test',
        'slug' => 'school-test',
        'academy_id' => $academy->id,
        'nation_id' => $nation->id,
    ]);

    $clan = Clan::create([
        'name' => 'Clan Test',
        'slug' => 'clan-test',
        'school_id' => $school->id,
    ]);

    return [$academy, $school, $clan];
}

test('personnel can be created from academy without birthday', function () {
    Mail::fake();
    $this->withoutMiddleware();

    $admin = createAdminUser();
    [$academy] = createInstitutionTree();

    $response = $this->actingAs($admin)->post(route('academies.users.create', $academy), [
        'academy_id' => $academy->id,
        'type' => 'personnel',
        'name' => 'Mario',
        'surname' => 'Rossi',
        'email' => 'personnel-academy@example.com',
        'roles' => 'manager',
    ]);

    $response->assertRedirect(route('academies.edit', $academy));

    $user = User::where('email', 'personnel-academy@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->birthday)->toBeNull();
    expect($user->is_user_minor)->toBeFalse();

    $this->assertDatabaseHas('academies_personnel', [
        'academy_id' => $academy->id,
        'user_id' => $user->id,
    ]);
});

test('personnel can be created from school without birthday', function () {
    Mail::fake();
    $this->withoutMiddleware();

    $admin = createAdminUser();
    [$academy, $school] = createInstitutionTree();

    $response = $this->actingAs($admin)->post(route('schools.users.create', $school), [
        'school_id' => $school->id,
        'type' => 'personnel',
        'name' => 'Luca',
        'surname' => 'Bianchi',
        'email' => 'personnel-school@example.com',
        'roles' => 'manager',
    ]);

    $response->assertRedirect(route('schools.edit', $school));

    $user = User::where('email', 'personnel-school@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->birthday)->toBeNull();
    expect($user->is_user_minor)->toBeFalse();

    $this->assertDatabaseHas('academies_personnel', [
        'academy_id' => $academy->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('schools_personnel', [
        'school_id' => $school->id,
        'user_id' => $user->id,
    ]);
});

test('personnel can be created from clan without birthday', function () {
    Mail::fake();
    $this->withoutMiddleware();

    $admin = createAdminUser();
    [$academy, $school, $clan] = createInstitutionTree();

    $response = $this->actingAs($admin)->post(route('clans.users.create', $clan), [
        'clan_id' => $clan->id,
        'type' => 'personnel',
        'name' => 'Giulia',
        'surname' => 'Verdi',
        'email' => 'personnel-clan@example.com',
        'roles' => 'manager',
    ]);

    $response->assertRedirect(route('clans.edit', $clan));

    $user = User::where('email', 'personnel-clan@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->birthday)->toBeNull();
    expect($user->is_user_minor)->toBeFalse();

    $this->assertDatabaseHas('academies_personnel', [
        'academy_id' => $academy->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('schools_personnel', [
        'school_id' => $school->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('clans_personnel', [
        'clan_id' => $clan->id,
        'user_id' => $user->id,
    ]);
});

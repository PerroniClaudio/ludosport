<?php

use App\Models\Academy;
use App\Models\Clan;
use App\Models\Nation;
use App\Models\Role;
use App\Models\School;
use App\Models\User;

beforeEach(function () {
    Role::query()->create([
        'name' => 'admin',
        'prefix' => 'admin',
        'label' => 'admin',
    ]);

    Role::query()->create([
        'name' => 'manager',
        'prefix' => 'manager',
        'label' => 'manager',
    ]);

    Role::query()->create([
        'name' => 'athlete',
        'prefix' => 'athlete',
        'label' => 'athlete',
    ]);

    $nation = Nation::query()->create([
        'name' => 'Italia',
        'code' => 'IT',
    ]);

    $academy = Academy::query()->create([
        'name' => 'Academy Test',
        'slug' => 'academy-test',
        'nation_id' => $nation->id,
    ]);

    $school = School::query()->create([
        'name' => 'School Test',
        'slug' => 'school-test',
        'academy_id' => $academy->id,
        'nation_id' => $nation->id,
    ]);

    Clan::query()->create([
        'name' => 'Clan Test',
        'slug' => 'clan-test',
        'school_id' => $school->id,
    ]);

    $admin = User::factory()->create([
        'email' => 'admin+personnel@test.local',
        'nation_id' => $nation->id,
        'academy_id' => $academy->id,
    ]);

    $adminRoleId = Role::query()->where('name', 'admin')->value('id');
    $admin->roles()->sync([$adminRoleId]);

    $this->actingAs($admin);
    session(['role' => 'admin']);

    $this->academy = $academy;
    $this->school = $school;
    $this->clan = Clan::query()->firstOrFail();
});

it('creates personnel for academy with birthday', function () {
    $response = $this->post(route('academies.users.create', $this->academy), [
        'academy_id' => $this->academy->id,
        'type' => 'personnel',
        'name' => 'Mario',
        'surname' => 'Rossi',
        'email' => 'academy-personnel@test.local',
        'birthday' => '1990-01-01',
        'roles' => 'manager',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'academy-personnel@test.local',
        'birthday' => '1990-01-01',
    ]);
});

it('creates personnel for school with birthday', function () {
    $response = $this->post(route('schools.users.create', $this->school), [
        'school_id' => $this->school->id,
        'type' => 'personnel',
        'name' => 'Luca',
        'surname' => 'Bianchi',
        'email' => 'school-personnel@test.local',
        'birthday' => '1991-02-02',
        'roles' => 'manager',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'school-personnel@test.local',
        'birthday' => '1991-02-02',
    ]);
});

it('creates personnel for clan with birthday', function () {
    $response = $this->post(route('clans.users.create', $this->clan), [
        'clan_id' => $this->clan->id,
        'type' => 'personnel',
        'name' => 'Giulia',
        'surname' => 'Verdi',
        'email' => 'clan-personnel@test.local',
        'birthday' => '1992-03-03',
        'roles' => 'manager',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'clan-personnel@test.local',
        'birthday' => '1992-03-03',
    ]);
});

it('requires birthday for personnel', function () {
    $response = $this->post(route('academies.users.create', $this->academy), [
        'academy_id' => $this->academy->id,
        'type' => 'personnel',
        'name' => 'Personale',
        'surname' => 'SenzaData',
        'email' => 'personnel-no-birthday@test.local',
        'roles' => 'manager',
    ]);

    $response->assertSessionHasErrors('birthday');
});

it('still requires birthday for athlete', function () {
    $response = $this->post(route('academies.users.create', $this->academy), [
        'academy_id' => $this->academy->id,
        'type' => 'athlete',
        'name' => 'Atleta',
        'surname' => 'SenzaData',
        'email' => 'athlete-no-birthday@test.local',
    ]);

    $response->assertSessionHasErrors('birthday');
});

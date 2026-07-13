<?php

use App\Models\Nation;
use App\Models\PrivacyPolicy;
use App\Models\Rank;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    config(['scout.driver' => 'null']);

    Rank::firstOrCreate(['name' => 'Novice']);
    PrivacyPolicy::getOrCreate();

    foreach (range(1, 10) as $id) {
        Nation::firstOrCreate(
            ['id' => $id],
            ['name' => "Nation {$id}", 'code' => str_pad((string) $id, 2, '0', STR_PAD_LEFT)]
        );
    }
});

test('profile page is displayed', function () {
    $user = User::factory()->create(['privacy_policy_accepted_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create(['privacy_policy_accepted_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'gender' => 'notsay',
            'birthday' => '1990-01-01',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create(['privacy_policy_accepted_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
            'gender' => 'notsay',
            'birthday' => '1990-01-01',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create(['privacy_policy_accepted_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create(['privacy_policy_accepted_at' => now()]);

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test('incomplete athlete profile redirects to profile and minor birthday starts approval flow', function () {
    $role = Role::create(['name' => 'athlete', 'prefix' => 'athlete', 'label' => 'athlete']);
    $user = User::factory()->create([
        'profile_completed' => false,
        'privacy_policy_accepted_at' => now(),
    ]);
    $user->roles()->sync($role->id);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect('/profile');

    $this->patch('/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'gender' => 'notsay',
        'birthday' => now()->subYears(16)->toDateString(),
    ])->assertRedirect('/profile');

    $user->refresh();

    $this->assertTrue($user->profile_completed);
    $this->assertTrue($user->is_user_minor);
    $this->assertFalse($user->has_user_uploaded_documents);
    $this->assertFalse($user->has_admin_approved_minor);
});

test('incomplete non-athlete profile redirects to profile and requires birthday', function () {
    $role = Role::create(['name' => 'admin', 'prefix' => 'admin', 'label' => 'admin']);
    $user = User::factory()->create([
        'profile_completed' => false,
        'privacy_policy_accepted_at' => now(),
    ]);
    $user->roles()->sync($role->id);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect('/profile');

    $this->patch('/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'gender' => 'notsay',
    ])->assertSessionHasErrors('birthday');

    $this->patch('/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'gender' => 'notsay',
        'birthday' => '1990-01-01',
    ])->assertRedirect('/profile');

    $this->assertTrue($user->refresh()->profile_completed);
});

test('approved minor completing profile keeps approval and document flags', function () {
    $role = Role::create(['name' => 'athlete', 'prefix' => 'athlete', 'label' => 'athlete']);
    $user = User::factory()->create([
        'profile_completed' => false,
        'is_user_minor' => true,
        'has_user_uploaded_documents' => true,
        'has_admin_approved_minor' => true,
        'uploaded_documents_path' => '/users/1/approval_documents/test.pdf',
        'birthday' => now()->subYears(16)->toDateString(),
        'privacy_policy_accepted_at' => now(),
    ]);
    $user->roles()->sync($role->id);

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'gender' => 'male',
            'birthday' => now()->subYears(16)->toDateString(),
        ])
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertTrue($user->profile_completed);
    $this->assertTrue($user->is_user_minor);
    $this->assertTrue($user->has_user_uploaded_documents);
    $this->assertTrue($user->has_admin_approved_minor);
    $this->assertSame('/users/1/approval_documents/test.pdf', $user->uploaded_documents_path);
});

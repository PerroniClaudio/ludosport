<?php

use App\Models\Event;
use App\Models\Nation;
use App\Models\Rank;
use App\Models\Role;
use App\Models\User;
use App\Http\Middleware\EnsureMinorUserIsApproved;
use App\Http\Middleware\EnsurePrivacyPolicyAccepted;
use App\Http\Middleware\EnsureRoleAndInstitutionSelectedMiddleware;
use App\Http\Middleware\UserRoleMiddleware;
use Carbon\Carbon;

beforeEach(function () {
    config()->set('scout.driver', null);
});

function createAdminUser(): User
{
    Rank::create(['name' => 'Novice']);

    $nation = Nation::create([
        'name' => 'Italy',
        'code' => 'IT',
    ]);

    $adminRole = Role::create([
        'name' => 'admin',
        'prefix' => 'admin',
        'label' => 'admin',
    ]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'nation_id' => $nation->id,
    ]);

    $user->roles()->sync([$adminRole->id]);

    return $user;
}

function createPublishedEvent(int $userId): Event
{
    return Event::create([
        'name' => 'Original Event Name',
        'slug' => 'original-event-name',
        'description' => '<p>Event description</p>',
        'start_date' => Carbon::parse('2026-05-10 10:00:00'),
        'end_date' => Carbon::parse('2026-05-11 18:00:00'),
        'location' => 'Rome',
        'user_id' => $userId,
        'is_approved' => true,
        'is_published' => true,
        'event_type' => 1,
        'price' => 10,
        'block_subscriptions' => false,
    ]);
}

test('admin can update the name of a published event', function () {
    $this->withoutMiddleware([
        EnsurePrivacyPolicyAccepted::class,
        EnsureMinorUserIsApproved::class,
        EnsureRoleAndInstitutionSelectedMiddleware::class,
        UserRoleMiddleware::class,
    ]);

    $admin = createAdminUser();
    $event = createPublishedEvent($admin->id);

    $response = $this
        ->actingAs($admin)
        ->withSession(['role' => 'admin'])
        ->post(route('events.update', $event), [
            'name' => 'Updated Published Event Name',
            'block_subscriptions' => 'on',
        ]);

    $response->assertOk()->assertJson([
        'error' => false,
        'message' => 'Event saved successfully',
    ]);

    $event->refresh();

    expect($event->name)->toBe('Updated Published Event Name');
    expect((bool) $event->block_subscriptions)->toBeTrue();
});

test('admin still cannot update other locked fields of a published event', function () {
    $this->withoutMiddleware([
        EnsurePrivacyPolicyAccepted::class,
        EnsureMinorUserIsApproved::class,
        EnsureRoleAndInstitutionSelectedMiddleware::class,
        UserRoleMiddleware::class,
    ]);

    $admin = createAdminUser();
    $event = createPublishedEvent($admin->id);

    $response = $this
        ->actingAs($admin)
        ->withSession(['role' => 'admin'])
        ->post(route('events.update', $event), [
            'name' => 'Original Event Name',
            'start_date' => '2026-06-01 09:00:00',
        ]);

    $response->assertOk()->assertJson([
        'error' => true,
        'message' => 'After approval, you can only modify "block subscriptions" and the event name once it is published',
    ]);

    $event->refresh();

    expect($event->start_date->format('Y-m-d H:i:s'))->toBe('2026-05-10 10:00:00');
});

<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Fee;
use App\Models\Rank;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EventPurchaseTestSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'athlete')->firstOrFail();
        $rank = Rank::firstOrFail();
        $academy = Academy::where('is_disabled', false)
            ->where('id', '!=', 1)
            ->orderBy('id')
            ->firstOrFail();
        $school = School::where('academy_id', $academy->id)
            ->where('is_disabled', false)
            ->orderBy('id')
            ->firstOrFail();
        $eventType = EventType::firstOrFail();

        $user = User::updateOrCreate(
            ['email' => 'username@test2.com'],
            [
                'name' => 'Test',
                'surname' => 'Athlete',
                'email_verified_at' => now(),
                'password' => Hash::make('Password@2026'),
                'subscription_year' => now()->year,
                'academy_id' => $academy->id,
                'school_id' => $school->id,
                'nation_id' => $academy->nation_id,
                'rank_id' => $rank->id,
                'gender' => 'notsay',
                'birthday' => '1995-01-01',
                'has_paid_fee' => true,
                'fee_payment_date' => now(),
                'fee_expires_at' => now()->endOfYear(),
                'is_user_minor' => false,
                'has_user_uploaded_documents' => false,
                'has_admin_approved_minor' => false,
                'has_to_switch_from_minor' => false,
                'uploaded_documents_path' => null,
                'privacy_policy_accepted_at' => now(),
            ]
        );

        $user->roles()->sync([$role->id]);
        $user->academyAthletes()->sync([$academy->id => ['is_primary' => true]]);
        $user->schoolAthletes()->sync([$school->id => ['is_primary' => true]]);

        $fee = Fee::updateOrCreate(
            ['user_id' => $user->id, 'academy_id' => $academy->id, 'used' => true],
            [
                'type' => 3,
                'start_date' => now()->startOfDay(),
                'end_date' => now()->addYear()->endOfYear()->setMonth(8)->setDay(31)->endOfDay(),
                'auto_renew' => false,
                'unique_id' => (string) Str::orderedUuid(),
                'is_admin_generated' => true,
            ]
        );

        $user->forceFill([
            'active_fee_id' => $fee->id,
        ])->save();

        $startDate = now()->addDays(14)->setTime(10, 0);
        $endDate = (clone $startDate)->addHours(8);

        Event::updateOrCreate(
            ['slug' => 'event-purchase-test-user-' . $user->id],
            [
                'name' => 'Event Purchase Test 2',
                'description' => '<p>Seeder event for purchase flow testing.</p>',
                'user_id' => $user->id,
                'is_approved' => true,
                'is_published' => true,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'location' => '',
                'nation_id' => $academy->nation_id,
                'academy_id' => $academy->id,
                'school_id' => $school->id,
                'city' => $academy->city,
                'address' => $academy->address,
                'postal_code' => $academy->zip,
                'event_type' => $eventType->id,
                'price' => 10,
                'max_participants' => null,
                'block_subscriptions' => false,
                'waiting_list_close_date' => null,
                'internal_shop' => true,
                'year' => Event::calculateEventYear($startDate),
            ]
        );
    }
}

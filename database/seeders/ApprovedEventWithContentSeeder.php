<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Nation;
use App\Models\Rank;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApprovedEventWithContentSeeder extends Seeder
{
    public function run(): void
    {
        $nation = Nation::updateOrCreate(
            ['code' => 'IT'],
            [
                'name' => 'Italy',
                'flag' => null,
                'continent' => 'Europe',
            ]
        );

        $academy = Academy::updateOrCreate(
            ['slug' => 'seed-academy-approved-event'],
            [
                'name' => 'Seed Academy Approved Event',
                'nation_id' => $nation->id,
                'address' => 'Via dello Sport 10',
                'city' => 'Roma',
                'state' => 'Lazio',
                'zip' => '00100',
                'country' => 'Italy',
                'coordinates' => null,
                'email' => 'academy-approved-event@example.com',
                'picture' => null,
                'main_rector' => null,
            ]
        );

        $school = School::updateOrCreate(
            ['slug' => 'seed-school-approved-event'],
            [
                'name' => 'Seed School Approved Event',
                'academy_id' => $academy->id,
                'nation_id' => $nation->id,
                'address' => 'Via della Sala 25',
                'city' => 'Roma',
                'state' => 'Lazio',
                'zip' => '00100',
                'country' => 'Italy',
                'coordinates' => null,
                'email' => 'school-approved-event@example.com',
                'main_dean' => null,
            ]
        );

        $eventType = EventType::updateOrCreate(
            ['name' => 'School Tournament'],
            ['is_disabled' => false]
        );

        $rank = Rank::firstOrCreate(['name' => 'Novice']);

        $user = User::updateOrCreate(
            ['email' => 'approved-event-seeder@example.com'],
            [
                'name' => 'Event',
                'surname' => 'Seeder',
                'email_verified_at' => now(),
                'password' => Hash::make('Password@2026'),
                'subscription_year' => now()->year,
                'nation_id' => $nation->id,
                'academy_id' => $academy->id,
                'school_id' => $school->id,
                'rank_id' => $rank->id,
                'gender' => 'notsay',
                'battle_name' => 'EventSeeder10',
                'privacy_policy_accepted_at' => now(),
                'unique_code' => 'SEED-EVNT-APRV-0001',
            ]
        );

        $startDate = now()->addDays(21)->setTime(10, 0);
        $endDate = (clone $startDate)->addHours(8);
        $slug = 'approved-event-with-content';

        Event::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => 'Evento approvato con contenuti e luogo',
                'slug' => $slug,
                'description' => implode('', [
                    '<h2>Programma della giornata</h2>',
                    '<p>Evento demo creato dal seeder con contenuti HTML gia valorizzati.</p>',
                    '<p>Include check-in, sessioni pratiche, pausa pranzo e saluti finali.</p>',
                    '<ul>',
                    '<li>Apertura accrediti alle 09:30</li>',
                    '<li>Allenamento tecnico dalle 10:00</li>',
                    '<li>Torneo interno nel pomeriggio</li>',
                    '</ul>',
                ]),
                'thumbnail' => null,
                'user_id' => $user->id,
                'is_approved' => true,
                'is_published' => true,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'location' => 'Palazzetto dello Sport di Roma',
                'nation_id' => $nation->id,
                'academy_id' => $academy->id,
                'school_id' => $school->id,
                'city' => 'Roma',
                'address' => 'Via dello Sport 10',
                'postal_code' => '00100',
                'event_type' => $eventType->id,
                'price' => 0,
                'weapon_form_id' => null,
                'max_participants' => null,
                'block_subscriptions' => false,
                'waiting_list_close_date' => null,
                'internal_shop' => false,
                'year' => Event::calculateEventYear($startDate),
            ]
        );
    }
}

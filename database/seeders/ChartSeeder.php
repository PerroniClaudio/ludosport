<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use \App\Models\Event;
use App\Models\EventResult;

class ChartSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        //

        /*

        \App\Models\Academy::factory(10)->create();
        \App\Models\School::factory(10)->create();

        Event::factory()
            ->count(35)
            ->create([
                'is_approved' => true,
                'is_published' => true,
            ]);

        

        User::factory()
            ->count(64)
            ->create()
            ->each(function ($user) {
                $user->schoolAthletes()->attach(rand(1, 10));
                $user->academyAthletes()->attach(rand(1, 10));
                $user->roles()->attach(7);
            });

        */



        Event::all()->each(function ($event) {

            for ($i = 1; $i < 65; $i++) {

                $user = User::inRandomOrder()->first();

                $pointsEarned = round((64 - $i + 1) * $event->eventMultiplier(), 0, PHP_ROUND_HALF_UP);


                switch ($i) {
                    case 1:
                        $bonus_war_points = $event->eventBonusPoints("FIRST_IN_WAR");
                        break;
                    case 2:
                        $bonus_war_points = $event->eventBonusPoints("SECOND_IN_WAR");
                        break;
                    case 3:
                        $bonus_war_points = $event->eventBonusPoints("THIRD_IN_WAR");
                        break;
                    default:
                        $bonus_war_points = 0;
                        break;
                }

                if ($bonus_war_points === null) {
                    $bonus_war_points = 0;
                }

                EventResult::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'war_points' => $pointsEarned,
                    'style_points' => $pointsEarned,
                    'bonus_war_points' => $bonus_war_points,
                    'bonus_style_points' => $bonus_war_points,
                    'total_war_points' => $pointsEarned + $bonus_war_points,
                    'total_style_points' => $pointsEarned + $bonus_war_points,
                ]);
            }
        });
    }
}

<?php

namespace App\Jobs;

use App\Mail\ReachedAdultAgeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckUsersComingOfAgeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $adultThreshold = Carbon::now()->subYears(18)->startOfDay();

        $users = User::query()
            ->where('is_user_minor', true)
            ->where('has_to_switch_from_minor', false)
            ->whereNotNull('birthday')
            ->whereDate('birthday', '<=', $adultThreshold->toDateString())
            ->get();

        if ($users->isEmpty()) {
            Log::info('No users found for CheckUsersComingOfAgeJob');

            return;
        }

        foreach ($users as $user) {
            $user->forceFill([
                'has_to_switch_from_minor' => true,
            ])->save();

            Mail::to($user->email)->send(new ReachedAdultAgeMail($user));

            Log::info('User flagged for adult account verification after reaching 18 years old', [
                'user_id' => $user->id,
            ]);
        }
    }
}

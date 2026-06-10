<?php

use App\Http\Middleware\EnsureMinorUserIsApproved;
use App\Http\Middleware\EnsurePrivacyPolicyAccepted;
use App\Http\Middleware\EnsureRoleAndInstitutionSelectedMiddleware;
use App\Http\Middleware\UserRoleMiddleware;
use App\Models\Chart;
use App\Models\Nation;
use App\Models\Rank;
use App\Models\Role;
use App\Models\User;

function createAdminForChartTest(): User
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

it('shows the chart with the most recent created_at without sorting the whole table in PHP', function () {
    $this->withoutMiddleware([
        EnsurePrivacyPolicyAccepted::class,
        EnsureMinorUserIsApproved::class,
        EnsureRoleAndInstitutionSelectedMiddleware::class,
        UserRoleMiddleware::class,
    ]);

    $admin = createAdminForChartTest();

    $olderChart = Chart::create([
        'note' => 'Older chart',
        'data' => [['rank' => 1, 'user_name' => 'Older User']],
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $newerChart = Chart::create([
        'note' => 'Newer chart',
        'data' => [['rank' => 1, 'user_name' => 'Newer User']],
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Chart::create([
        'note' => 'Backfilled chart',
        'data' => [['rank' => 1, 'user_name' => 'Backfilled User']],
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ]);

    $response = $this
        ->actingAs($admin)
        ->withSession(['role' => 'admin'])
        ->get(route('rankings.index'));

    $response->assertOk();
    $response->assertViewHas('chart', fn (Chart $chart) => $chart->is($newerChart));
    $response->assertViewHas('chart_data', $newerChart->data);

    expect($newerChart->isNot($olderChart))->toBeTrue();
});

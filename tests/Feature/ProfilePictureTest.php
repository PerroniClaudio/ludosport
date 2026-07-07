<?php

use App\Models\Nation;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('minor profile picture falls back to placeholder for guest viewers', function () {
    Rank::create(['name' => 'Novice']);
    foreach (range(1, 10) as $id) {
        Nation::create([
            'id' => $id,
            'name' => "Nation {$id}",
            'code' => str_pad((string) $id, 2, '0', STR_PAD_LEFT),
        ]);
    }

    Http::fake([
        'https://ui-avatars.com/*' => Http::response('fake-image', 200, ['Content-Type' => 'image/png']),
    ]);

    $user = User::factory()->create([
        'is_user_minor' => true,
        'profile_picture' => 'users/profile-picture.png',
    ]);

    $response = $this->get(route('profile-picture', $user));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');
    expect($response->getContent())->toBe('fake-image');
});

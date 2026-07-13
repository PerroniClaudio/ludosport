<?php

use App\Models\Nation;
use App\Models\PrivacyPolicy;
use App\Models\Rank;
use App\Models\User;

test('activate membership page exposes invoice form alpine methods', function () {
    Rank::firstOrCreate(['name' => 'Novice']);
    PrivacyPolicy::getOrCreate();
    foreach (range(1, 10) as $id) {
        Nation::firstOrCreate(
            ['id' => $id],
            ['name' => "Nation {$id}", 'code' => str_pad((string) $id, 2, '0', STR_PAD_LEFT)]
        );
    }

    $user = User::factory()->create([
        'has_paid_fee' => 0,
        'is_user_minor' => true,
        'has_admin_approved_minor' => true,
        'profile_completed' => true,
        'privacy_policy_accepted_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('shop-activate-membership'));

    $response->assertOk();
    $response->assertSee('validateInvoiceForm()', false);
    $response->assertSee('markInvoiceDirty()', false);
    $response->assertSee('invoiceSaved', false);
});

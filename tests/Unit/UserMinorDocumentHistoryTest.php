<?php

namespace Tests\Unit;

use App\Models\Nation;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserMinorDocumentHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_replacing_a_minor_document_archives_the_previous_one(): void
    {
        config(['scout.driver' => 'null']);

        $nation = Nation::create([
            'name' => 'Italy',
            'code' => 'IT',
        ]);
        $rank = Rank::create(['name' => 'Student']);

        $user = User::factory()->create([
            'nation_id' => $nation->id,
            'rank_id' => $rank->id,
            'is_user_minor' => true,
            'has_user_uploaded_documents' => true,
            'has_admin_approved_minor' => true,
            'uploaded_documents_path' => '/users/1/approval_documents/old-document.pdf',
        ]);

        $user->replaceMinorApprovalDocument('/users/1/approval_documents/new-document.pdf');
        $user->save();
        $user->refresh();

        $this->assertSame('/users/1/approval_documents/new-document.pdf', $user->uploaded_documents_path);
        $this->assertTrue($user->has_user_uploaded_documents);
        $this->assertDatabaseHas('user_document_histories', [
            'user_id' => $user->id,
            'document_path' => '/users/1/approval_documents/old-document.pdf',
            'was_admin_approved' => true,
        ]);
    }
}

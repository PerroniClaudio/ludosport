<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\Rank;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MinorDocumentHistoryUiTestSeeder extends Seeder
{
    public function run(): void
    {
        $nation = Nation::firstOrCreate(
            ['code' => 'IT'],
            ['name' => 'Italy']
        );

        $rectorRole = Role::firstOrCreate(
            ['name' => 'rector'],
            ['prefix' => 'rector', 'label' => 'rector']
        );

        $athleteRole = Role::firstOrCreate(
            ['name' => 'athlete'],
            ['prefix' => 'athlete', 'label' => 'athlete']
        );

        $rank = Rank::firstOrCreate(['name' => 'Novice']);

        $academy = Academy::withoutSyncingToSearch(function () use ($nation) {
            return Academy::updateOrCreate(
                ['slug' => 'minor-doc-history-ui-academy'],
                [
                    'name' => 'Minor Docs UI Academy',
                    'nation_id' => $nation->id,
                ]
            );
        });

        $school = School::withoutSyncingToSearch(function () use ($academy, $nation) {
            return School::updateOrCreate(
                ['slug' => 'minor-doc-history-ui-school'],
                [
                    'name' => 'Minor Docs UI School',
                    'academy_id' => $academy->id,
                    'nation_id' => $nation->id,
                ]
            );
        });

        $rector = User::withoutSyncingToSearch(function () use ($academy, $school, $nation, $rank) {
            return User::updateOrCreate(
                ['email' => 'rector.minor-docs-ui@ludosport.test'],
                [
                    'name' => 'Rettore',
                    'surname' => 'Documenti',
                    'password' => Hash::make('Rector@2026'),
                    'email_verified_at' => now(),
                    'subscription_year' => 2026,
                    'academy_id' => $academy->id,
                    'school_id' => $school->id,
                    'nation_id' => $nation->id,
                    'rank_id' => $rank->id,
                    'gender' => 'notsay',
                    'birthday' => '1985-05-12',
                    'is_user_minor' => false,
                    'has_user_uploaded_documents' => false,
                    'has_admin_approved_minor' => false,
                    'uploaded_documents_path' => null,
                ]
            );
        });

        $minorUser = User::withoutSyncingToSearch(function () use ($academy, $school, $nation, $rank) {
            return User::updateOrCreate(
                ['email' => 'minor.document-history-ui@ludosport.test'],
                [
                    'name' => 'Atleta',
                    'surname' => 'Minorenne',
                    'password' => Hash::make('Athlete@2026'),
                    'email_verified_at' => now(),
                    'subscription_year' => 2026,
                    'academy_id' => $academy->id,
                    'school_id' => $school->id,
                    'nation_id' => $nation->id,
                    'rank_id' => $rank->id,
                    'gender' => 'notsay',
                    'birthday' => '2011-09-21',
                    'is_user_minor' => true,
                    'has_user_uploaded_documents' => true,
                    'has_admin_approved_minor' => false,
                ]
            );
        });

        $rector->roles()->syncWithoutDetaching([$rectorRole->id]);
        $rector->academies()->syncWithoutDetaching([$academy->id]);
        $rector->setPrimaryAcademy($academy->id);

        $academy->main_rector = $rector->id;
        $academy->save();

        $minorUser->roles()->syncWithoutDetaching([$athleteRole->id]);
        $minorUser->academyAthletes()->syncWithoutDetaching([$academy->id]);
        $minorUser->schoolAthletes()->syncWithoutDetaching([$school->id]);
        $minorUser->setPrimaryAcademyAthlete($academy->id);
        $minorUser->setPrimarySchoolAthlete($school->id);

        $currentPath = "/users/{$minorUser->id}/approval_documents/current-minor-approval.pdf";
        $historyPathOne = "/users/{$minorUser->id}/approval_documents/history-1-minor-approval.pdf";
        $historyPathTwo = "/users/{$minorUser->id}/approval_documents/history-2-minor-approval.pdf";

        $this->storeFakePdf($currentPath, 'Current minor approval document');
        $this->storeFakePdf($historyPathOne, 'Archived minor approval document 1');
        $this->storeFakePdf($historyPathTwo, 'Archived minor approval document 2');

        $minorUser->uploaded_documents_path = $currentPath;
        $minorUser->has_user_uploaded_documents = true;
        $minorUser->has_admin_approved_minor = false;
        $minorUser->save();

        $minorUser->minorDocumentHistories()->delete();
        $minorUser->minorDocumentHistories()->createMany([
            [
                'document_path' => $historyPathOne,
                'was_admin_approved' => true,
                'archived_at' => now()->subDays(14),
            ],
            [
                'document_path' => $historyPathTwo,
                'was_admin_approved' => false,
                'archived_at' => now()->subDays(3),
            ],
        ]);
    }

    private function storeFakePdf(string $path, string $title): void
    {
        Storage::disk('gcs')->put(
            ltrim($path, '/'),
            $this->fakePdfContent($title)
        );
    }

    private function fakePdfContent(string $title): string
    {
        $safeTitle = str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            Str::limit($title, 60, '')
        );

        $stream = "BT\n/F1 14 Tf\n20 90 Td\n({$safeTitle}) Tj\nET";

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Count 1 /Kids [3 0 R] >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 300 144] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            4 => "<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf('%010d 00000 n ', $offset)."\n";
        }

        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}

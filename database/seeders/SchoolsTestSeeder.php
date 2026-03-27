<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SchoolsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academies = Academy::query()
            ->where('is_disabled', '0')
            ->where('id', '!=', 1)
            ->orderBy('id')
            ->take(6)
            ->get();

        if ($academies->isEmpty()) {
            $this->command?->warn('No academies available. Seed academies first.');
            return;
        }

        foreach ($academies as $academyIndex => $academy) {
            for ($schoolNumber = 1; $schoolNumber <= 4; $schoolNumber++) {
                $sequence = $academyIndex + 1;
                $name = sprintf('Test School %02d-%02d', $sequence, $schoolNumber);
                $slug = Str::slug($name);

                School::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'nation_id' => $academy->nation_id,
                        'academy_id' => $academy->id,
                        'address' => 'Via Test '.$schoolNumber,
                        'city' => 'Citta Test '.$sequence,
                        'state' => 'Provincia Test',
                        'zip' => sprintf('10%03d', ($sequence * 10) + $schoolNumber),
                        'country' => $academy->nation?->name,
                        'email' => sprintf('test-school-%02d-%02d@example.com', $sequence, $schoolNumber),
                    ]
                );
            }
        }

        $this->command?->info('Test schools seeded successfully.');
    }
}

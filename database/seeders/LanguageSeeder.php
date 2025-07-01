<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        //

        $languages = [
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'Spanish', 'code' => 'es'],
            ['name' => 'French', 'code' => 'fr'],
            ['name' => 'German', 'code' => 'de'],
            ['name' => 'Italian', 'code' => 'it'],
            ['name' => 'Portuguese', 'code' => 'pt'],
            ['name' => 'Russian', 'code' => 'ru'],
            ['name' => 'Japanese', 'code' => 'ja'],
            ['name' => 'Chinese', 'code' => 'zh'],
            ['name' => 'Korean', 'code' => 'ko'],
            ['name' => 'Arabic', 'code' => 'ar'],
            ['name' => 'Hindi', 'code' => 'hi']
        ];

        foreach ($languages as $language) {
            \App\Models\Language::create([
                'name' => $language['name'],
                'code' => $language['code'],
            ]);
        }
    }
}

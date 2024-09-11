<?php

namespace Database\Seeders;

use App\Models\Academy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AcademySeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        //
        if(!Academy::where('slug', 'no-academy')->exists()) {
            Academy::create([
                'name' => 'No academy',
                'slug' => 'no-academy',
                'nation_id' => 1,
            ]);
        }

        $filePath = database_path('data/academies.json');

        if (!File::exists($filePath)) {
            Log::error("File not found: $filePath");
            return;
        }

        // Leggi il contenuto del file JSON
        $academiesJson = File::get(database_path('data/academies.json'));

        // Decodifica il JSON in un array associativo
        $academies = json_decode($academiesJson, true);

        // Inserisci i dati nel database
        foreach ($academies as $academy) {
            
            $slug = Str::slug($academy['name']);
            
            $address = $academy['address'] . " " . $academy['city'] . " "  . $academy['zip'];
            $location = $this->getLocation($address);

            $nation = \App\Models\Nation::where('name', $academy['country'])->first();
            if (!$nation && ($location != null)) {
                $nation = \App\Models\Nation::where('name', $location['country'])->first();
            }

            $coordinates = $location ? json_encode(['lat' => $location['lat'], 'lng' => $location['lng']]) : null;

            if(!Academy::where('slug', $slug)->exists()) {
                Academy::create([
                    'name' => $academy['name'],
                    'nation_id' => $nation->id ?? 1,
                    'slug' => $slug,
                    'address' => $academy['address'] ?? null,
                    'city' => $academy['city'] ?? null,
                    'state' => $academy['state'] ?? null, //provincia
                    'zip' => $academy['zip'] ?? null,
                    'country' => $academy['country'] ?? null,
                    'coordinates' => $coordinates
                ]);
            }

        }
    }

    private function getLocation($address) {

        $address = str_replace(" ", "+", $address);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=" . env('MAPS_GOOGLE_MAPS_ACCESS_TOKEN');
        $response = file_get_contents($url);
        $json = json_decode($response, true);

        if ($json['status'] == 'ZERO_RESULTS') {
            return null;
        }

        $addressComponents = $json['results'][0]['address_components'];
        $city = "";
        if(isset($addressComponents[2])){
            $city = $addressComponents[2]['types'][0] == "route" ? ($addressComponents[3]['long_name'] ?? "") : $addressComponents[2]['long_name'];
        }
        return [
            'lat' => $json['results'][0]['geometry']['location']['lat'],
            'lng' => $json['results'][0]['geometry']['location']['lng'],
            'city' => $city,
            'state' => $addressComponents[5]['long_name'] ?? "",
            'country' => $addressComponents[6]['long_name']  ?? "",
        ];
    }
}

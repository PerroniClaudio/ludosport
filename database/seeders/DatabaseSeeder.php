<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\School;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {

        // Roles ---------------------------------------------------------------------------------

        $roles = [
            [
                'name' => 'admin',
                'prefix' => 'admin',
                'label' => 'admin',
            ],
            [
                'name' => 'rector',
                'prefix' => 'rector',
                'label' => 'rector',
            ],
            [
                'name' => 'dean',
                'prefix' => 'dean',
                'label' => 'dean',
            ],
            [
                'name' => 'manager',
                'prefix' => 'manager',
                'label' => 'manager',
            ],
            [
                'name' => 'technician',
                'prefix' => 'technician',
                'label' => 'technician',
            ],
            [
                'name' => 'instructor',
                'prefix' => 'instructor',
                'label' => 'instructor',
            ],
            [
                'name' => 'athlete',
                'prefix' => 'athlete',
                'label' => 'athlete',
            ],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::factory()->create($role);
        }

        // Rank ---------------------------------------------------------------------------------

        $ranks = [
            'Novice',
            'Initiate',
            'Academic',
            'Chevalier',
        ];

        foreach ($ranks as $rank) {
            \App\Models\Rank::create([
                'name' => $rank,
            ]);
        }

        // Event Types ---------------------------------------------------------------------------------

        $event_types = [
            [
                'name' => 'Other Event',
            ],
            [
                'name' => 'School Tournament',
            ],
            [
                'name' => 'Academy Tournament',
            ],
            [
                'name' => 'National Tournament',
            ],
            [
                'name' => 'Training Course',
            ],
        ];

        foreach ($event_types as $event_type) {
            \App\Models\EventType::create($event_type);
        }

        // Weapon forms ---------------------------------------------------------------------------------

        $weaponForms = [
            [
                'name' => 'Form 1',
                'image' => '/weapon-forms/form_1.webp'
            ],
            [
                'name' => 'Form 2',
                'image' => '/weapon-forms/form_2.webp'
            ],
            [
                'name' => 'Form Y',
                'image' => '/weapon-forms/form_y.webp'
            ],
            [
                'name' => 'Form 3 Long Saber',
                'image' => '/weapon-forms/long_saber/form_3.webp'
            ],
            [
                'name' => 'Form 4 Long Saber',
                'image' => '/weapon-forms/long_saber/form_4.webp'
            ],
            [
                'name' => 'Form 5 Long Saber',
                'image' => '/weapon-forms/long_saber/form_5.webp'
            ],
            [
                'name' => 'Form 3 Dual Sabers',
                'image' => '/weapon-forms/dual_saber/form_3.webp'
            ],
            [
                'name' => 'Form 4 Dual Sabers',
                'image' => '/weapon-forms/dual_saber/form_4.webp'
            ],
            [
                'name' => 'Form 5 Dual Sabers',
                'image' => '/weapon-forms/dual_saber/form_5.webp'
            ],
            [
                'name' => 'Form 3 Saberstaff',
                'image' => '/weapon-forms/saberstaff/form_3.webp'
            ],
            [
                'name' => 'Form 4 Saberstaff',
                'image' => '/weapon-forms/saberstaff/form_4.webp'
            ],
            [
                'name' => 'Form 5 Saberstaff',
                'image' => '/weapon-forms/saberstaff/form_5.webp'
            ],
        ];

        foreach ($weaponForms as $weaponForm) {
            \App\Models\WeaponForm::create([
                'name' => $weaponForm['name'],
                'image' => $weaponForm['image']
            ]);
        }

        // Languages ---------------------------------------------------------------------------------

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

        // Nations ---------------------------------------------------------------------------------

        $this->populateCountries();

        // Academies ---------------------------------------------------------------------------------

        $this->populateAcademies();

        // Schools ---------------------------------------------------------------------------------

        $this->populateSchools();

        // Users ---------------------------------------------------------------------------------

        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('LudoSport@2024'),
        ]);

        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $user->roles()->syncWithoutDetaching($adminRole->id);
    }

    // ________________________________________________________________________________________________
    // ________________________________________________________________________________________________
    // Functions 

    // Countries / Nations ---------------------------------------------------------------------------------
    private function populateCountries() {

        $countries = [
            "AF" => ["country" => "Afghanistan", "continent" => "Asia"],
            "AX" => ["country" => "Åland Islands", "continent" => "Europe"],
            "AL" => ["country" => "Albania", "continent" => "Europe"],
            "DZ" => ["country" => "Algeria", "continent" => "Africa"],
            "AS" => ["country" => "American Samoa", "continent" => "Oceania"],
            "AD" => ["country" => "Andorra", "continent" => "Europe"],
            "AO" => ["country" => "Angola", "continent" => "Africa"],
            "AI" => ["country" => "Anguilla", "continent" => "North America"],
            "AQ" => ["country" => "Antarctica", "continent" => "Antarctica"],
            "AG" => ["country" => "Antigua and Barbuda", "continent" => "North America"],
            "AR" => ["country" => "Argentina", "continent" => "South America"],
            "AM" => ["country" => "Armenia", "continent" => "Asia"],
            "AW" => ["country" => "Aruba", "continent" => "North America"],
            "AU" => ["country" => "Australia", "continent" => "Oceania"],
            "AT" => ["country" => "Austria", "continent" => "Europe"],
            "AZ" => ["country" => "Azerbaijan", "continent" => "Asia"],
            "BS" => ["country" => "Bahamas", "continent" => "North America"],
            "BH" => ["country" => "Bahrain", "continent" => "Asia"],
            "BD" => ["country" => "Bangladesh", "continent" => "Asia"],
            "BB" => ["country" => "Barbados", "continent" => "North America"],
            "BY" => ["country" => "Belarus", "continent" => "Europe"],
            "BE" => ["country" => "Belgium", "continent" => "Europe"],
            "BZ" => ["country" => "Belize", "continent" => "North America"],
            "BJ" => ["country" => "Benin", "continent" => "Africa"],
            "BM" => ["country" => "Bermuda", "continent" => "North America"],
            "BT" => ["country" => "Bhutan", "continent" => "Asia"],
            "BO" => ["country" => "Bolivia", "continent" => "South America"],
            "BA" => ["country" => "Bosnia and Herzegovina", "continent" => "Europe"],
            "BW" => ["country" => "Botswana", "continent" => "Africa"],
            "BV" => ["country" => "Bouvet Island", "continent" => "Antarctica"],
            "BR" => ["country" => "Brazil", "continent" => "South America"],
            "IO" => ["country" => "British Indian Ocean Territory", "continent" => "Asia"],
            "BN" => ["country" => "Brunei Darussalam", "continent" => "Asia"],
            "BG" => ["country" => "Bulgaria", "continent" => "Europe"],
            "BF" => ["country" => "Burkina Faso", "continent" => "Africa"],
            "BI" => ["country" => "Burundi", "continent" => "Africa"],
            "KH" => ["country" => "Cambodia", "continent" => "Asia"],
            "CM" => ["country" => "Cameroon", "continent" => "Africa"],
            "CA" => ["country" => "Canada", "continent" => "North America"],
            "CV" => ["country" => "Cape Verde", "continent" => "Africa"],
            "KY" => ["country" => "Cayman Islands", "continent" => "North America"],
            "CF" => ["country" => "Central African Republic", "continent" => "Africa"],
            "TD" => ["country" => "Chad", "continent" => "Africa"],
            "CL" => ["country" => "Chile", "continent" => "South America"],
            "CN" => ["country" => "China", "continent" => "Asia"],
            "CX" => ["country" => "Christmas Island", "continent" => "Asia"],
            "CC" => ["country" => "Cocos (Keeling] Islands", "continent" => "Asia"],
            "CO" => ["country" => "Colombia", "continent" => "South America"],
            "KM" => ["country" => "Comoros", "continent" => "Africa"],
            "CG" => ["country" => "Congo", "continent" => "Africa"],
            "CD" => ["country" => "The Democratic Republic of The Congo", "continent" => "Africa"],
            "CK" => ["country" => "Cook Islands", "continent" => "Oceania"],
            "CR" => ["country" => "Costa Rica", "continent" => "North America"],
            "CI" => ["country" => "Cote D'ivoire", "continent" => "Africa"],
            "HR" => ["country" => "Croatia", "continent" => "Europe"],
            "CU" => ["country" => "Cuba", "continent" => "North America"],
            "CY" => ["country" => "Cyprus", "continent" => "Asia"],
            "CZ" => ["country" => "Czech Republic", "continent" => "Europe"],
            "DK" => ["country" => "Denmark", "continent" => "Europe"],
            "DJ" => ["country" => "Djibouti", "continent" => "Africa"],
            "DM" => ["country" => "Dominica", "continent" => "North America"],
            "DO" => ["country" => "Dominican Republic", "continent" => "North America"],
            "EC" => ["country" => "Ecuador", "continent" => "South America"],
            "EG" => ["country" => "Egypt", "continent" => "Africa"],
            "SV" => ["country" => "El Salvador", "continent" => "North America"],
            "GQ" => ["country" => "Equatorial Guinea", "continent" => "Africa"],
            "ER" => ["country" => "Eritrea", "continent" => "Africa"],
            "EE" => ["country" => "Estonia", "continent" => "Europe"],
            "ET" => ["country" => "Ethiopia", "continent" => "Africa"],
            "FK" => ["country" => "Falkland Islands (Malvinas]", "continent" => "South America"],
            "FO" => ["country" => "Faroe Islands", "continent" => "Europe"],
            "FJ" => ["country" => "Fiji", "continent" => "Oceania"],
            "FI" => ["country" => "Finland", "continent" => "Europe"],
            "FR" => ["country" => "France", "continent" => "Europe"],
            "GF" => ["country" => "French Guiana", "continent" => "South America"],
            "PF" => ["country" => "French Polynesia", "continent" => "Oceania"],
            "TF" => ["country" => "French Southern Territories", "continent" => "Antarctica"],
            "GA" => ["country" => "Gabon", "continent" => "Africa"],
            "GM" => ["country" => "Gambia", "continent" => "Africa"],
            "GE" => ["country" => "Georgia", "continent" => "Asia"],
            "DE" => ["country" => "Germany", "continent" => "Europe"],
            "GH" => ["country" => "Ghana", "continent" => "Africa"],
            "GI" => ["country" => "Gibraltar", "continent" => "Europe"],
            "GR" => ["country" => "Greece", "continent" => "Europe"],
            "GL" => ["country" => "Greenland", "continent" => "North America"],
            "GD" => ["country" => "Grenada", "continent" => "North America"],
            "GP" => ["country" => "Guadeloupe", "continent" => "North America"],
            "GU" => ["country" => "Guam", "continent" => "Oceania"],
            "GT" => ["country" => "Guatemala", "continent" => "North America"],
            "GG" => ["country" => "Guernsey", "continent" => "Europe"],
            "GN" => ["country" => "Guinea", "continent" => "Africa"],
            "GW" => ["country" => "Guinea-bissau", "continent" => "Africa"],
            "GY" => ["country" => "Guyana", "continent" => "South America"],
            "HT" => ["country" => "Haiti", "continent" => "North America"],
            "HM" => ["country" => "Heard Island and Mcdonald Islands", "continent" => "Antarctica"],
            "VA" => ["country" => "Holy See (Vatican City State)", "continent" => "Europe"],
            "HN" => ["country" => "Honduras", "continent" => "North America"],
            "HK" => ["country" => "Hong Kong", "continent" => "Asia"],
            "HU" => ["country" => "Hungary", "continent" => "Europe"],
            "IS" => ["country" => "Iceland", "continent" => "Europe"],
            "IN" => ["country" => "India", "continent" => "Asia"],
            "ID" => ["country" => "Indonesia", "continent" => "Asia"],
            "IR" => ["country" => "Iran", "continent" => "Asia"],
            "IQ" => ["country" => "Iraq", "continent" => "Asia"],
            "IE" => ["country" => "Ireland", "continent" => "Europe"],
            "IM" => ["country" => "Isle of Man", "continent" => "Europe"],
            "IL" => ["country" => "Israel", "continent" => "Asia"],
            "IT" => ["country" => "Italy", "continent" => "Europe"],
            "JM" => ["country" => "Jamaica", "continent" => "North America"],
            "JP" => ["country" => "Japan", "continent" => "Asia"],
            "JE" => ["country" => "Jersey", "continent" => "Europe"],
            "JO" => ["country" => "Jordan", "continent" => "Asia"],
            "KZ" => ["country" => "Kazakhstan", "continent" => "Asia"],
            "KE" => ["country" => "Kenya", "continent" => "Africa"],
            "KI" => ["country" => "Kiribati", "continent" => "Oceania"],
            "KP" => ["country" => "Democratic People's Republic of Korea", "continent" => "Asia"],
            "KR" => ["country" => "Republic of Korea", "continent" => "Asia"],
            "KW" => ["country" => "Kuwait", "continent" => "Asia"],
            "KG" => ["country" => "Kyrgyzstan", "continent" => "Asia"],
            "LA" => ["country" => "Lao People's Democratic Republic", "continent" => "Asia"],
            "LV" => ["country" => "Latvia", "continent" => "Europe"],
            "LB" => ["country" => "Lebanon", "continent" => "Asia"],
            "LS" => ["country" => "Lesotho", "continent" => "Africa"],
            "LR" => ["country" => "Liberia", "continent" => "Africa"],
            "LY" => ["country" => "Libya", "continent" => "Africa"],
            "LI" => ["country" => "Liechtenstein", "continent" => "Europe"],
            "LT" => ["country" => "Lithuania", "continent" => "Europe"],
            "LU" => ["country" => "Luxembourg", "continent" => "Europe"],
            "MO" => ["country" => "Macao", "continent" => "Asia"],
            "MK" => ["country" => "Macedonia", "continent" => "Europe"],
            "MG" => ["country" => "Madagascar", "continent" => "Africa"],
            "MW" => ["country" => "Malawi", "continent" => "Africa"],
            "MY" => ["country" => "Malaysia", "continent" => "Asia"],
            "MV" => ["country" => "Maldives", "continent" => "Asia"],
            "ML" => ["country" => "Mali", "continent" => "Africa"],
            "MT" => ["country" => "Malta", "continent" => "Europe"],
            "MH" => ["country" => "Marshall Islands", "continent" => "Oceania"],
            "MQ" => ["country" => "Martinique", "continent" => "North America"],
            "MR" => ["country" => "Mauritania", "continent" => "Africa"],
            "MU" => ["country" => "Mauritius", "continent" => "Africa"],
            "YT" => ["country" => "Mayotte", "continent" => "Africa"],
            "MX" => ["country" => "Mexico", "continent" => "North America"],
            "FM" => ["country" => "Micronesia", "continent" => "Oceania"],
            "MD" => ["country" => "Moldova", "continent" => "Europe"],
            "MC" => ["country" => "Monaco", "continent" => "Europe"],
            "MN" => ["country" => "Mongolia", "continent" => "Asia"],
            "ME" => ["country" => "Montenegro", "continent" => "Europe"],
            "MS" => ["country" => "Montserrat", "continent" => "North America"],
            "MA" => ["country" => "Morocco", "continent" => "Africa"],
            "MZ" => ["country" => "Mozambique", "continent" => "Africa"],
            "MM" => ["country" => "Myanmar", "continent" => "Asia"],
            "NA" => ["country" => "Namibia", "continent" => "Africa"],
            "NR" => ["country" => "Nauru", "continent" => "Oceania"],
            "NP" => ["country" => "Nepal", "continent" => "Asia"],
            "NL" => ["country" => "Netherlands", "continent" => "Europe"],
            "AN" => ["country" => "Netherlands Antilles", "continent" => "North America"],
            "NC" => ["country" => "New Caledonia", "continent" => "Oceania"],
            "NZ" => ["country" => "New Zealand", "continent" => "Oceania"],
            "NI" => ["country" => "Nicaragua", "continent" => "North America"],
            "NE" => ["country" => "Niger", "continent" => "Africa"],
            "NG" => ["country" => "Nigeria", "continent" => "Africa"],
            "NU" => ["country" => "Niue", "continent" => "Oceania"],
            "NF" => ["country" => "Norfolk Island", "continent" => "Oceania"],
            "MP" => ["country" => "Northern Mariana Islands", "continent" => "Oceania"],
            "NO" => ["country" => "Norway", "continent" => "Europe"],
            "OM" => ["country" => "Oman", "continent" => "Asia"],
            "PK" => ["country" => "Pakistan", "continent" => "Asia"],
            "PW" => ["country" => "Palau", "continent" => "Oceania"],
            "PS" => ["country" => "Palestinia", "continent" => "Asia"],
            "PA" => ["country" => "Panama", "continent" => "North America"],
            "PG" => ["country" => "Papua New Guinea", "continent" => "Oceania"],
            "PY" => ["country" => "Paraguay", "continent" => "South America"],
            "PE" => ["country" => "Peru", "continent" => "South America"],
            "PH" => ["country" => "Philippines", "continent" => "Asia"],
            "PN" => ["country" => "Pitcairn", "continent" => "Oceania"],
            "PL" => ["country" => "Poland", "continent" => "Europe"],
            "PT" => ["country" => "Portugal", "continent" => "Europe"],
            "PR" => ["country" => "Puerto Rico", "continent" => "North America"],
            "QA" => ["country" => "Qatar", "continent" => "Asia"],
            "RE" => ["country" => "Reunion", "continent" => "Africa"],
            "RO" => ["country" => "Romania", "continent" => "Europe"],
            "RU" => ["country" => "Russian Federation", "continent" => "Europe"],
            "RW" => ["country" => "Rwanda", "continent" => "Africa"],
            "SH" => ["country" => "Saint Helena", "continent" => "Africa"],
            "KN" => ["country" => "Saint Kitts and Nevis", "continent" => "North America"],
            "LC" => ["country" => "Saint Lucia", "continent" => "North America"],
            "PM" => ["country" => "Saint Pierre and Miquelon", "continent" => "North America"],
            "VC" => ["country" => "Saint Vincent and The Grenadines", "continent" => "North America"],
            "WS" => ["country" => "Samoa", "continent" => "Oceania"],
            "SM" => ["country" => "San Marino", "continent" => "Europe"],
            "ST" => ["country" => "Sao Tome and Principe", "continent" => "Africa"],
            "SA" => ["country" => "Saudi Arabia", "continent" => "Asia"],
            "SN" => ["country" => "Senegal", "continent" => "Africa"],
            "RS" => ["country" => "Serbia", "continent" => "Europe"],
            "SC" => ["country" => "Seychelles", "continent" => "Africa"],
            "SL" => ["country" => "Sierra Leone", "continent" => "Africa"],
            "SG" => ["country" => "Singapore", "continent" => "Asia"],
            "SK" => ["country" => "Slovakia", "continent" => "Europe"],
            "SI" => ["country" => "Slovenia", "continent" => "Europe"],
            "SB" => ["country" => "Solomon Islands", "continent" => "Oceania"],
            "SO" => ["country" => "Somalia", "continent" => "Africa"],
            "ZA" => ["country" => "South Africa", "continent" => "Africa"],
            "GS" => ["country" => "South Georgia and The South Sandwich Islands", "continent" => "Antarctica"],
            "ES" => ["country" => "Spain", "continent" => "Europe"],
            "LK" => ["country" => "Sri Lanka", "continent" => "Asia"],
            "SD" => ["country" => "Sudan", "continent" => "Africa"],
            "SR" => ["country" => "Suriname", "continent" => "South America"],
            "SJ" => ["country" => "Svalbard and Jan Mayen", "continent" => "Europe"],
            "SZ" => ["country" => "Swaziland", "continent" => "Africa"],
            "SE" => ["country" => "Sweden", "continent" => "Europe"],
            "CH" => ["country" => "Switzerland", "continent" => "Europe"],
            "SY" => ["country" => "Syrian Arab Republic", "continent" => "Asia"],
            "TW" => ["country" => "Taiwan, Province of China", "continent" => "Asia"],
            "TJ" => ["country" => "Tajikistan", "continent" => "Asia"],
            "TZ" => ["country" => "Tanzania, United Republic of", "continent" => "Africa"],
            "TH" => ["country" => "Thailand", "continent" => "Asia"],
            "TL" => ["country" => "Timor-leste", "continent" => "Asia"],
            "TG" => ["country" => "Togo", "continent" => "Africa"],
            "TK" => ["country" => "Tokelau", "continent" => "Oceania"],
            "TO" => ["country" => "Tonga", "continent" => "Oceania"],
            "TT" => ["country" => "Trinidad and Tobago", "continent" => "North America"],
            "TN" => ["country" => "Tunisia", "continent" => "Africa"],
            "TR" => ["country" => "Turkey", "continent" => "Asia"],
            "TM" => ["country" => "Turkmenistan", "continent" => "Asia"],
            "TC" => ["country" => "Turks and Caicos Islands", "continent" => "North America"],
            "TV" => ["country" => "Tuvalu", "continent" => "Oceania"],
            "UG" => ["country" => "Uganda", "continent" => "Africa"],
            "UA" => ["country" => "Ukraine", "continent" => "Europe"],
            "AE" => ["country" => "United Arab Emirates", "continent" => "Asia"],
            "GB" => ["country" => "United Kingdom", "continent" => "Europe"],
            "US" => ["country" => "United States", "continent" => "North America"],
            "UM" => ["country" => "United States Minor Outlying Islands", "continent" => "Oceania"],
            "UY" => ["country" => "Uruguay", "continent" => "South America"],
            "UZ" => ["country" => "Uzbekistan", "continent" => "Asia"],
            "VU" => ["country" => "Vanuatu", "continent" => "Oceania"],
            "VE" => ["country" => "Venezuela", "continent" => "South America"],
            "VN" => ["country" => "Viet Nam", "continent" => "Asia"],
            "VG" => ["country" => "Virgin Islands, British", "continent" => "North America"],
            "VI" => ["country" => "Virgin Islands, U.S.", "continent" => "North America"],
            "WF" => ["country" => "Wallis and Futuna", "continent" => "Oceania"],
            "EH" => ["country" => "Western Sahara", "continent" => "Africa"],
            "YE" => ["country" => "Yemen", "continent" => "Asia"],
            "ZM" => ["country" => "Zambia", "continent" => "Africa"],
            "ZW" => ["country" => "Zimbabwe", "continent" => "Africa"]
        ];


        $newCountries = [];
        $id = 1;
        foreach ($countries as $code => $data) {
            $flag = "/nations/flags/" . str_replace(" ", "_", strtolower($data["country"])) . ".png";
            $name = $data["country"];

            if ($name === "Italy") {
                $newCountries[] = ["id" => 2, "name" => $name, "code" => $code, "flag" => $flag, "continent" => $data["continent"]];
            } else {

                if ($id === 2) {
                    $idToUse = 244;
                } else {
                    $idToUse = $id;
                }

                $newCountries[] = ["id" => $idToUse, "name" => $name, "code" => $code, "flag" => $flag, "continent" => $data["continent"]];
                $id++;
            }
        }

        // Visualizza il nuovo array
        // echo json_encode($newCountries, JSON_PRETTY_PRINT);

        foreach ($newCountries as $country) {
            $nation = new Nation();
            $nation->id = $country['id'];
            $nation->name = $country['name'];
            $nation->code = $country['code'];
            $nation->flag = $country['flag'];
            $nation->continent = $country['continent'];
            $nation->save();
        }
    }

    // Academies ---------------------------------------------------------------------------------

    private function populateAcademies() {

        if (!Academy::where('slug', 'no-academy')->exists()) {
            Academy::create([
                'name' => 'No academy',
                'slug' => 'no-academy',
                'nation_id' => 1,
            ]);
        }

        $academies = [
            [
                'name' => 'LudoSport Alpha',
                'slug' => 'ludosport-alpha',
                'nation_id' => 2,
                'created_at' => '2025-03-06 12:47:15',
                'updated_at' => '2025-07-02 08:46:58',
                'is_disabled' => 0,
                'address' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'country' => null,
                'coordinates' => null,
                'picture' => '/academies/99/1741261752_logo.png',
                'email' => 'alpha@ludosport.net',
            ],
            [
                'name' => 'LudoSport Porta dei Laghi',
                'slug' => 'ludosport-porta-dei-laghi',
                'nation_id' => 2,
                'created_at' => '2025-03-25 12:52:15',
                'updated_at' => '2025-07-04 07:34:39',
                'is_disabled' => 0,
                'address' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'country' => null,
                'coordinates' => null,
                'picture' => '/academies/100/1742907171_logo.png',
                'email' => 'portadeilaghi@ludosport.net',
            ],
            [
                'name' => 'LudoSport Porta dei Mari',
                'slug' => 'ludosport-porta-dei-mari',
                'nation_id' => 2,
                'created_at' => '2025-03-25 12:53:50',
                'updated_at' => '2025-09-17 08:55:46',
                'is_disabled' => 0,
                'address' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'country' => null,
                'coordinates' => null,
                'picture' => '/academies/101/1742907363_logo.png',
                'email' => 'portadeimari@ludosport.net',
            ],
            [
                'name' => 'LudoSport Aemilia',
                'slug' => 'ludosport-aemilia',
                'nation_id' => 2,
                'created_at' => '2025-06-18 12:34:31',
                'updated_at' => '2025-07-04 07:34:47',
                'is_disabled' => 0,
                'address' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'country' => null,
                'coordinates' => null,
                'picture' => '/academies/102/1750250588_logo.png',
                'email' => 'aemilia@ludosport.net',
            ],
            [
                'name' => 'LudoSport Cassia',
                'slug' => 'ludosport-cassia',
                'nation_id' => 2,
                'created_at' => '2025-06-18 12:44:01',
                'updated_at' => '2025-07-04 07:34:25',
                'is_disabled' => 0,
                'address' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'country' => null,
                'coordinates' => null,
                'picture' => '/academies/103/1750250699_logo.png',
                'email' => 'cassia@ludosport.net',
            ],
            [
                'name' => 'LudoSport Neapolis',
                'slug' => 'ludosport-neapolis',
                'nation_id' => 2,
                'created_at' => '2025-06-18 12:45:28',
                'updated_at' => '2025-07-04 07:34:20',
                'is_disabled' => 0,
                'address' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'country' => null,
                'coordinates' => null,
                'picture' => '/academies/104/1750250943_logo.png',
                'email' => 'neapolis@ludosport.net',
            ],
            [
                'name' => 'LudoSport Roma',
                'slug' => 'ludosport-roma',
                'nation_id' => 2,
                'created_at' => '2025-06-18 12:49:24',
                'updated_at' => '2025-07-02 08:50:28',
                'is_disabled' => 0,
                'address' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'country' => null,
                'coordinates' => null,
                'picture' => '/academies/105/1750251104_logo.png',
                'email' => 'roma@ludosport.net',
            ],
            [
                'name' => 'LudoSport Adriatica',
                'slug' => 'ludosport-adriatica',
                'nation_id' => 2,
                'created_at' => '2025-06-18 12:52:12',
                'updated_at' => '2025-07-02 10:12:37',
                'is_disabled' => 0,
                'address' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
                'country' => null,
                'coordinates' => null,
                'picture' => '/academies/106/1750251163_logo.png',
                'email' => 'adriatica@ludosport.net',
            ],
        ];

        foreach ($academies as $academy) {
            Academy::updateOrCreate(
                ['slug' => $academy['slug']],
                [
                    'name' => $academy['name'],
                    'nation_id' => $academy['nation_id'],
                    'address' => $academy['address'],
                    'city' => $academy['city'],
                    'state' => $academy['state'],
                    'zip' => $academy['zip'],
                    'country' => $academy['country'],
                    'coordinates' => $academy['coordinates'],
                    'picture' => $academy['picture'],
                    'email' => $academy['email'],
                    'is_disabled' => $academy['is_disabled'],
                    'created_at' => $academy['created_at'],
                    'updated_at' => $academy['updated_at'],
                ]
            );
        }
    }

    // Schools ---------------------------------------------------------------------------------

    private function populateSchools() {
        if (!School::where('slug', 'no-school')->exists()) {
            School::create([
                'name' => 'No school',
                'slug' => 'no-school',
                "academy_id" => 1,
                'nation_id' => 1,
            ]);
        }
    }

    // Get Location ---------------------------------------------------------------------------------

    private function getLocation($address) {

        $address = str_replace(" ", "+", $address);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=" . config('app.google.maps_key');
        $response = file_get_contents($url);
        $json = json_decode($response, true);

        if ($json['status'] == 'ZERO_RESULTS') {
            return null;
        }

        $addressComponents = $json['results'][0]['address_components'];
        $city = "";
        if (isset($addressComponents[2])) {
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

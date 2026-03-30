<?php

namespace App\Imports;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Str;

class UsersImport implements ToCollection {

    private $importingUser = null;
    private $log = [];
    private $is_partial = false;

    public function __construct($user)
    {
        $this->importingUser = $user;
    }

    public function collection(Collection $rows) {

        $firstRow = true;
        foreach ($rows as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }
            
            try {
                $birthday = $this->parseBirthday($row[3] ?? null);

                if (! $birthday) {
                    $this->log[] = "['Error: Missing or invalid birthday. User email: " . ($row[2] ?? 'N/A') . "']";
                    $this->is_partial = true;
                    continue;
                }

                $isUserMinor = $birthday->greaterThan(Carbon::today()->subYears(18));
                $nationality = Nation::where('name', $row[4])->first();

                $noAcademy = Academy::where('slug', 'no-academy')->first();
                
                if (($row[5] ?? null) == null) {
                    if($noAcademy) {
                        $row[5] = $noAcademy->id;
                    } else {
                        $row[5] = 1;
                    }
                }
                $academy = Academy::where('id', $row[5])->first();

                $noSchool = School::where('slug', 'no-school')->first();
                
                if (User::where('email', $row[2])->exists()) {
                    $this->log[] = "['Error: User email already exists. Email: " . $row[2] . "']";
                    $this->is_partial = true;
                    continue;
                }
                $user = User::create([
                    'name'     => $row[0],
                    'surname'    => $row[1],
                    'email'    => $row[2],
                    'nation_id' => $nationality ? $nationality->id : 2,
                    'password' => Hash::make(Str::random(8)),
                    'academy_id' => $academy ? $academy->id : $noAcademy->id,
                    'subscription_year' => now()->year,
                    'birthday' => $birthday->toDateString(),
                    'is_user_minor' => $isUserMinor,
                    'has_user_uploaded_documents' => false,
                    'has_admin_approved_minor' => false,
                ]);
                if(!$user){
                    $this->log[] = "['Error: User not created. Check for duplicated data or already existent user. User email: " . $row[2] . "']";
                    $this->is_partial = true;
                    continue;
                }
                $user->roles()->syncWithoutDetaching(7);
                $user->academyAthletes()->syncWithoutDetaching($academy ? $academy->id : $noAcademy->id);

                $user->schoolAthletes()->syncWithoutDetaching($noSchool ? $noSchool->id : 1);

                if(!$user->primaryAcademyAthlete()) {
                    $user->setPrimaryAcademyAthlete($academy ? $academy->id : $noAcademy->id);
                }
                
            } catch (\Exception $e) {
                $this->log[] = "['Error: User email: " . $row[2] . " - Error message: " . $e->getMessage() . "']";
                $this->is_partial = true;
                continue;
            }
        }
    }

    public function getLogArray() {
        return $this->log;
    }
    public function getIsPartial() {
        return $this->is_partial;
    }

    private function parseBirthday(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->startOfDay();
            }

            return Carbon::parse((string) $value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Clan;
use App\Models\Nation;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SchoolController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        // The dean (and maybe others, ex. manager) should only see his school
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if (in_array($authRole, ['dean', 'manager'])) {
            $school = $authUser->primarySchool() ?? null;
            if (!$school) {
                return redirect()->route('dashboard')->with('error', 'You don\'t have a school assigned!');
            }
            return $this->edit($school);
        }

        if ($authRole === 'rector') {
            if (!$authUser->validatePrimaryInstitutionPersonnel()) {
                return redirect()->route('dashboard')->with('error', 'You don\'t have an academy assigned!');
            }
            
            $primaryAcademy = $authUser->primaryAcademy();

            $schools = School::with('nation')->where([['academy_id', $primaryAcademy->id], ['is_disabled', '0']])->orderBy('created_at', 'desc')->get();
        } else {
            $schools = School::with('nation')->where('is_disabled', '0')->orderBy('created_at', 'desc')->get();
        }

        foreach ($schools as $key => $school) {
            $schools[$key]->nation_name = $school->nation->name;
            $schools[$key]->academy_name = $school->academy->name;
        }

        $viewPath = $authRole === 'admin' ? 'school.index' : 'school.' . $authRole . '.index';
        return view($viewPath, [
            'schools' => $schools
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
        $authRole = User::find(auth()->user()->id)->getRole();
        if (!in_array($authRole, ['admin', 'rector'])) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        $nations = Nation::all();

        foreach ($nations as $nation) {
            $countries[$nation['continent']][] = ['id' => $nation['id'], 'name' => $nation['name']];
        }

        $countries = [
            'Europe' => $countries['Europe'],
            'Africa' => $countries['Africa'],
            'Asia' => $countries['Asia'],
            'North America' => $countries['North America'],
            'South America' => $countries['South America'],
            'Oceania' => $countries['Oceania'],
        ];

        $viewPath = $authRole === 'admin' ? 'school.create' : 'school.' . $authRole . '.create';

        return view($viewPath, [
            'nations' => $countries,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
        $authRole = User::find(auth()->user()->id)->getRole();
        if (!in_array($authRole, ['admin', 'rector'])) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->name);

        if (School::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $school = School::create([
            'name' => $request->name,
            'nation_id' =>  $request->nationality,
            'slug' => Str::slug($request->name),
            'academy_id' => $request->academy_id
        ]);

        $redirectRoute = $authRole === 'admin' ? 'schools.edit' : $authRole . '.schools.edit';
        return redirect()->route($redirectRoute, $school)->with('success', 'School created successfully!');
    }

    public function storeacademy(Request $request) {
        $authRole = User::find(auth()->user()->id)->getRole();
        if (!in_array($authRole, ['admin', 'rector'])) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $academy = Academy::find($request->academy_id);

        $slug = Str::slug($request->name);

        if (School::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $school = School::create([
            'name' => $request->name,
            'nation_id' =>  $academy->nation_id,
            'slug' => $slug,
            'academy_id' => $academy->id
        ]);

        if ($request->go_to_edit_school) {
            $redirectRoute = $authRole === 'admin' ? 'schools.edit' : $authRole . '.schools.edit';
            return redirect()->route($redirectRoute, $school->id)->with('success', 'School created successfully!');
        } else {
            $redirectRoute = $authRole === 'admin' ? 'academies.edit' : $authRole . '.academies.edit';
            return redirect()->route($redirectRoute, $academy->id)->with('success', 'School created successfully!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school) {
        // Aggiungere i controlli per poter accedere alla pagina di modifica della scuola 
        if (!$this->checkPermission($school)) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if ($school->is_disabled && $authRole !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'School disabled.');
        }

        $nations = Nation::all();

        foreach ($nations as $nation) {
            $countries[$nation['continent']][] = ['id' => $nation['id'], 'name' => $nation['name'], 'code' => $nation['code']];
        }

        $countries = [
            'Europe' => $countries['Europe'],
            'Africa' => $countries['Africa'],
            'Asia' => $countries['Asia'],
            'North America' => $countries['North America'],
            'South America' => $countries['South America'],
            'Oceania' => $countries['Oceania'],
        ];

        $clans = Clan::whereNotIn('id', $school->clan->pluck('id'))->where('is_disabled', '0')->with(['nation'])->get();
        $associated_athletes = $school->athletes;
        $associated_personnel = $school->personnel;

        // $personnel = User::where('is_disabled', '0')->whereNotIn('id', $school->personnel->pluck('id'))->with(['roles'])->get();
        $personnel = User::where('is_disabled', '0')
            ->whereNotIn('id', $school->personnel->pluck('id'))
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['rector', 'dean', 'manager', 'technician', 'instructor']);
            })
            ->with(['roles'])
            ->get();

        foreach ($personnel as $key => $person) {
            $personnel[$key]->role = implode(', ', $person->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());
        }

        foreach ($associated_personnel as $key => $person) {
            $associated_personnel[$key]->role = implode(', ', $person->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());
        }

        $athletes = [];

        // admin (tutti), rector, dean e manager (solo gli utenti associati all'accademia della scuola o a no academy)
        switch ($authRole) {
            case 'admin':
                $athletes = User::where('is_disabled', '0')->whereNotIn('id', $school->athletes->pluck('id'))->get();
                break;
            case 'rector':
            case 'dean':
            case 'manager':
                $athletes = User::where('is_disabled', '0')->whereNotIn('id', $school->athletes->pluck('id'))->whereHas(
                    'roles',
                    function ($query) {
                        $query->where('name', 'athlete');
                    }
                )->whereHas(
                    'academyAthletes',
                    function ($query) use ($school) {
                        // $query->whereIn('academy_id', [$school->academy->id, 1]); //1 è no academy. Se si vuole far pescare anche da no academy direttamente nella scuola si fa così
                        $query->where('academy_id', $school->academy->id);
                    }
                )->get();
                break;
            default:
                break;
        }

        $roles = Role::all();
        $editable_roles = $authUser->getEditableRoles();

        return view('school.edit', [
            'school' => $school,
            'nations' => $countries,
            'clans' => $clans,
            'personnel' => $personnel,
            'athletes' => $athletes,
            'associated_personnel' => $associated_personnel,
            'associated_athletes' => $associated_athletes,
            'academies' => $school->academy->nation->academies ?? [],
            'roles' => $roles,
            'editable_roles' => $editable_roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, School $school) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if(!$this->checkPermission($school)) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'academy_id' => 'required|integer|exists:academies,id',
            'email' => 'nullable|email',
        ]);

        $school->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'academy_id' => $request->academy_id,
            'email' => $request->email,
        ]);

        $authRole = User::find(auth()->user()->id)->getRole();
        $redirectRoute = $authRole === 'admin' ? 'schools.edit' : $authRole . '.schools.edit';
        return redirect()->route($redirectRoute, $school)->with('success', 'School updated successfully!');
    }

    public function notupdate(Request $request, School $school) {
        //
        if (!$this->checkPermission($school)) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        if ($request->address) {

            $address = $request->address . " " . $request->city . " "  . $request->zip;
            $location = $this->getLocation($address);

            if (!$location) {
                return back()->with('error', 'Invalid address. Please check the address and try again.');
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'nationality' => 'required|integer|exists:nations,id',
                'academy_id' => 'required|integer|exists:academies,id',
            ]);

            $school->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'nation_id' => $request->nationality,
                'academy_id' => $request->academy_id,
                'address' => $request->address,
                'city' => $location['city'],
                'state' => $location['state'],
                'zip' => $request->zip,
                'country' => $location['country'],
                'coordinates' => json_encode(['lat' => $location['lat'], 'lng' => $location['lng']]),
            ]);

            $authRole = User::find(auth()->user()->id)->getRole();
            $redirectRoute = $authRole === 'admin' ? 'schools.edit' : $authRole . '.schools.edit';
            return redirect()->route($redirectRoute, $school)->with('success', 'School updated successfully!');
        } else {
            $request->validate([
                'name' => 'required|string|max:255',
                'nationality' => 'required|integer|exists:nations,id',
                'academy_id' => 'required|integer|exists:academies,id',
            ]);

            $school->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'nation_id' => $request->nationality,
                'academy_id' => $request->academy_id,
            ]);

            $authRole = User::find(auth()->user()->id)->getRole();
            $redirectRoute = $authRole === 'admin' ? 'schools.edit' : $authRole . '.schools.edit';
            return redirect()->route($redirectRoute, $school)->with('success', 'School updated successfully!');
        }
    }

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

    public function verifyAddress(Request $request) {
        $request->validate([
            'address' => 'required|string',
            'city' => 'required|string',
            'zip' => 'required|string',
            'nation' => 'required|integer|exists:nations,id',
            'school_id' => 'required|integer|exists:schools,id',
        ]);

        try {
            $nation = Nation::find($request->nation);

            $url = "https://addressvalidation.googleapis.com/v1:validateAddress?key=" . config('app.google.maps_key');
            $data = [
                'address' => [
                    'regionCode' => $nation->code,
                    'locality' =>  $request->city,
                    'postalCode' => $request->zip,
                    'addressLines' => [
                        $request->address
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $data);

            if ($response->successful()) {
                //return response()->json($response->json());
                $data = $response->json();

                // Controlla se l'indirizzo è completo

                // if (isset($data['result']['verdict']['addressComplete'])) {
                //     // Questa proprietà non c'è anche se i campi sono corretti
                //     $isAddressComplete = $data['result']['verdict']['addressComplete'];
                // } else {
                //     $isAddressComplete = false;
                // }
                // Controlla se ci sono componenti non confermati
                if (isset($data['result']['verdict']['hasUnconfirmedComponents'])) {
                    $hasUnconfirmedComponents = $data['result']['verdict']['hasUnconfirmedComponents'];
                } else {
                    $hasUnconfirmedComponents = false;
                }

                // Ottieni l'indirizzo formattato
                $formattedAddress = $data['result']['address']['formattedAddress'];

                // if ($isAddressComplete && !$hasUnconfirmedComponents) {
                if (!$hasUnconfirmedComponents) {
                    // L'indirizzo è valido e completo

                    $address = $request->address . " " . $request->city . " "  . $request->zip;
                    $location = $this->getLocation($address);
                    $school = School::find($request->school_id);

                    $postalAddress = $data['result']['address']['postalAddress'];
                    $school->update([
                        'address' => $request->address,
                        'city' => $postalAddress['locality'],
                        'state' => $location['state'], // Regione
                        'zip' => $request->zip,
                        'country' => ($location['country'] ? $location['country'] : $nation->name), // Nazione
                        'coordinates' => json_encode(['lat' => $location['lat'], 'lng' => $location['lng']]),
                    ]);

                    return response()->json([
                        'state' => 1,
                        'message' => 'The address is valid.',
                        'original' => $data
                    ]);
                } else {
                    // L'indirizzo potrebbe essere incompleto o ambiguo

                    if ($hasUnconfirmedComponents) {

                        $unconfirmed = $data['result']['address']['unconfirmedComponentTypes'];
                        $message = 'The given address is not completely correct, correct could be: ' . $formattedAddress;

                        $unconfirmedComponents = [];
                        foreach ($unconfirmed as $component) {
                            $unconfirmedComponents[] = __('school.address_' . $component);
                        }

                        $unconfirmed = 'The following elements need to be corrected: ' . implode(', ', $unconfirmedComponents);

                        return response()->json([
                            'state' => 2,
                            'message' => $message,
                            'unconfirmed' => $unconfirmed,
                            'original' => $data
                        ]);
                    } else {
                        return response()->json([
                            'state' => 2,
                            'message' => 'The given address is not completely correct, correct could be ' . $formattedAddress,
                            'original' => $data
                        ]);
                    }
                }
            } else {
                return response()->json([
                    'state' => 3,
                    'message' => 'An error occurred while validating the address.',
                    'response' => $response->json()
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'state' => 3,
                'message' => 'The address seems incorrect. Try including street name and house number, the city and the postal code.',
                // 'message' => $e->getMessage()
            ]);
        }
    }

    private function getCoordinates($location) {
        $location = urlencode($location);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$location}&key=" . config('app.google.maps_key');

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['results'][0]['geometry']['location'])) {
            $lat = $data['results'][0]['geometry']['location']['lat'];
            $lng = $data['results'][0]['geometry']['location']['lng'];
            return array($lat, $lng);
        }
        return array(null, null);
    }

    private function haversine($lat1, $lon1, $lat2, $lon2) {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $delta_lat = $lat2 - $lat1;
        $delta_lon = $lon2 - $lon1;

        $a = pow(sin($delta_lat / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($delta_lon / 2), 2);
        $c = 2 * asin(sqrt($a));
        $r = 6371;

        return ($c * $r);
    }

    private function findNearbySchools($schools, $locationLat, $locationLon, $radius) {
        $nearbySchools = [];
        foreach ($schools as $school) {

            if (!$school->coordinates) continue; // Skip Schools without coordinates (e.g. invalid addresses

            $coordinates = json_decode($school->coordinates, true);

            $schoolLat = $coordinates['lat'];
            $schoolLon = $coordinates['lng'];
            $distance = $this->haversine($locationLat, $locationLon, $schoolLat, $schoolLon);
            if ($distance <= $radius) {
                $nearbySchools[] = $school;
            }
        }
        return $nearbySchools;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        // solo rector e admin possono
        if (!$this->checkPermission($school, true)) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        // if ($school->athletes->count() > 0) {
        //     return back()->with('error', 'Cannot delete school with associated athletes.');
        // }

        // Non si può eliminare se ha corsi attivi (in questo caso clan ha già il filtro "is_disabled")
        if ($school->clan->count() > 0) {
            return back()->with('error', 'Cannot delete school with associated courses.');
        }

        $athletes = $school->athletes->pluck('id')->toArray();
        $personnel = $school->personnel->pluck('id')->toArray();

        $primaryPersonnel = $school->personnel->filter(function ($person) use ($school) {
            return $person->primarySchool() && $person->primarySchool()->id == $school->id;
        })->pluck('id')->toArray();

        $school->athletes()->detach();
        $school->personnel()->detach();
        $school->is_disabled = true;
        $school->save();

        Log::channel('school')->info('Disabled school', [
            'made_by' => $authUser->id,
            'school' => $school->id,
            'athletes_ids' => $athletes,
            'personnel_ids' => $personnel,
            'primary_personnel_ids' => $primaryPersonnel,
        ]);

        return redirect()->route(($authRole == 'admin' ? '' : $authRole) . 'schools.index')->with('success', 'School disabled successfully!');
    }

    public function addClan(School $school, Request $request) {
        //
        if (!$this->checkPermission($school)) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        $clan = Clan::find($request->clan_id);
        $clan->school_id = $school->id;

        $clan->save();

        $authRole = User::find(auth()->user()->id)->getRole();
        $redirectRoute = $authRole === 'admin' ? 'schools.edit' : $authRole . '.schools.edit';
        return redirect()->route($redirectRoute, $school)->with('success', 'Course added successfully!');
    }

    public function addPersonnel(School $school, Request $request) {
        //
        if (!$this->checkPermission($school)) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        // Se mancano le associazioni a scuola e accademia del corso, si aggiungono
        $personnel = User::find($request->personnel_id);
        $academy = Academy::find($school->academy_id);
        $isInThisAcademy = $academy->personnel->where('id', $personnel->id)->count();
        if (!$isInThisAcademy) {
            $academy->personnel()->syncWithoutDetaching($personnel->id);
        }

        $school->personnel()->syncWithoutDetaching($personnel->id);

        // Se il personale non ha la scuola principale, la assegna
        if (!$personnel->primarySchool()) {
            $personnel->setPrimarySchool($school->id);
        }

        $authRole = User::find(auth()->user()->id)->getRole();
        $redirectRoute = $authRole === 'admin' ? 'schools.edit' : $authRole . '.schools.edit';

        return redirect()->route($redirectRoute, $school)->with('success', 'Personnel added successfully!');
    }

    public function addAthlete(School $school, Request $request) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if (!$this->checkPermission($school)) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        // Se mancano le associazioni a scuola e accademia del corso, si aggiungono
        $athlete = User::find($request->athlete_id);
        // $academy = Academy::find($school->academy_id);

        if (!$athlete->schoolAthletes()->where('school_id', $school->id)->exists()) {

            if ($athlete->academyAthletes()->first()->id != $school->academy->id) {
                // L'admin può farlo sempre, il rettore, il dean e il manager solo se l'accademia è no academy
                if ($authRole !== 'admin' && $athlete->academyAthletes()->first()->id !== 1) {
                    return redirect()->route('dashboard')->with('error', 'Not authorized.');
                }
                // L'atleta può avere solo un'accademia associata
                $athlete->removeAcademiesAthleteAssociations();
            }

            $athlete->academyAthletes()->syncWithoutDetaching([$school->academy->id]);
            $athlete->schoolAthletes()->syncWithoutDetaching([$school->id]);
        }

        // Se l'atleta non ha l'accademia principale, la assegna
        if (!$athlete->primaryAcademyAthlete()) {
            $athlete->setPrimaryAcademyAthlete($school->academy->id);
        }
        // Se l'atleta non ha la scuola principale, la assegna
        if (!$athlete->primarySchoolAthlete()) {
            $athlete->setPrimarySchoolAthlete($school->id);
        }

        // Se l'atleta ha una scuola assegnata diversa da No School, toglie No School
        $noSchool = School::where('slug', 'no-school')->first();
        if ($noSchool) {
            if ($athlete->schoolAthletes()->whereNot('school_id', $noSchool->id)->count() > 0) {
                $athlete->schoolAthletes()->detach($noSchool->id);
            }
        }

        $authRole = User::find(auth()->user()->id)->getRole();
        $redirectRoute = $authRole === 'admin' ? 'schools.edit' : $authRole . '.schools.edit';

        return redirect()->route($redirectRoute, $school)->with('success', 'Athlete added successfully!');
    }

    public function all(Request $request) {
        $schools = School::where('is_disabled', '0')->with(['academy'])->get();
        $formatted_schools = [];

        foreach ($schools as $key => $school) {
            $formatted_schools[] = [
                'id' => $school->id,
                'academy' => $school->academy->name,
                'name' => $school->name,
            ];
        }

        return response()->json($formatted_schools);
    }

    public function getByAcademy(Request $request) {

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        $academies = json_decode($request->academies);

        $schools = School::whereIn('academy_id', $academies)->where('is_disabled', '0')->with(['academy'])->get();
        $formatted_schools = [];

        // Se l'utente è un istruttore, si filtrano le scuole per quelle a cui è associato
        $instructorSchools = $authUser->schools->pluck('id')->toArray();

        // Se l'utente è dean o manager si restituisce solo la sua scuola (se nell'accademia selezionata, che dovrebbe essere solo la sua)
        $primarySchool = $authUser->primarySchool();

        foreach ($schools as $key => $school) {
            if ($authRole === 'instructor' && !in_array($school->id, $instructorSchools)) {
                continue;
            }
            if (in_array($authRole, ['dean', 'manager']) && $school->id !== $primarySchool->id) {
                continue;
            }
            $formatted_schools[] = [
                'id' => $school->id,
                'academy' => $school->academy->name,
                'name' => $school->name,
            ];
        }

        return response()->json($formatted_schools);
    }

    public function search(Request $request) {
        // $academies = Academy::where('name', 'like', '%' . $request->name . '%')->where('is_disabled', '0')->get();

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if ($authRole === 'rector') {
            $academy = $authUser->primaryAcademy();
            $schools = $academy
                ? School::query()->when($request->search, function ($q, $search) {
                    return $q->whereIn('id', School::search($search)->keys());
                })->where([['is_disabled', '0'], ['academy_id', $academy->id]])->with(['academy'])->get()
                : collect([]);
        } else {
            $schools = School::query()->when($request->search, function ($q, $search) {
                return $q->whereIn('id', School::search($search)->keys());
            })->where('is_disabled', '0')->with(['academy'])->get();
        }

        $formatted_schools = [];

        foreach ($schools as $key => $school) {
            $formatted_schools[] = [
                'id' => $school->id,
                'academy' => $school->academy->name,
                'name' => $school->name,
            ];
        }


        return response()->json($formatted_schools);
    }

    public function athletesDataForSchool(School $school) {

        $athletes = $school->athletes;
        $active_users = 0;
        $active_users_no_course = 0;
        $users_course_not_active = 0;
        $new_users_this_year = 0;

        foreach ($athletes as $key => $athlete) {

            if ($athlete->has_paid_fee) {
                $active_users++;
            }

            if (($athlete->has_paid_fee) && ($athlete->clans()->count() == 0)) {
                $active_users_no_course++;
            }

            if ((!$athlete->has_paid_fee) && ($athlete->clans()->count() > 0)) {
                $users_course_not_active++;
            }

            if ($athlete->created_at->year == now()->year) {
                $new_users_this_year++;
            }
        }

        return response()->json([
            'active_users' => $active_users,
            'active_users_no_course' => $active_users_no_course,
            'users_course_not_active' => $users_course_not_active,
            'new_users_this_year' => $new_users_this_year,
        ]);
    }

    public function athletesClanDataForSchool(School $school) {
        $clans_data = [];

        foreach ($school->clan as $clan) {
            $clans_data[] = [
                'id' => $clan->id,
                'name' => $clan->name,
                'athletes' => $clan->users->count(),
            ];
        }

        return response()->json($clans_data);
    }

    public function getAthletesNumberPerYear(School $school) {
        $athletes = $school->athletes->where('is_disabled', '0');
        $athletes_last_year = 0;
        $athletes_this_year = 0;

        foreach ($athletes as $athlete) {
            $athlete->created_at->year == now()->year ? $athletes_this_year++ : $athletes_last_year++;
        }

        return response()->json([
            'last_year' => $athletes_last_year,
            'this_year' => $athletes_this_year,
        ]);
    }

    public function searchUsers(School $school, Request $request) {
        //

        $roles = json_decode($request->roles);


        $users = User::query()
            ->when($request->search, function (Builder $q, $value) {
                return $q->whereIn('id', User::search($value)->keys());
            })->with(['roles', 'schools', 'schoolAthletes'])->get();

        $users = $users->filter(function ($user) use ($school) {
            $schools = $user->schools->pluck('id')->toArray();
            $schoolAthletes = $user->schoolAthletes->pluck('id')->toArray();

            return in_array($school->id, $schools) || in_array($school->id, $schoolAthletes);
        });

        $filteredUsers = [];


        if ($request->filters_enabled == "true") {

            // Filtro per ruolo 

            if ($roles && count($roles) > 0) {

                $users = $users->filter(function ($user) use ($roles) {

                    $allowedRoles = collect($user->allowedRoleIds());

                    foreach ($roles as $id) {
                        if ($allowedRoles->contains($id)) {
                            return true;
                        }
                    }
                });
            }

            // Filtro per data creazione & Filtro per anno iscrizione

            $shouldCheckForCreationDateFrom = $request->from != null;
            $shouldCheckForCreationDateTo = $request->to != null;
            $shouldCheckForYear = $request->year != null;


            if ($request->creation_date) {
                $users = $users->filter(function ($user) use ($request) {
                    return $user->created_at->format('Y-m-d') == $request->creation_date;
                });
            }

            foreach ($users as $user) {
                $shouldAdd = true;

                if ($shouldCheckForYear) {
                    if ($user->subscription_year != $request->year) {
                        $shouldAdd = false;
                    }
                }

                if ($shouldCheckForCreationDateFrom) {
                    if ($user->created_at < $request->from) {
                        $shouldAdd = false;
                    }
                }

                if ($shouldCheckForCreationDateTo) {
                    if ($user->created_at > $request->to) {
                        $shouldAdd = false;
                    }
                }

                if ($shouldAdd) {
                    $filteredUsers[] = $user;
                }
            }
        } else {
            $filteredUsers = $users;
        }

        foreach ($filteredUsers as $user) {

            $user->academy = $user->primaryAcademyAthlete();
            $user->school = $user->primarySchoolAthlete();
            if ($user->academy) {
                $user->nation = $user->academy->nation->name;
            } else {
                $nation = Nation::find($user->nation_id);
                $user->nation = $nation->name;
            }

            $user->role = implode(', ', $user->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());
        }

        $authRole = User::find(auth()->user()->id)->getRole();
        $viewPath = $authRole == 'admin' ? 'users.filter-result' : 'users.' . $authRole . '.filter-result';
        $backUrlRoute = $authRole == 'admin' ? 'schools.edit' : $authRole . '.schools.edit';
        return view($viewPath, [
            'users' => $filteredUsers,
            'backUrl' => route($backUrlRoute, $school->id),
        ]);
    }

    public function checkPermission(School $school, $isStrict = false) {
        // admin -> sempre; rector -> solo se la scuola è nella sua accademia; dean e manager -> solo se la scuola è associata a lui; 
        // l'opzione isStrict permette di escludere anche i dean e manager, per funzionalità accessibili solo a rettori e admin
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        $authorized = true;

        switch ($authRole) {
            case 'admin': // sempre autorizzato
                break;
            case 'rector': // non autorizzato se la scuola non è nella sua accademia
                $schoolAcademy = $school->academy;
                $academyRector = $schoolAcademy->rector();
                if(!$academyRector || ($academyRector->id != $authUser->id)) {
                    $authorized = false;
                }
                // $primaryAcademy = $authUser->primaryAcademy();
                // if (!$primaryAcademy || ($primaryAcademy->id != $school->academy->id)) {
                //     $authorized = false;
                // }
                break;
            case 'dean':
                if (($authUser->id != ($school->dean()->id ?? null)) || $isStrict) {
                    $authorized = false;
                }
                break;
            case 'manager': // non autorizzato se la scuola non è associata a lui o se strict è true
                if (($authUser->primarySchool()->id ?? null) != $school->id || $isStrict) {
                    $authorized = false;
                }
                break;
            default:
                $authorized = false;
                break;
        }

        return $authorized;
    }


    /** Mappa Scuole */

    public function schoolsMap() {

        $schools = School::where('is_disabled', '0')->whereNotNull('coordinates')->with(['nation'])->get();
        $formatted_schools = [];
        $allnations = [];
        $available_nations = [];

        foreach ($schools as $key => $academy) {
            $formatted_schools[] = [
                'id' => $academy->id,
                'nation' => $academy->nation->name,
                'slug' => $academy->slug,
                'nation_id' => $academy->nation->id,
                'name' => $academy->name,
                'address' => $academy->address,
                'city' => $academy->city,
                'state' => $academy->state,
                'zip' => $academy->zip,
                'country' => $academy->country,
                'coordinates' => json_decode($academy->coordinates, true),
            ];

            if (!in_array($academy->nation->name, $allnations)) {
                $available_nations[] = [
                    'value' => $academy->nation->id,
                    'label' => $academy->nation->name,
                ];

                $allnations[] = $academy->nation->name;
            }
        }

        return view('website.schools-map', [
            'schools_json' => json_encode($formatted_schools),
            'nations' => $available_nations,
        ]);
    }

    public function searchSchools(Request $request) {

        $location = $request->location;
        $radius = $request->radius ? $request->radius : 50;

        $coordinates = $this->getCoordinates($location);
        $locationLat = $coordinates[0];
        $locationLon = $coordinates[1];

        $schools = Academy::where('is_disabled', '0')->whereNotNull('coordinates')->get();
        $nearbyAcademies = $this->findNearbySchools($schools, $locationLat, $locationLon, $radius);

        return response()->json($nearbyAcademies);
    }

    public function detail(School $school) {

        $dean = $school->dean() ? $school->dean()->name . " " . $school->dean()->surname : "";

        $academy = $school->academy;
        $rector = $academy->rector() ? $academy->rector()->name . " " . $academy->rector()->surname : "";

        return view('website.school-profile', [
            'school' => $school,
            'academy' => $academy,
            'rector' => $rector,
            'dean' => $dean,
            'athletes' => $school->athletes,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AcademyController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if (in_array($authRole, ['rector'])) {
            $academy = $authUser->primaryAcademy();
            if ($authUser->validatePrimaryInstitutionPersonnel()) {
                return $this->edit($academy);
            }
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }

        $academies = Academy::with('nation')->where('is_disabled', '0')->orderBy('created_at', 'desc')->get();

        foreach ($academies as $key => $academy) {
            $academies[$key]->nation_name = $academy->nation->name;
        }

        return view('academy.index', [
            'academies' => $academies,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        $nations = Nation::all();

        return view('academy.create', [
            'nations' => $nations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $nation = Nation::where('name', $request->nationality)->first();

        $slug = Str::slug($request->name);

        if (Academy::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $academy = Academy::create([
            'name' => $request->name,
            'nation_id' => $nation->id,
            'slug' =>  $slug
        ]);

        return redirect()->route('academies.edit', $academy)->with('success', 'Academy created successfully!');
    }

    public function storenation(Request $request) {

        $should_go_to_edit = $request->go_to_edit === 'on' ? true : false;

        $request->validate([
            'name' => 'required|string|max:255',
            'nation_id' => 'required|exists:nations,id',
        ]);

        $slug = Str::slug($request->name);

        if (Academy::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $academy = Academy::create([
            'name' => $request->name,
            'nation_id' => $request->nation_id,
            'slug' => $slug
        ]);

        if ($should_go_to_edit) {
            return redirect()->route('academies.edit', $academy)->with('success', 'Academy created successfully!');
        } else {
            return redirect()->route('nations.edit', $request->nation_id)->with('success', 'Academy created successfully!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Academy $academy) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Academy $academy) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if ($academy->is_disabled && $authRole !== 'admin') {
            return redirect()->route('academies.index')->with('error', 'Academy is disabled.');
        }

        if (!$this->checkPermission($academy)) {
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

        $schools = School::whereNotIn('id', $academy->schools->pluck('id'))->where('is_disabled', '0')->with(['nation'])->get();
        $associated_athletes = $academy->athletes;
        $associated_personnel = $academy->personnel;

        // $personnel = User::where('is_disabled', '0')->whereNotIn('id', $academy->personnel->pluck('id'))->with(['roles'])->get();
        $personnel = User::where('is_disabled', '0')
            ->whereNotIn('id', $academy->personnel->pluck('id'))
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

        foreach ($associated_athletes as $athlete) {
            if ($athlete->primarySchoolAthlete()) {
                $athlete->school = $athlete->primarySchoolAthlete()->name;
            } else {
                $athlete->school = 'No school';
            }
        }


        $athletes = [];
        switch ($authRole) {
            case 'admin':
                $athletes = User::where('is_disabled', '0')->whereNotIn('id', $academy->athletes->pluck('id'))->whereHas(
                    'roles',
                    function ($query) {
                        $query->where('name', 'athlete');
                    }
                )->get();
                break;
            case 'rector':
                $athletes = User::where('is_disabled', '0')->whereNotIn('id', $academy->athletes->pluck('id'))->whereHas(
                    'roles',
                    function ($query) {
                        $query->where('name', 'athlete');
                    }
                )->whereHas(
                    'academyAthletes',
                    function ($query) use ($academy) {
                        $query->whereIn('academy_id', [$academy->id, 1]); //1 è no academy
                    }
                )->get();
                break;
            default:
                break;
        }

        $roles = Role::all();
        $editable_roles = $authUser->getEditableRoles();

        return view('academy.edit', [
            'academy' => $academy,
            'nations' => $countries,
            'schools' => $schools,
            'personnel' => $personnel,
            'athletes' => $athletes,
            'associated_personnel' => $associated_personnel,
            'associated_athletes' => $associated_athletes,
            'roles' => $roles,
            'editable_roles' => $editable_roles
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Academy $academy) {
        //

        if ($request->address) {
            $address = $request->address . " " . $request->city . " "  . $request->zip;
            $location = $this->getLocation($address);

            if (!$location) {
                return back()->with('error', 'Invalid address. Please check the address and try again.');
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'nationality' => 'required|exists:nations,id',
            ]);

            $academy->update([
                'name' => $request->name,
                'nation_id' => $request->nationality,
                'slug' => Str::slug($request->name),
                'address' => $request->address,
                'city' => $location['city'],
                'state' => $location['state'],
                'zip' => $request->zip,
                'country' => $location['country'],
                'coordinates' => json_encode(['lat' => $location['lat'], 'lng' => $location['lng']]),
            ]);
        } else {

            $request->validate([
                'name' => 'required|string|max:255',
                'nationality' => 'required|exists:nations,id',
            ]);

            $academy->update([
                'name' => $request->name,
                'nation_id' => $request->nationality,
                'slug' => Str::slug($request->name),
            ]);
        }

        return redirect()->route('academies.edit', $academy->id)->with('success', 'Academy updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Academy $academy) {
        //
        $authUser = User::find(auth()->user()->id);

        if (User::find(auth()->user()->id)->getRole() !== 'admin') {
            return redirect()->route('academies.index')->with('error', 'You are not authorized to perform this action.');
        }

        if ($academy->schools->count() > 0) {
            return back()->with('error', 'Cannot delete academy with associated schools.');
        }

        $athletes = $academy->athletes->pluck('id')->toArray();
        $personnel = $academy->personnel->pluck('id')->toArray();

        $primaryAthletes = $academy->athletes()->wherePivot('is_primary', 1)->get();
        $primaryPersonnel = $academy->personnel()->wherePivot('is_primary', 1)->get();

        foreach ($primaryAthletes as $athlete) {
            $athlete->academyAthletes()->syncWithoutDetaching(1);
            $athlete->setPrimaryAcademyAthlete(1);
        }

        foreach ($primaryPersonnel as $person) {
            $person->academies()->syncWithoutDetaching(1);
            $person->setPrimaryAcademy(1);
        }

        $academy->athletes()->detach();
        $academy->personnel()->detach();
        $academy->is_disabled = true;
        $academy->save();

        Log::channel('academy')->info('Disabled academy', [
            'made_by' => $authUser->id,
            'academy' => $academy->id,
            'athletes_ids' => $athletes,
            'personnel_ids' => $personnel,
            'primary_athletes' => $primaryAthletes->pluck('id')->toArray(),
            'primary_personnel' => $primaryPersonnel->pluck('id')->toArray(),
        ]);

        $academy->is_disabled = true;
        $academy->save();

        // Questa parte serve solo se si toglie il blocco all'eliminazione se ci sono atleti associati
        // // Prende gli atleti dell'accademia
        // $athletes = $academy->athletes;

        // foreach ($athletes as $athlete) {
        //     // Disassocia l'atleta dall'accademia
        //     $athlete->academyAthletes()->detach($academy->id);
        //     // Se ne ha un'altra la imposta come principale, altrimenti imposta no-academy come principale
        //     if ($athlete->academyAthletes()->count() > 0) {
        //         $athlete->setPrimaryAcademyAthlete($athlete->academyAthletes->first()->id);
        //     } else {
        //         $noAcademy = Academy::where('slug', 'no-academy')->first();
        //         $athlete->academyAthletes()->syncWithoutDetaching($noAcademy->id);
        //         $athlete->setPrimaryAcademyAthlete($noAcademy->id);
        //     }
        // }

        // // Prende il personale dell'accademia
        // $personnel = $academy->personnel;

        // foreach ($personnel as $person) {
        //     // Disassocia il personale dall'accademia
        //     $person->academies()->detach($academy->id);
        //     // Se ne ha un'altra la imposta come principale, altrimenti imposta no-academy come principale
        //     if ($person->academies()->count() > 0) {
        //         $person->setPrimaryAcademy($person->academies->first()->id);
        //     } else {
        //         $noAcademy = Academy::where('slug', 'no-academy')->first();
        //         $person->academies()->syncWithoutDetaching($noAcademy->id);
        //         $person->setPrimaryAcademy($noAcademy->id);
        //     }
        // }

        return redirect()->route('academies.index')->with('success', 'Academy disabled successfully!');
    }

    public function schools(Academy $academy) {
        return response()->json($academy->schools);
    }

    public function addSchool(Request $request, Academy $academy) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        $school = School::find($request->school_id);
        $school->academy_id = $academy->id;
        $school->save();

        $redirectRoute = $authRole === 'admin' ? 'academies.edit' : $authRole . '.academies.edit';
        return redirect()->route($redirectRoute, $academy)->with('success', 'School added successfully!');
    }

    public function addPersonnel(Request $request, Academy $academy) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        $personnel = User::find($request->personnel_id);

        $academy->personnel()->syncWithoutDetaching($personnel->id);

        // Se il personale non ha l'accademia principale, la assegna
        if (!$personnel->primaryAcademy()) {
            $personnel->setPrimaryAcademy($academy->id);
        }

        $redirectRoute = $authRole === 'admin' ? 'academies.edit' : $authRole . '.academies.edit';
        return redirect()->route($redirectRoute, $academy)->with('success', 'Personnel added successfully!');
    }

    public function addAthlete(Request $request, Academy $academy) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        $athlete = User::find($request->athlete_id);
        $redirectRoute = $authRole === 'admin' ? 'academies.edit' : $authRole . '.academies.edit';

        if ($academy->athletes->contains($athlete->id)) {
            return redirect()->route($redirectRoute, $academy)->with('success', 'Athlete is already associated with this academy.');
        }

        // l'atleta può essere associato ad una sola accademia, quindi se si modifica vanno rimossi anche tutti i collegamenti inferiori (scuole e corsi)

        // L'admin può farlo sempre, il rettore solo se l'accademia è no academy
        if ($authRole !== 'admin' && $athlete->academyAthletes()->first()->id !== 1) {
            return redirect()->route('dashboard')->with('error', 'Not authorized.');
        }
        // l'argomento è l'accademia che fa eccezione, (se serve)
        $athlete->removeAcademiesAthleteAssociations($academy);

        $academy->athletes()->syncWithoutDetaching($athlete->id);
        // Se l'atleta non ha l'accademia principale, la assegna
        if (!$athlete->primaryAcademyAthlete()) {
            $schoolAcademy = null;
            if ($athlete->primarySchoolAthlete()) {
                $schoolAcademy = $athlete->primarySchoolAthlete()->academy;
            }
            $athlete->setPrimaryAcademyAthlete($schoolAcademy ? $schoolAcademy->id : $academy->id);
        }

        // Se l'atleta non ha una scuola assegnata, assegna No school
        if ($athlete->schoolAthletes()->count() == 0) {
            $noSchool = School::where('slug', 'no-school')->first();
            if ($noSchool) {
                $athlete->schoolAthletes()->syncWithoutDetaching($noSchool->id);
            }
        }

        return redirect()->route($redirectRoute, $academy)->with('success', 'Athlete added successfully!');
    }

    public function all(Request $request) {
        $academies = Academy::where('is_disabled', '0')->with(['nation'])->get();
        $formatted_academies = [];

        foreach ($academies as $key => $academy) {
            $formatted_academies[] = [
                'id' => $academy->id,
                'nation' => $academy->nation->name,
                'name' => $academy->name,
            ];
        }

        return response()->json($formatted_academies);
    }

    public function allWithLocation(Request $request) {
        $academies = Academy::where('is_disabled', '0')->whereNotNull('coordinates')->with(['nation'])->get();
        $formatted_academies = [];

        foreach ($academies as $key => $academy) {
            $formatted_academies[] = [
                'id' => $academy->id,
                'nation' => $academy->nation->name,
                'name' => $academy->name,
                'address' => $academy->address,
                'city' => $academy->city,
                'state' => $academy->state,
                'zip' => $academy->zip,
                'country' => $academy->country,
                'coordinates' => json_decode($academy->coordinates, true),
            ];
        }

        return response()->json($formatted_academies);
    }

    public function search(Request $request) {
        // $academies = Academy::where('name', 'like', '%' . $request->name . '%')->where('is_disabled', '0')->get();

        $academies = Academy::query()->when($request->search, function ($q, $search) {
            return $q->whereIn('id', Academy::search($search)->keys());
        })->where('is_disabled', '0')->with(['nation'])->get();

        $formatted_academies = [];

        foreach ($academies as $key => $academy) {
            $formatted_academies[] = [
                'id' => $academy->id,
                'nation' => $academy->nation->name,
                'name' => $academy->name,
            ];
        }


        return response()->json($formatted_academies);
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

    /** Ricerca lato web */

    public function academiesMap() { //Funzione non utilizzata. Nel caso copiare la view schools-map

        $academies = Academy::where('is_disabled', '0')->whereNotNull('coordinates')->with(['nation'])->get();
        $formatted_academies = [];
        $allnations = [];
        $available_nations = [];

        foreach ($academies as $key => $academy) {
            $formatted_academies[] = [
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

        return view('website.academies-map', [
            'academies_json' => json_encode($formatted_academies),
            'nations' => $available_nations,
        ]);
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

    private function findNearbyAcademies($acadmies, $locationLat, $locationLon, $radius) {
        $nearbyAcademies = [];
        foreach ($acadmies as $academy) {

            if (!$academy->coordinates) continue; // Skip academies without coordinates (e.g. invalid addresses

            $coordinates = json_decode($academy->coordinates, true);

            $academyLat = $coordinates['lat'];
            $academyLon = $coordinates['lng'];
            $distance = $this->haversine($locationLat, $locationLon, $academyLat, $academyLon);
            if ($distance <= $radius) {
                $nearbyAcademies[] = $academy;
            }
        }
        return $nearbyAcademies;
    }

    public function searchAcademies(Request $request) {

        $location = $request->location;
        $radius = $request->radius ? $request->radius : 50;

        $coordinates = $this->getCoordinates($location);
        $locationLat = $coordinates[0];
        $locationLon = $coordinates[1];

        $academies = Academy::where('is_disabled', '0')->whereNotNull('coordinates')->get();
        $nearbyAcademies = $this->findNearbyAcademies($academies, $locationLat, $locationLon, $radius);

        return response()->json($nearbyAcademies);
    }

    public function athletesDataForAcademy(Academy $academy) {
        $athletes = $academy->athletes;
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

    public function athletesSchoolDataForAcademy(Academy $academy) {

        $schools = [];

        foreach ($academy->schools as $key => $school) {

            $schools[] = [
                'id' => $school->id,
                'name' => $school->name,
                'athletes' => $school->athletes->count(),
            ];
        }

        return response()->json($schools);
    }

    public function getAthletesNumberPerYear(Academy $academy) {
        $athletes = $academy->athletes;
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

    public function searchUsers(Academy $academy, Request $request) {
        //

        $roles = json_decode($request->roles);


        $users = User::query()
            ->when($request->search, function (Builder $q, $value) {
                return $q->whereIn('id', User::search($value)->keys());
            })->with(['roles', 'academies', 'academyAthletes'])->get();

        $users = $users->filter(function ($user) use ($academy) {
            $academies = $user->academies->pluck('id')->toArray();
            $academyAthletes = $user->academyAthletes->pluck('id')->toArray();

            return in_array($academy->id, $academies) || in_array($academy->id, $academyAthletes);
        });

        $filteredUsers = [];


        if ($request->filters_enabled == "true") {

            // Filtro per ruolo 

            if (count($roles) > 0) {

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
        $viewPath = $authRole === 'admin' ? 'users.filter-result' : 'users.' . $authRole . '.filter-result';

        return view($viewPath, [
            'users' => $filteredUsers,
            'backUrl' => route('academies.edit', $academy->id),
        ]);
    }

    public function checkPermission(Academy $academy, $isStrict = false) {
        // admin -> sempre; rector -> solo se l'accademia è associata a lui; 
        // l'opzione isStrict permette di escludere anche i rector, per funzionalità accessibili solo agli admin
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        $authorized = false;

        switch ($authRole) {
            case 'admin': // sempre autorizzato
                $authorized = true;
                break;
            case 'rector': // non autorizzato se non è la sua accademia
                if (!$isStrict && ($authUser->primaryAcademy() != null) && $authUser->primaryAcademy()->id == $academy->id) {
                    $authorized = true;
                }
                break;
            default:
                $authorized = false;
                break;
        }

        return $authorized;
    }

    public function detail(Academy $academy) {

        $rector = "";
        $associated_personnel = $academy->personnel;

        foreach ($associated_personnel as $key => $person) {
            if ($person->hasRole('rector')) {
                $rector = $person->name . " " . $person->surname;
            }

            $associated_personnel[$key]->role = implode(', ', $person->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());
        }

        return view('website.academy-profile', [
            'academy' => $academy,
            'rector' => $rector,
            'athletes' => $academy->athletes,
            'personnel' => $associated_personnel,
        ]);
    }

    public function picture(Academy $academy, Request $request) {
        if ($request->file('academylogo') != null) {
            $file = $request->file('academylogo');

            $file_extension = $file->getClientOriginalExtension();
            $file_name = time() . '_logo.' . $file_extension;
            $path = "/academies/" . $academy->id . "/" . $file_name;
            $storeFile = $file->storeAs("/academies/" . $academy->id . "/", $file_name, "gcs");

            if ($storeFile) {

                $academy->picture = $path;
                $academy->save();

                return redirect()->route('academies.edit', $academy->id)->with('success', 'Academy picture uploaded successfully!');
            } else {
                ddd($storeFile);
            }
        } else {
            return redirect()->route('academies.edit', $academy->id)->with('error', 'Error uploading Academy picture!');
        }
    }

    public function academyImage(Academy $academy) {

        $cacheKey = 'academy-img-' . $academy->id;

        $image = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($academy) {

            $url = Storage::disk('gcs')->temporaryUrl(
                $academy->picture,
                now()->addMinutes(5)
            );
            $response = Http::get($url);

            return $response->body();
        });

        $headers = [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($image),
        ];

        return response($image, 200, $headers);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Nation;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AcademyController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

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
        //

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

        $personnel = User::where('is_disabled', '0')->whereNotIn('id', $academy->personnel->pluck('id'))->with(['roles'])->get();

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
            if ($athlete->schoolAthletes->first()) {
                $athlete->school = $athlete->schoolAthletes->first()->name;
            } else {
                $athlete->school = 'Not assigned';
            }
        }


        $athletes = User::whereNotIn('id', $academy->athletes->pluck('id'))->where('is_disabled', '0')->get();

        $roles = Role::all();

        return view('academy.edit', [
            'academy' => $academy,
            'nations' => $countries,
            'schools' => $schools,
            'personnel' => $personnel,
            'athletes' => $athletes,
            'associated_personnel' => $associated_personnel,
            'associated_athletes' => $associated_athletes,
            'roles' => $roles,
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

        return redirect()->route('academies.index', $academy)->with('success', 'Academy updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Academy $academy) {
        //

        $academy->is_disabled = true;
        $academy->save();

        return redirect()->route('academies.index')->with('success', 'Academy disabled successfully!');
    }

    public function schools(Academy $academy) {
        return response()->json($academy->schools);
    }

    public function addSchool(Request $request, Academy $academy) {
        $school = School::find($request->school_id);
        $school->academy_id = $academy->id;
        $school->save();

        return redirect()->route('academies.edit', $academy)->with('success', 'School added successfully!');
    }

    public function addPersonnel(Request $request, Academy $academy) {
        $personnel = User::find($request->personnel_id);

        $academy->personnel()->attach($personnel);

        return redirect()->route('academies.edit', $academy)->with('success', 'Personnel added successfully!');
    }

    public function addAthlete(Request $request, Academy $academy) {
        $athlete = User::find($request->athlete_id);

        $academy->athletes()->attach($athlete);

        return redirect()->route('academies.edit', $academy)->with('success', 'Athlete added successfully!');
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
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=" . env('MAPS_GOOGLE_MAPS_ACCESS_TOKEN');
        $response = file_get_contents($url);
        $json = json_decode($response, true);


        return [
            'lat' => $json['results'][0]['geometry']['location']['lat'],
            'lng' => $json['results'][0]['geometry']['location']['lng'],
            'city' => $json['results'][0]['address_components'][2]['long_name'],
            'state' => $json['results'][0]['address_components'][5]['long_name'],
            'country' => $json['results'][0]['address_components'][6]['long_name'],
        ];
    }

    /** Ricerca lato web */

    private function getCoordinates($location) {
        $location = urlencode($location);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$location}&key=" . env('MAPS_GOOGLE_MAPS_ACCESS_TOKEN');

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
        $radius = $request->radius ? $request->radius : 10;

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
}

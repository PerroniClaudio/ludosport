<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WeaponForm;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WeaponFormController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $weaponForms = WeaponForm::all();

        return view('weapon-forms.index', [
            'weaponForms' => $weaponForms,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(WeaponForm $weaponForm) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WeaponForm $weaponForm) {
        // Può accedere solo l'admin
        $authRole = User::find(auth()->user()->id)->getRole();
        if($authRole !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to view this page');
        }

        $athletes = $weaponForm->users;
        $instructors = $weaponForm->personnel;
        // Lista atleti
        foreach ($athletes as $key => $value) {
            $awarded_on = Carbon::parse($athletes[$key]->pivot->created_at);
            $athletes[$key]['awarded_on'] = $awarded_on->format('d/m/Y');

            $user = User::find($value->id);
            $athletes[$key]['name'] = $user->name . ' ' . $user->surname;
        }
        // Lista personale
        foreach ($instructors as $key => $value) {
            $awarded_on = Carbon::parse($instructors[$key]->pivot->created_at);
            $instructors[$key]['awarded_on'] = $awarded_on->format('d/m/Y');

            $user = User::find($value->id);
            $instructors[$key]['name'] = $user->name . ' ' . $user->surname;

            if ($user->hasRole('instructor')) {
                $instructors[$key]['type'] = __('users.instructor');
            } else {
                $instructors[$key]['type'] = __('users.technician');
            }
        }

        // Possibili atleti da aggiungere
        $athletes_to_add = User::where('is_disabled', '0')->whereNotIn('id', $weaponForm->users->pluck('id'))->whereHas('roles', function ($query) {
            $query->whereIn('name', ['athlete']);
        })->with(['roles'])->get();
        foreach ($athletes_to_add as $key => $athlete) {
            $athletes_to_add[$key]->role = implode(', ', $athlete->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());
        }
        
        // Possibile personale da aggiungere
        $personnel = User::where('is_disabled', '0')->whereNotIn('id', $weaponForm->personnel->pluck('id'))->whereHas('roles', function ($query) {
            $query->whereIn('name', ['instructor', 'technician']);
        })->with(['roles'])->get();
        foreach ($personnel as $key => $person) {
            $personnel[$key]->role = implode(', ', $person->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());
        }

        return view('weapon-forms.edit', [
            'weaponForm' => $weaponForm,
            'instructors' => $instructors,
            'athletes' => $athletes,
            'personnel' => $personnel,
            'athletes_to_add' => $athletes_to_add,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WeaponForm $weaponForm) {
        //

        $request->validate([
            'name' => 'required',
        ]);

        $weaponForm->update($request->all());

        return redirect()->route('weapon-forms.edit', $weaponForm)->with('success', 'Weapon form updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WeaponForm $weaponForm) {
        //
    }

    // public function addPersonnel(Request $request, WeaponForm $weaponForm) {
    //     $users = json_decode($request->users);

    //     foreach ($users as $key => $value) {
    //         $weaponForm->users()->attach($value);
    //     }

    //     return redirect()->route('weapon-forms.edit', $weaponForm)->with('success', 'Personnel added successfully');
    // }

    public function addPersonnel(Request $request, WeaponForm $weaponForm)
    {
        // Può accedere solo l'admin
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if($authRole !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'You are not authorized');
        }

        $usersIds = json_decode($request->users);
        $personnel = User::whereIn('id', $usersIds)->get();

        foreach ($personnel as $person) {
            if(!$person->hasAnyRole(['instructor', 'technician'])) {
                return redirect()->route('weapon-forms.edit', $weaponForm)->with('error', 'Only instructors and technicians can be added');
            }
            $weaponForm->personnel()->syncWithoutDetaching($person->id, [
                'admin_id' => $authUser->id,
            ]);
            // Aggiunge anche la forma da atleta se non ce l'ha già. NON AGGIUNGE IL RUOLO.
            $weaponForm->users()->syncWithoutDetaching($person->id, [
                'admin_id' => $authUser->id,
            ]);
        }

        return redirect()->route('weapon-forms.edit', $weaponForm)->with('success', 'Personnel added successfully');
    }
}

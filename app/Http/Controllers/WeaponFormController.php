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
        //

        $users = $weaponForm->users;
        $athletes = [];
        $instructors = [];


        foreach ($users as $key => $value) {

            $awarded_on = Carbon::parse($users[$key]->pivot->created_at);
            $users[$key]['awarded_on'] = $awarded_on->format('d/m/Y');



            $user = User::find($value->id);
            $users[$key]['name'] = $user->name . ' ' . $user->surname;


            if ($user->hasRole('athlete')) {
                $athletes[] = $users[$key];
            } else {

                if ($user->hasRole('instructor')) {
                    $users[$key]['type'] = __('users.instructor');
                } else {
                    $users[$key]['type'] = __('users.technician');
                }


                $instructors[] = $users[$key];
            }
        }

        $users = User::where('is_disabled', '0')->whereNotIn('id', $weaponForm->users->pluck('id'))->with(['roles'])->get();
        $athletes_to_add = [];
        $personnel = [];
        foreach ($users as $key => $person) {

            if (($person->hasRole('instructor')) || ($person->hasRole('technician'))) {
                $users[$key]->role = implode(', ', $person->roles->pluck('name')->map(function ($role) {
                    return __('users.' . $role);
                })->toArray());

                $personnel[] = $users[$key];
            } else {

                if ($person->hasRole('athlete')) {
                    $users[$key]->role = implode(', ', $person->roles->pluck('name')->map(function ($role) {
                        return __('users.' . $role);
                    })->toArray());

                    $athletes_to_add[] = $users[$key];
                }
            }
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

    public function addPersonnel(Request $request, WeaponForm $weaponForm) {
        $users = json_decode($request->users);

        foreach ($users as $key => $value) {
            $weaponForm->users()->attach($value);
        }

        return redirect()->route('weapon-forms.edit', $weaponForm)->with('success', 'Personnel added successfully');
    }
}

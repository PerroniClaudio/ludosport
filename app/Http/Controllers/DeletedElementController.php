<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Clan;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;

class DeletedElementController extends Controller {
    //

    public function index() {

        $deleted_users = User::where('is_disabled', true)->get();
        $deleted_schools = School::where('is_disabled', true)->get();
        $deleted_academies = Academy::where('is_disabled', true)->get();
        $deleted_courses = Clan::where('is_disabled', true)->get();

        return view('deleted_elements.index', [
            'deleted_users' => $deleted_users,
            'deleted_schools' => $deleted_schools,
            'deleted_academies' => $deleted_academies,
            'deleted_courses' => $deleted_courses,
        ]);
    }

    public function restore(Request $request) {

        switch ($request->element_type) {
            case 'user':
                $element = User::find($request->element_id);

                break;
            case 'school':
                $element = School::find($request->element_id);
                break;
            case 'academy':
                $element = Academy::find($request->element_id);
                break;
            case 'course':
                $element = Clan::find($request->element_id);
                break;
            default:
                return redirect()->route('deleted-elements.index')->with('error', 'Element type not found');
        }

        $element->is_disabled = false;
        $element->save();

        return redirect()->route('deleted-elements.index')->with('success', 'Element restored successfully');
    }
}

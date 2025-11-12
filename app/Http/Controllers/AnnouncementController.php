<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Announcement;
use App\Models\AnnouncementUser;
use App\Models\Nation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AnnouncementController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $announcements = Announcement::where('is_deleted', false)->where('type', '!=', '4')->orderBy('id', 'desc')->get();

        foreach ($announcements as $announcement) {
            if ($announcement->roles !== null) {
                foreach (json_decode($announcement->roles) as $roles) {
                    $announcement->target .= __('users.' . Role::find($roles)->name . '_role') . ',';
                }
            } else {
                $announcement->target = __('users.' . $announcement->role->name . '_role');
            }

            $announcement->target = rtrim($announcement->target, ',');
        }





        return view('announcements.index', [
            'announcements' => $announcements
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        $announcement = new Announcement();
        $types = $announcement->getTypes();

        $academies = Academy::all();
        $academies = $academies->map(function ($academy) {
            return [
                'id' => $academy->id,
                'name' => $academy->name
            ];
        });


        $nations = Nation::all();

        $roles = Role::all();

        $roles = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => __('users.' . $role->name)
            ];
        });

        $typesOptions = [];
        foreach ($types as $key => $type) {
            $typesOptions[] = [
                'value' => $key,
                'label' => __('announcements.' . $type)
            ];
        }


        return view('announcements.create', [
            'types' => $typesOptions,
            'roles' => $roles,
            'nations' => $nations,
            'academies' => $academies
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //

        $request->validate([
            'object' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|integer',
        ]);

        if ($request->selectedNations != null) {
            $nations = $request->selectedNations;
        } else {
            $nations = "[]";
        }

        if ($request->selectedAcademies != null) {
            $academies =  $request->selectedAcademies;
        } else {
            $academies = "[]";
        }

        if ($request->selectedRoles != null) {
            $roles = $request->selectedRoles;
        } else {
            $roles = "[]";
        }

        $announcement = new Announcement([
            'object' => $request->object,
            'content' => $request->content,
            'type' => $request->type,
            'role_id' => 1,
            'nations' => $nations,
            'academies' => $academies,
            'roles' =>  $roles
        ]);

        $announcement->save();

        return redirect()->route('announcements.edit', $announcement->id)->with('success', 'Announcement created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement) {
        //

        $userhaveseen = $announcement->userHaveSeen()->with(['roles'])->get();

        foreach ($userhaveseen as $key => $user) {
            $userhaveseen[$key]['name'] = $user->name . ' ' . $user->surname;
            $userhaveseen[$key]['role'] = implode(', ', $user->roles->pluck('name')->map(function ($role) {
                return __('users.' . $role);
            })->toArray());

            /** Kai seen_at lmao */
            $userhaveseen[$key]['seen_at'] = $user->pivot->created_at;
        }


        $types = $announcement->getTypes();

        $roles = Role::all();
        $roles = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => __('users.' . $role->name)
            ];
        });

        $typesOptions = [];
        foreach ($types as $key => $type) {
            $typesOptions[] = [
                'value' => $key,
                'label' => __('announcements.' . $type)
            ];
        }

        $academies = Academy::all();
        $academies = $academies->map(function ($academy) {
            return [
                'id' => $academy->id,
                'name' => $academy->name
            ];
        });


        $nations = Nation::all();

        return view('announcements.edit', [
            'announcement' => $announcement,
            'types' => $typesOptions,
            'roles' => $roles,
            'haveseen' => $userhaveseen,
            'nations' => $nations,
            'academies' => $academies
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement) {
        //

        $request->validate([
            'object' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|integer',
            'role' => 'integer'
        ]);

        $announcement->object = $request->object;
        $announcement->content = $request->content;
        $announcement->type = $request->type;

        if ($request->selectedNations != null) {
            $nations = $request->selectedNations;
        } else {
            $nations = "[]";
        }

        if ($request->selectedAcademies != null) {
            $academies =  $request->selectedAcademies;
        } else {
            $academies = "[]";
        }

        if ($request->selectedRoles != null) {
            $roles = $request->selectedRoles;
        } else {
            $roles = "[]";
        }

        $announcement->nations = $nations;
        $announcement->academies = $academies;
        $announcement->roles = $roles;

        $announcement->save();

        return redirect()->route('announcements.edit', $announcement->id)->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement) {
        //

        $announcement->is_deleted = true;
        $announcement->save();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted successfully.');
    }

    public function athlete() {

        $auth = auth()->user();
        $user = User::find($auth->id);

        $user_roles = $user->roles->pluck('id')->toArray();

        $seen_announcements = $user->seenAnnouncements()->get();
        $announcements = Announcement::where('is_deleted', false)
            ->where('type', '!=', '4')
            ->orderBy('created_at', 'desc')
            ->get();



        // Verifica se sei della nazione giusta ed accademia giusta per visualizzare l'annuncio

        $announcements = $announcements->filter(function ($announcement) use ($user) {
            $nations = json_decode($announcement->nations);
            $academies = json_decode($announcement->academies);
            $roles = json_decode($announcement->roles);

            if ($nations != null) {
                if (!in_array($user->nation_id, $nations)) {
                    return false;
                }
            }

            if ($academies != null) {
                if (!in_array($user->academy_id, $academies)) {
                    return false;
                }
            }

            if ($roles != null) {
                if (!array_intersect($user->roles->pluck('id')->toArray(), $roles)) {
                    return false;
                }
            }

            return true;
        });

        $first_announcement = $announcements->first();

        if ($first_announcement) {
            if (!in_array($first_announcement->id, $seen_announcements->pluck('id')->toArray())) {
                AnnouncementUser::create([
                    'announcement_id' => $first_announcement->id,
                    'user_id' => $user->id
                ]);

                $seen_announcements = $user->seenAnnouncements()->get();
            }
        }


        return view('announcements.athlete', [
            'seen_announcements' => $seen_announcements,
            'announcements' => $announcements,
            'active_announcement' => $first_announcement
        ]);
    }

    // public function technician() {
    public function ownRoles() {

        $auth = auth()->user();
        $user = User::find($auth->id);

        $seen_announcements = $user->seenAnnouncements()->get();
        $announcements = Announcement::where('is_deleted', false)->where('type', '!=', '4')->orderBy('created_at', 'desc')->get();
        $direct_messages = Announcement::where([['is_deleted', false], ['type', '4'], ['user_id', $user->id]])->orderBy('created_at', 'desc')->get();
        $announcements = $announcements->merge($direct_messages);

        // Verifica se sei della nazione giusta ed accademia giusta per visualizzare l'annuncio

        $announcements = $announcements->filter(function ($announcement) use ($user) {

            $nations = $announcement->nations != null ? json_decode($announcement->nations) : null;
            $academies = $announcement->academies != null ? $academies = json_decode($announcement->academies) : null;
            $allowed_roles = $announcement->roles != null ? json_decode($announcement->roles) : null;

            if ($nations != null) {
                if (!in_array($user->nation_id, $nations)) {
                    return false;
                }
            }

            if ($academies != null) {

                if ($allowed_roles == null) {
                    $allAcademies = $user->academies->pluck('id')->merge($user->primaryAcademyAthlete() ? [$user->primaryAcademyAthlete()->id] : []);
                    if (!array_intersect($allAcademies->toArray(), $academies)) {
                        return false;
                    }
                } else {
                    $athleteRoleId = Role::where('name', 'athlete')->first()->id;
                    $canSee = false;
                    if (in_array($athleteRoleId, $allowed_roles)) {
                        $primaryAcademyAthlete = $user->primaryAcademyAthlete() ? $user->primaryAcademyAthlete()->id : null;
                        if (in_array($primaryAcademyAthlete, $academies)) {
                            $canSee = true;
                        }
                    }
                    if (array_intersect($user->roles->where('id', '!=', $athleteRoleId)->pluck('id')->toArray(), $allowed_roles)) {
                        // $allAcademiesPersonnel = $user->academies->pluck('id')->toArray();
                        // $primaryAcademyPersonnel = $user->primaryAcademy() ? $user->getActiveInstitutionId() : null;
                        // Alcuni ruoli possono non avere l'active institution settata. quindi se c'è si prende quella, altirmenti la primary academy
                        $primaryAcademyPersonnel = $user->getActiveInstitution() 
                            ? $user->getActiveInstitutionId() 
                            : ($user->primaryAcademy() 
                                ? $user->primaryAcademy()->id 
                                : null);
                        if (in_array($primaryAcademyPersonnel, $academies)) {
                            $canSee = true;
                        }
                    }

                    if (!$canSee) {
                        return false;
                    }
                }
            }

            if ($allowed_roles != null) {

                /** 
                 * 09/12/2024 - cambio funzione, adesso vede solo gli annunci per il ruolo scelto nella sessione attiva. 
                 * 16/12/2024 - modifica revertata
                 */

                if (!array_intersect($user->roles->pluck('id')->toArray(), $allowed_roles)) {
                    return false;
                }
            }

            return true;
        });


        $first_announcement = $announcements->first();

        if ($first_announcement) {
            if (!in_array($first_announcement->id, $seen_announcements->pluck('id')->toArray())) {
                AnnouncementUser::create([
                    'announcement_id' => $first_announcement->id,
                    'user_id' => $user->id
                ]);

                $seen_announcements = $user->seenAnnouncements()->get();
            }
        } else {
            $first_announcement = "";
        }

        return view('announcements.allroles', [
            'seen_announcements' => $seen_announcements,
            'announcements' => $announcements,
            'active_announcement' => $first_announcement,
        ]);
    }

    public function setSeen(Announcement $announcement) {
        $authUser = User::find(auth()->user()->id);
        $seen_announcements = $authUser->seenAnnouncements()->get();

        if (!in_array($announcement->id, $seen_announcements->pluck('id')->toArray())) {
            AnnouncementUser::create([
                'announcement_id' => $announcement->id,
                'user_id' => $authUser->id
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }
}

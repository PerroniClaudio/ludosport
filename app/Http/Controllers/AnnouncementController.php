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

        $announcements = Announcement::where('is_deleted', false)->where('type', '!=', '4')->get();

        foreach ($announcements as $announcement) {
            $announcement->target = __('users.' . $announcement->role->name . '_role');
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
        $rolesOptions = [];
        foreach ($roles as $role) {
            $rolesOptions[] = [
                'value' => $role->id,
                'label' => __('users.' . $role->name)
            ];
        }

        $typesOptions = [];
        foreach ($types as $key => $type) {
            $typesOptions[] = [
                'value' => $key,
                'label' => __('announcements.' . $type)
            ];
        }

        return view('announcements.create', [
            'types' => $typesOptions,
            'roles' => $rolesOptions,
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
            'role' => 'integer'
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

        $announcement = new Announcement([
            'object' => $request->object,
            'content' => $request->content,
            'type' => $request->type,
            'role_id' => $request->role,
            'nations' => $nations,
            'academies' => $academies
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
        }


        $types = $announcement->getTypes();

        $roles = Role::all();
        $rolesOptions = [];
        foreach ($roles as $role) {
            $rolesOptions[] = [
                'value' => $role->id,
                'label' => __('users.' . $role->name)
            ];
        }

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
            'roles' => $rolesOptions,
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
        $announcement->role_id = $request->role;

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

        $announcement->nations = $nations;
        $announcement->academies = $academies;

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

        $seen_announcements = $user->seenAnnouncements()->get();
        $announcements = Announcement::where('is_deleted', false)->whereIn('role_id', $user->roles->pluck('id'))->where('type', '!=', '4')->orderBy('created_at', 'desc')->get();

        // Verifica se sei della nazione giusta ed accademia giusta per visualizzare l'annuncio

        $announcements = $announcements->filter(function ($announcement) use ($user) {
            $nations = json_decode($announcement->nations);
            $academies = json_decode($announcement->academies);

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
        $announcements = Announcement::where('is_deleted', false)->whereIn('role_id', $user->roles->pluck('id'))->where('type', '!=', '4')->orderBy('created_at', 'desc')->get();
        $direct_messages = Announcement::where([['is_deleted', false], ['type', '4'], ['user_id', $user->id]])->orderBy('created_at', 'desc')->get();
        $announcements = $announcements->merge($direct_messages);

        // Verifica se sei della nazione giusta ed accademia giusta per visualizzare l'annuncio

        $announcements = $announcements->filter(function ($announcement) use ($user) {
            $nations = json_decode($announcement->nations);
            $academies = json_decode($announcement->academies);

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

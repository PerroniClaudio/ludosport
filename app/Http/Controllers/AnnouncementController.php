<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Role;
use Illuminate\Http\Request;

class AnnouncementController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $announcements = Announcement::where('is_deleted', false)->get();

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
            'roles' => $rolesOptions
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

        $announcement = new Announcement([
            'object' => $request->object,
            'content' => $request->content,
            'type' => $request->type,
            'role_id' => $request->role
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

        return view('announcements.edit', [
            'announcement' => $announcement,
            'types' => $typesOptions,
            'roles' => $rolesOptions
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
}

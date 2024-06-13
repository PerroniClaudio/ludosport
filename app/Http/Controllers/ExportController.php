<?php

namespace App\Http\Controllers;

use App\Models\Export;
use App\Models\Role;
use Illuminate\Http\Request;

class ExportController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $exports = Export::with('user')->orderBy('created_at', 'desc')->get();

        foreach ($exports as $key => $export) {
            $exports[$key]->type = __('exports.' . $export->type);
            $exports[$key]->status = __('exports.' . $export->status);
        }

        return view('export.index', [
            'exports' => $exports
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        $export = new Export();
        $types = $export->getExportTypes();
        $typesSelect = [];

        foreach ($types as $type) {
            $typesSelect[] = [
                'value' => $type,
                'label' => __('exports.' . $type)
            ];
        }

        $roles = Role::all();

        foreach ($roles as $key => $role) {
            $roles[$key]->name = __('users.' . $role->name);
        }

        return view('export.create', [
            'types' => $typesSelect,
            'roles' => $roles
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //  

        $export = new Export();
        $exportTypes = $export->getExportTypes()->toArray();

        $request->validate([
            'type' => 'required|string|in:' . implode(',', $exportTypes)
        ]);

        $filters = [];

        switch ($request->type) {
            case 'users':
                $filters = [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date
                ];
                break;
            case 'users_course':
                $filters = [
                    "users_type" => $request->users_type,
                    "courses" => json_decode($request->filters, true),
                ];
                break;
            case 'users_academy':
                $filters = [
                    "users_type" => $request->users_type,
                    "academies" => json_decode($request->filters, true),
                ];
                break;
            case 'users_school':
                $filters = [
                    "users_type" => $request->users_type,
                    "schools" => json_decode($request->filters, true),
                ];
                break;
            default:

                break;
        }

        $export->type = $request->type;
        $export->status = 'pending';
        $export->user_id = auth()->id();
        $export->filters = json_encode($filters);
        $export->file = '';
        $export->log = "['Export requested at " . now()->format('Y-m-d H:i:s') . "']";

        $export->save();

        return redirect()->route('exports.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Export $export) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Export $export) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Export $export) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Export $export) {
        //
    }
}

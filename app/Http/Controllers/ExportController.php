<?php

namespace App\Http\Controllers;

use App\Exports\EventParticipantsExport;
use App\Exports\EventsStyleExport;
use App\Exports\EventsWarExport;
use App\Exports\UsersAcademyExport;
use App\Exports\UsersCourseExport;
use App\Exports\UsersExport;
use App\Exports\UsersRoleExport;
use App\Exports\UsersSchoolExport;
use App\Models\Export;
use App\Models\Role;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
            case 'user_roles':
                $filters = [
                    'selected_roles' => json_decode($request->selected_roles, true),
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

            case 'event_participants':
            case 'event_war':
            case 'event_style':
                $filters = [
                    "filters" => json_decode($request->filters, true),
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

    public function resolvePendingExports() {

        $exports = Export::where('status', 'pending')->get();

        foreach ($exports as $export) {
            $export->status = 'processing';
            $log = json_encode($export->log);
            $export->save();

            $log[] = "['Export started at " . now()->format('Y-m-d H:i:s') . "']";

            switch ($export->type) {
                case 'users':
                    $log[] = "['Exporting users']";
                    $file_path = 'exports/' . $export->id . '/users.xlsx';
                    Excel::export(new UsersExport($export), $file_path, 'gcs');
                    $export->file = $file_path;
                    $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                    break;

                case 'user_roles':
                    $log[] = "['Exporting user roles']";
                    $file_path = 'exports/' . $export->id . '/user_roles.xlsx';
                    Excel::export(new UsersRoleExport($export), $file_path, 'gcs');
                    $export->file = $file_path;
                    $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                    break;
                case 'users_course':
                    $log = "['Exporting users course']";
                    $file_path = 'exports/' . $export->id . '/users_course.xlsx';
                    Excel::export(new UsersCourseExport($export), $file_path, 'gcs');
                    $export->file = $file_path;
                    $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                    break;
                case 'users_academy':
                    $log = "['Exporting users academy']";
                    $file_path = 'exports/' . $export->id . '/users_academy.xlsx';
                    Excel::export(new UsersAcademyExport($export), $file_path, 'gcs');
                    $export->file = $file_path;
                    $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                    break;
                case 'users_school':
                    $log = "['Exporting users school']";
                    $file_path = 'exports/' . $export->id . '/users_school.xlsx';
                    Excel::export(new UsersSchoolExport($export), $file_path, 'gcs');
                    $export->file = $file_path;
                    $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                    break;
                case 'event_participants':
                    $log = "['Exporting event participants']";
                    $file_path = 'exports/' . $export->id . '/event_participants.xlsx';
                    Excel::export(new EventParticipantsExport($export), $file_path, 'gcs');
                    $export->file = $file_path;
                    $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                    break;
                case 'event_war':
                    $log = "['Exporting event war points']";
                    $file_path = 'exports/' . $export->id . '/event_war_points.xlsx';
                    Excel::export(new EventsWarExport($export), $file_path, 'gcs');
                    $export->file = $file_path;
                    $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                    break;
                case 'event_style':
                    $log = "['Exporting event style points']";
                    $file_path = 'exports/' . $export->id . '/event_style_points.xlsx';
                    Excel::export(new EventsStyleExport($export), $file_path, 'gcs');
                    $export->file = $file_path;
                    $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                    break;
                default:
                    break;
            }

            $export->log = json_encode($log);
            $export->status = 'finished';
            $export->save();
        }
    }
}

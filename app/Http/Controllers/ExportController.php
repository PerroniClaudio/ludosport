<?php

namespace App\Http\Controllers;

use App\Exports\EventsInstructorResultsExport;
use App\Exports\EventsParticipantsExport;
use App\Exports\EventsStyleExport;
use App\Exports\EventsWarExport;
use App\Exports\OrdersExport;
use App\Exports\UsersAcademyExport;
use App\Exports\UsersCourseExport;
use App\Exports\UsersExport;
use App\Exports\UsersNationExport;
use App\Exports\UsersRoleExport;
use App\Exports\UsersSchoolExport;
use App\Models\Export;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {

        return redirect()->route('dashboard')->with('error', 'The function is temporarily disabled');
        //
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if($authRole != 'admin') {
            return redirect()->route('dashboard')->with('error', 'The function is temporarily disabled');
        }


        if (!$authUser->validatePrimaryInstitutionPersonnel()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page');
        }

        $exports = Export::whereIn('type', Export::getAvailableExportsByRole($authRole))->with('user')->orderBy('created_at', 'desc')->get();

        $addToRoute = $authRole == 'admin' ? '' : $authRole . '.';

        foreach ($exports as $key => $export) {

            if ($export->status == "finished") {
                $exports[$key]->url = route($addToRoute . 'exports.download', $export);
            }

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
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if (!$authUser->validatePrimaryInstitutionPersonnel()) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page');
        }

        $export = new Export();
        $types = $export->getAvailableExportsByRole($authRole);
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

        $authRole = User::find(auth()->user()->id)->getRole();
        $viewPath = $authRole == 'admin' ? 'export.create' : 'export.' . $authRole . '.create';
        return view($viewPath, [
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
            case 'orders':
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
            case 'users_nation':
                $filters = [
                    "users_type" => $request->users_type,
                    "nations" => json_decode($request->filters, true),
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
            case 'users_course':
                $filters = [
                    "users_type" => $request->users_type,
                    "courses" => json_decode($request->filters, true),
                ];
                break;

            case 'event_participants':
            case 'instructor_event_results':
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

        $authRole = User::find(auth()->user()->id)->getRole();

        $redirectRoute = $authRole == 'admin' ? 'exports.index' : $authRole . '.exports.index';
        return redirect()->route($redirectRoute);
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
            $log = json_decode($export->log);
            $export->save();

            $log[] = "['Export started at " . now()->format('Y-m-d H:i:s') . "']";

            try {
                switch ($export->type) {
                    case 'users':
                        $log[] = "['Exporting users']";
                        $file_path = 'exports/' . $export->id . '/users.xlsx';
                        Excel::store(new UsersExport($export), $file_path, 'gcs');
                        $export->file = $file_path;
                        $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'orders':
                        $log[] = "['Exporting orders']";
                        $file_path = 'exports/' . $export->id . '/orders.xlsx';
                        Excel::store(new OrdersExport($export), $file_path, 'gcs');
                        $export->file = $file_path;
                        $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                        break;

                    case 'user_roles':
                        $log[] = "['Exporting user roles']";
                        $file_path = 'exports/' . $export->id . '/user_roles.xlsx';
                        Excel::store(new UsersRoleExport($export), $file_path, 'gcs');
                        $export->file = $file_path;
                        $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'users_course':
                        $log[] = "['Exporting users course']";
                        $file_path = 'exports/' . $export->id . '/users_course.xlsx';
                        Excel::store(new UsersCourseExport($export), $file_path, 'gcs');
                        $export->file = $file_path;
                        $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'users_nation':
                        $log[] = "['Exporting users nation']";
                        $file_path = 'exports/' . $export->id . '/users_nation.xlsx';
                        Excel::store(new UsersNationExport($export), $file_path, 'gcs');
                        $export->file = $file_path;
                        $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'users_academy':
                        $log[] = "['Exporting users academy']";
                        $file_path = 'exports/' . $export->id . '/users_academy.xlsx';
                        Excel::store(new UsersAcademyExport($export), $file_path, 'gcs');
                        $export->file = $file_path;
                        $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'users_school':
                        $log[] = "['Exporting users school']";
                        $file_path = 'exports/' . $export->id . '/users_school.xlsx';
                        Excel::store(new UsersSchoolExport($export), $file_path, 'gcs');
                        $export->file = $file_path;
                        $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'event_participants':
                        $log[] = "['Exporting event participants']";
                        $file_path = 'exports/' . $export->id . '/event_participants.xlsx';
                        Excel::store(new EventsParticipantsExport($export), $file_path, 'gcs');
                        $export->file = $file_path;
                        $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'instructor_event_results':
                        $log[] = "['Exporting event results']";
                        $file_path = 'exports/' . $export->id . '/event_results.xlsx';
                        Excel::store(new EventsInstructorResultsExport($export), $file_path, 'gcs');
                        $export->file = $file_path;
                        $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'event_war':
                        $log[] = "['Exporting event arena points']";
                        $file_path = 'exports/' . $export->id . '/event_arena_points.xlsx';
                        Excel::store(new EventsWarExport($export), $file_path, 'gcs');
                        $export->file = $file_path;
                        $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'event_style':
                        $log[] = "['Exporting event style points']";
                        $file_path = 'exports/' . $export->id . '/event_style_points.xlsx';
                        Excel::store(new EventsStyleExport($export), $file_path, 'gcs');
                        $export->file = $file_path;
                        $log[] = "['Export finished at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    default:
                        break;
                }

                $export->log = json_encode($log);
                $export->status = 'finished';
                $export->save();
            } catch (\Exception $e) {
                $log[] = "['Error exporting file']";
                $log[] = "['" . $e->getMessage() . "']";
                $export->log = json_encode($log);
                $export->status = 'error';
                $export->save();
            }
        }
    }

    public function download(Export $export) {
        /** 
         * @disregard Intelephense non rileva il metodo temporaryurl
         * 
         * @see https://github.com/spatie/laravel-google-cloud-storage
         */
        $file = Storage::disk('gcs')->temporaryUrl(
            $export->file,
            now()->addMinutes(5)
        );

        $file2 = file_get_contents($file);
        $filename = basename($export->file);

        return response($file2)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

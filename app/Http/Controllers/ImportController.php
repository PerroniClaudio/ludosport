<?php

namespace App\Http\Controllers;

use App\Exports\TemplateExport;
use App\Imports\EventInstructorImport;
use App\Imports\EventStyleImport;
use App\Imports\EventWarImport;
use App\Imports\UsersAcademyImport;
use App\Imports\UsersCourseImport;
use App\Imports\UsersEventImport;
use App\Imports\UsersImport;
use App\Imports\UsersSchoolImport;
use App\Models\Event;
use App\Models\Import;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        // new_users,users_course,users_academy,users_school,event_participants,event_war,event_style
        $authRole = User::find(auth()->user()->id)->getRole();

        $imports = Import::whereIn('type', Import::getAvailableImportsByRole($authRole))->with('user')->orderBy('created_at', 'desc')->get();

        foreach ($imports as $key => $import) {
            $imports[$key]->type = __('imports.' . $import->type);
            $imports[$key]->status = __('imports.' . $import->status);
            $imports[$key]->author = $import->user->name . ' ' . ($import->user->surname ?? '');
            $imports[$key]->created_at_formatted = $import->created_at->format('d/m/Y H:i');
        }

        return view('import.index', [
            'imports' => $imports
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
        $authRole = User::find(auth()->user()->id)->getRole();

        // $types = $import->getImportTypes();
        $types = Import::getAvailableImportsByRole($authRole);
        $typesSelect = [];

        foreach ($types as $type) {
            $typesSelect[] = [
                'value' => $type,
                'label' => __('imports.' . $type)
            ];
        }

        $instructorEvents = Event::all()->filter(function ($event) {
            return $event->resultType() == 'enabling';
        })->map(function ($event) {
            return [
                'value' => $event->id,
                'label' => $event->name
            ];
        });

        $rankingEvents = Event::all()->filter(function ($event) {
            return $event->resultType() == 'ranking';
        })->map(function ($event) {
            return [
                'value' => $event->id,
                'label' => $event->name
            ];
        });

        $authRole = User::find(auth()->user()->id)->getRole();
        $viewPath = $authRole == 'admin' ? 'import.create' : 'import.' . $authRole . '.create';
        return view($viewPath, [
            'types' => $typesSelect,
            'instructorEvents' => $instructorEvents,
            'rankingEvents' => $rankingEvents
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //

        $request->validate([
            'type' => 'required|in:new_users,users_course,users_academy,users_school,event_participants,event_war,event_style,event_instructor_results',
        ]);

        $import = Import::create([
            'file' => '',
            'status' => 'pending',
            'type' => $request->type,
            'log' => "['File uploaded at " . now()->format('Y-m-d H:i:s') . "']",
            'user_id' => auth()->id()
        ]);

        // File upload

        $file = $request->file('file');
        $file_name = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $path = "imports/{$import->id}/{$file_name}";
        $storeFile = $file->storeAs("imports/{$import->id}/", $file_name, "gcs");

        $authRole = User::find(auth()->user()->id)->getRole();

        if ($storeFile) {
            $import->file = $path;
            $import->save();

            $redirectRoute = $authRole == 'admin' ? 'imports.index' : $authRole . '.imports.index';
            return redirect()->route($redirectRoute);
        } else {
            $redirectRoute = $authRole == 'admin' ? 'imports.create' : $authRole . '.imports.create';
            return redirect()->route($redirectRoute)->with('error', 'Error uploading file');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Import $import) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Import $import) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Import $import) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Import $import) {
        //
    }

    public function template(Request $request) {

        $type = $request->type;
        $eventId = $request->event_id;

        $name = $type . time() . '.xlsx';
        return Excel::download(new TemplateExport($type, $eventId), $name);
    }

    public function resolvePendingImports() {
        $imports = Import::where('status', 'pending')->get();

        foreach ($imports as $import) {
            $import->status = 'processing';
            $import->save();
            $is_partial = false;

            $log = json_decode($import->log);


            $log[] = "['File downloaded at " . now()->format('Y-m-d H:i:s') . "']";

            try {
                switch ($import->type) {
                    case 'new_users':
                        $log[] = "['Processing new users']";
                        $usersImport = new UsersImport($import->user);
                        Excel::import($usersImport, $import->file, 'gcs');
                        $usersImportLog = $usersImport->getLogArray();
                        if(count($usersImportLog) > 0) {
                            array_push($log, ...$usersImportLog);
                        }
                        $is_partial = $usersImport->getIsPartial();
                        $log[] = "['Users imported at " . now()->format('Y-m-d H:i:s') . "']";

                        break;
                    case 'users_course':
                        $log[] = "['Processing users course']";
                        $usersCourseImport = new UsersCourseImport($import->user);
                        Excel::import($usersCourseImport, $import->file, 'gcs');
                        $usersCourseImportLog = $usersCourseImport->getLogArray();
                        if(count($usersCourseImportLog) > 0) {
                            array_push($log, ...$usersCourseImportLog);
                        }
                        $is_partial = $usersCourseImport->getIsPartial();
                        $log[] = "['Users course imported at " . now()->format('Y-m-d H:i:s') . "']";

                        break;
                    case 'users_academy':
                        $log[] = "['Processing users academy']";
                        $usersAcademyImport = new UsersAcademyImport($import->user);
                        Excel::import($usersAcademyImport, $import->file, 'gcs');
                        $usersAcademyImportLog = $usersAcademyImport->getLogArray();
                        if(count($usersAcademyImportLog) > 0) {
                            array_push($log, ...$usersAcademyImportLog);
                        }
                        $is_partial = $usersAcademyImport->getIsPartial();
                        $log[] = "['Users academy imported at " . now()->format('Y-m-d H:i:s') . "']";

                        break;
                    case 'users_school':
                        $log[] = "['Processing users school']";
                        $usersSchoolImport = new UsersSchoolImport($import->user);
                        Excel::import($usersSchoolImport, $import->file, 'gcs');
                        $usersSchoolImportLog = $usersSchoolImport->getLogArray();
                        if(count($usersSchoolImportLog) > 0) {
                            array_push($log, ...$usersSchoolImportLog);
                        }
                        $is_partial = $usersSchoolImport->getIsPartial();
                        $log[] = "['Users school imported at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'event_participants':
                        $log[] = "['Processing event participants']";
                        $userEventImport = new UsersEventImport($import->user);
                        Excel::import($userEventImport, $import->file, 'gcs');
                        $userEventImportLog = $userEventImport->getLogArray();
                        if(count($userEventImportLog) > 0) {
                            array_push($log, ...$userEventImportLog);
                        }
                        $is_partial = $userEventImport->getIsPartial();
                        $log[] = "['Event participants imported at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'event_war':
                        $log[] = "['Processing event war']";
                        $EventWarImport = new EventWarImport($import->user);
                        Excel::import($EventWarImport, $import->file, 'gcs');
                        $EventWarImportLog = $EventWarImport->getLogArray();
                        if(count($EventWarImportLog) > 0) {
                            array_push($log, ...$EventWarImportLog);
                        }
                        $is_partial = $EventWarImport->getIsPartial();
                        $log[] = "['Event war imported at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'event_style':
                        $log[] = "['Processing event style']";
                        $eventStyleImport = new EventStyleImport($import->user);
                        Excel::import($eventStyleImport, $import->file, 'gcs');
                        $eventStyleImportLog = $eventStyleImport->getLogArray();
                        if(count($eventStyleImportLog) > 0) {
                            array_push($log, ...$eventStyleImportLog);
                        }
                        $is_partial = $eventStyleImport->getIsPartial();
                        $log[] = "['Event style imported at " . now()->format('Y-m-d H:i:s') . "']";
                        break;
                    case 'event_instructor_results':
                        $log[] = "['Processing event instructor']";
                        $eventInstructorImport = new EventInstructorImport($import->user);
                        Excel::import($eventInstructorImport, $import->file, 'gcs');
                        $eventInstructorImportLog = $eventInstructorImport->getLogArray();
                        if(count($eventInstructorImportLog) > 0) {
                            array_push($log, ...$eventInstructorImportLog);
                        }
                        $is_partial = $eventInstructorImport->getIsPartial();
                        $log[] = "['Event instructor imported at " . now()->format('Y-m-d H:i:s') . "']";
                        break;

                    default:
                        break;
                }

                $import->log = json_encode($log);

                $import->status = $is_partial ? 'partial' : 'completed';
                $import->save();
            } catch (\Exception $e) {
                $log[] = "['Error exporting file']";
                $log[] = "['" . $e->getMessage() . "']";
                $import->log = json_encode($log);
                $import->status = 'error';
                $import->save();
            }
        }
    }
}

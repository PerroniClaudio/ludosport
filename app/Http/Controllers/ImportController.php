<?php

namespace App\Http\Controllers;

use App\Exports\TemplateExport;
use App\Imports\UsersAcademyImport;
use App\Imports\UsersCourseImport;
use App\Imports\UsersImport;
use App\Imports\UsersSchoolImport;
use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $imports = Import::with('user')->orderBy('created_at', 'desc')->get();

        foreach ($imports as $key => $import) {
            $imports[$key]->type = __('imports.' . $import->type);
            $imports[$key]->status = __('imports.' . $import->status);
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

        $import = new Import();
        $types = $import->getImportTypes();
        $typesSelect = [];

        foreach ($types as $type) {
            $typesSelect[] = [
                'value' => $type,
                'label' => __('imports.' . $type)
            ];
        }

        return view('import.create', [
            'types' => $typesSelect
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //

        $request->validate([
            'type' => 'required|in:new_users,users_course,users_academy,users_school'
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
        $path = "imports/4316/{$file_name}";
        $storeFile = $file->storeAs("imports/4316/", $file_name, "gcs");


        if ($storeFile) {
            $import->file = $path;
            $import->save();

            return redirect()->route('imports.index');
        } else {
            return redirect()->route('imports.create')->with('error', 'Error uploading file');
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

        $name = $type . time() . '.xlsx';
        return Excel::download(new TemplateExport($type), $name);
    }

    public function resolvePendingImports() {
        $imports = Import::where('status', 'pending')->get();

        foreach ($imports as $import) {
            $import->status = 'processing';
            $import->save();

            $log = json_decode($import->log);


            $log[] = "['File downloaded at " . now()->format('Y-m-d H:i:s') . "']";

            switch ($import->type) {
                case 'new_users':
                    $log[] = "['Processing new users']";
                    Excel::import(new UsersImport, $import->file, 'gcs');
                    $log[] = "['Users imported at " . now()->format('Y-m-d H:i:s') . "']";

                    break;
                case 'users_course':
                    $log[] = "['Processing users course']";
                    Excel::import(new UsersCourseImport, $import->file, 'gcs');
                    $log[] = "['Users course imported at " . now()->format('Y-m-d H:i:s') . "']";

                    break;
                case 'users_academy':
                    $log[] = "['Processing users academy']";
                    Excel::import(new UsersAcademyImport, $import->file, 'gcs');
                    $log[] = "['Users academy imported at " . now()->format('Y-m-d H:i:s') . "']";

                    break;
                case 'users_school':
                    $log[] = "['Processing users school']";
                    Excel::import(new UsersSchoolImport, $import->file, 'gcs');
                    $log[] = "['Users school imported at " . now()->format('Y-m-d H:i:s') . "']";
                    break;
                default:
                    break;
            }

            $import->log = json_encode($log);
            $import->status = 'completed';
            $import->save();
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Export;
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

        return view('export.create', [
            'types' => $typesSelect
        ]);
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

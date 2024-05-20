<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $user = auth()->user();

        if ($user->role === 'tecnico') {
            $view = 'event.technician.index';
            $events = Event::where('user_id', $user->id)->get();
        } else {
            $view = 'event.index';
            $events = Event::all();
        }

        foreach ($events as $event) {
            if ($event->is_pending) {
                $pending[] = $event;
            } else {
                $approved[] = $event;
            }
        }



        return view($view, [
            'approved_events' => $approved ?? [],
            'pending_events' =>  $pending ?? [],
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
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
    public function show(Event $event) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event) {
        //
    }
}

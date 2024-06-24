<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\Request;

class EventTypeController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $eventTypes = EventType::where('is_disabled', false)->get();

        foreach ($eventTypes as $eventType) {
            $eventType->events_number = $eventType->events->count();
        }

        return view('event.types.index', [
            'eventTypes' => $eventTypes,
        ]);
    }

    public function list() {
        $eventTypes = EventType::where('is_disabled', false)->get();

        return response()->json([
            'eventTypes' => $eventTypes,
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

        $request->validate([
            'name' => 'required|string|unique:event_types,name',
        ]);

        EventType::create($request->all());

        return response()->json([
            'message' => 'Event type created successfully',
        ], 201);

        //return redirect()->route('events.update', ['event' => $request->event_id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(EventType $eventType) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventType $eventType) {
        //

        $otherEvents = Event::where('event_type', "!=", $eventType->id)->get();

        return view('event.types.edit', [
            'eventType' => $eventType,
            'events' => $otherEvents,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventType $eventType) {
        //

        $request->validate([
            'name' => 'required|string|unique:event_types,name,' . $eventType->id,
        ]);

        $eventType->update($request->all());

        return redirect()->route('events.update_type', ['eventType' => $eventType])->with('success', 'Event type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventType $eventType) {
        //

        $eventType->is_disabled = true;
        $eventType->save();

        return redirect()->route('events.list_types')->with('success', 'Event type disabled successfully!');
    }

    public function associate_event(Request $request, EventType $eventType) {
        $event = Event::find($request->event_id);
        $event->event_type = $eventType->id;
        $event->save();

        return redirect()->route('events.update_type', ['eventType' => $eventType])->with('success', 'Event associated successfully!');
    }
}

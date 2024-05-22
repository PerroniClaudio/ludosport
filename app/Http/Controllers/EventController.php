<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $approved = [];
        $pending = [];

        foreach ($events as $key => $event) {
            if ($event->is_approved) {
                $approved[$key] = $event;
            } else {
                $pending[$key] = $event;
            }
        }


        return view($view, [
            'approved_events' => $approved,
            'pending_events' =>  $pending,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        return view('event.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //

        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'date' => 'required',
            'start' => 'required',
            'end' => 'required',
        ]);

        $coordinates = $this->getCoordinates($request->address);
        $start_time = $request->date . ' ' . $request->start;
        $end_time = $request->date . ' ' . $request->end;

        Event::create([
            'name' => $request->name,
            'start_date' => $start_time,
            'end_date' => $end_time,
            'description' => $request->name,
            'user_id' => auth()->user()->id,
            'location' => json_encode($coordinates),
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('technician.events.index');
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

        $user = auth()->user();

        if ($user->role === 'tecnico') {
            $view = 'event.technician.edit';

            if ($event->user_id !== $user->id) {
                return redirect()->route('technician.events.index');
            }
        } else {
            $view = 'event.edit';
        }

        return view($view, [
            'event' => $event,
        ]);
    }

    public function saveDescription(Request $request, Event $event) {
        $event->description = $request->description;
        $event->save();

        return redirect()->route('technician.events.edit', $event->id);
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

    private function getCoordinates($address) {
        $address = str_replace(" ", "+", $address);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=" . env('MAPS_GOOGLE_MAPS_ACCESS_TOKEN');
        $response = file_get_contents($url);
        $json = json_decode($response, true);

        return [
            'lat' => $json['results'][0]['geometry']['location']['lat'],
            'lng' => $json['results'][0]['geometry']['location']['lng'],
        ];
    }
}

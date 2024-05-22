<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Nation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        $event = Event::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => '',
            'user_id' => auth()->user()->id,
            'location' => '',
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

        if ($event->thumbnail) {
            $event->thumbnail = Storage::disk('gcs')->temporaryUrl(
                $event->thumbnail,
                now()->addMinutes(5)
            );
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

    public function saveLocation(Request $request, Event $event) {
        $event->location = $request->location;
        $event->city = $request->city;
        $event->address = $request->address;
        $event->postal_code = $request->postal_code;

        $nation = Nation::where('name', $request->nation)->first();

        if ($nation) {
            $event->nation_id = $nation->id;
        }

        $event->save();

        return redirect()->route('technician.events.edit', $event->id)->with('success', 'Location saved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event) {
        //

        $request->validate([
            'name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        $event->name = $request->name;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;

        $event->save();

        return redirect()->route('technician.events.edit', $event->id)->with('success', 'Event saved successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event) {
        //
    }

    public function getLocationData(Request $request) {

        $coordinates = json_decode($request->location, true);

        if (!isset($coordinates['lat'])) {
            $coordinates = [
                'lat' => '45.46404266357422',
                'lng' => '9.1893892288208',
            ];
        }
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$coordinates['lat']},{$coordinates['lng']}&key=" . env('MAPS_GOOGLE_MAPS_ACCESS_TOKEN');

        $response = file_get_contents($url);
        $json = json_decode($response, true);

        return response()->json($json['results'][0]);
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

    public function coordinates(Request $request) {
        $coordinates = $this->getCoordinates($request->address);

        return response()->json($coordinates);
    }

    public function updateThumbnail($id, Request $request) {
        //
        if ($request->file('thumbnail') != null) {
            $file = $request->file('thumbnail');
            $file_name = time() . '_' . $file->getClientOriginalName();
            $path = "events/" . $id . "/" . $file_name;

            $storeFile = $file->storeAs("events/" . $id . "/", $file_name, "gcs");

            if ($storeFile) {
                $event = Event::find($id);
                $event->thumbnail = $path;
                $event->save();

                return redirect()->route('technician.events.edit', $event->id)->with('success', 'Thumbnail uploaded successfully!');
            } else {
                return redirect()->route('technician.events.edit', $id)->with('error', 'Error uploading thumbnail!');
            }
        } else {
            return redirect()->route('technician.events.edit', $id)->with('error', 'Error uploading thumbnail!');
        }
    }
}

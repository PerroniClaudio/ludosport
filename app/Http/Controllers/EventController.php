<?php

namespace App\Http\Controllers;

use App\Exports\EventParticipantsExport;
use App\Models\Academy;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Nation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class EventController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $user = auth()->user();

        if ($user->getRole() === 'technician') {
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

        $user = auth()->user();

        if ($user->getRole() === 'admin') {
            $academies = Academy::all();
        } else {
            $academies = $user->academies()->get();
        }


        return view('event.create', [
            'academies' => $academies
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //

        $user = auth()->user();

        $request->validate([
            'name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        $slug = Str::slug($request->name);

        $slugExists = Event::where('slug', $slug)->exists();
        if ($slugExists) {
            $counter = 1;
            while ($slugExists) {
                $newSlug = $slug . '-' . $counter;
                $slugExists = Event::where('slug', $newSlug)->exists();
                $counter++;
            }
            $slug = $newSlug;
        }

        $event = Event::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => '',
            'user_id' => auth()->user()->id,
            'location' => '',
            'slug' => $slug,
            'is_approved' => 0,
            'is_published' => 0,
            'academy_id' => $request->academy_id,
            'event_type' => EventType::first()->id,
        ]);

        if ($user->getRole() === 'technician') {
            return redirect()->route('technician.events.edit', $event->id);
        } else {
            return redirect()->route('events.edit', $event->id);
        }
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



        if ($user->getRole() === 'technician') {
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

        $results = $event->results()->with('user')->orderBy('war_points', 'desc')->get();

        foreach ($results as $key => $result) {

            $results[$key]['user_fullname'] = $result->user['name'] . ' ' . $result->user['surname'];
        }

        return view($view, [
            'event' => $event,
            'results' => $results
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

        $user = auth()->user();

        if ($user->role === 'tecnico') {
            return redirect()->route('technician.events.edit', $event->id)->with('success', 'Location saved successfully');
        }

        return redirect()->route('events.edit', $event->id)->with('success', 'Location saved successfully');
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
            'price' => 'min:0',

        ]);

        $event->name = $request->name;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;

        if (Auth::user()->getRole() === 'admin') {

            $event_type = EventType::where('name', $request->event_type)->first();

            $event->event_type = $event_type->id;


            if ($request->is_free == 'on') {
                $event->is_free = true;
                $event->price = 0;
            } else {
                $event->is_free = false;
                $event->price = $request->price;
            }
        }

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

        if ((!isset($coordinates['lat'])) || ($coordinates['lat'] == 0)) {
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

    public function calendar(Request $request) {

        header('Content-Type: application/json');

        $events = Event::where('start_date', '>=', $request->start)
            ->where('end_date', '<=', $request->end)
            ->with('user')
            ->get();

        $events_data = [];

        foreach ($events as $event) {

            $classname = 'bg-primary-500';

            if ($event->is_approved) {
                $classname = $event->is_published ? 'bg-primary-500' : 'bg-primary-700';
            } else {
                $classname = 'bg-background-500 dark:bg-background-700';
            }

            $events_data[] = [
                'id' => $event->id,
                'title' => $event->name,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'url' => "/events/{$event->id}",
                'className' => $classname,
                'is_approved' => $event->is_approved,
                'is_published' => $event->is_published,
                'user' => $event->user->name . " " . $event->user->surname,
                'academy' => $event->academy->name,
            ];
        }

        return response()->json($events_data);
    }

    public function review(Event $event) {

        if ($event->is_approved) {
            return redirect()->route('events.edit', $event->id)->with('error', 'This event has already been approved!');
        }

        return view('event.review', [
            'event' => $event,
        ]);
    }

    public function approve(Event $event) {
        $event->is_approved = true;
        $event->save();

        return redirect()->route('events.edit', $event->id)->with('success', 'Event approved successfully!');
    }

    public function publish(Event $event) {
        $event->is_published = true;
        $event->save();

        return redirect()->route('events.edit', $event->id)->with('success', 'Event published successfully!');
    }

    public function available(Event $event) {
        $users = $event->academy->athletes()->get();
        return response()->json($users);
    }

    public function participants(Event $event) {
        $participants = $event->results()->with('user')->get();
        $users = [];

        foreach ($participants as $key => $participant) {
            $users[] = $participant->user;
        }

        return response()->json($users);
    }

    public function selectParticipants(Request $request) {

        $event = Event::find($request->event_id);

        $participants = json_decode($request->participants);

        $event->results()->delete();

        foreach ($participants as $participant) {
            $event->results()->create([
                'user_id' => $participant,
                'war_points' => 0,
                'style_points' => 0,
            ]);
        }

        return response()->json(['success' => 'Participants added successfully!']);
    }

    public function exportParticipants(Event $event) {
        $name = "event_" . $event->name . '_participants.xlsx';

        return Excel::download(new EventParticipantsExport($event->id), $name);
    }

    public function all() {
        $events = Event::where('is_approved', 1)->get();

        $formatted_events = [];

        foreach ($events as $event) {
            $formatted_events[] = [
                'id' => $event->id,
                'name' => $event->name,
                'start_date' => $event->start_date
            ];
        }

        return response()->json($events);
    }

    public function search(Request $request) {

        $events = Event::query()->when($request->search, function ($q, $search) {
            return $q->where('id', Event::search($search)->keys());
        })->get();

        $formatted_events = [];

        foreach ($events as $event) {
            $formatted_events[] = [
                'id' => $event->id,
                'name' => $event->name,
                'start_date' => $event->start_date
            ];
        }

        return response()->json($formatted_events);
    }
}

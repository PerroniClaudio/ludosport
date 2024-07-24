<?php

namespace App\Http\Controllers;

use App\Exports\EventParticipantsExport;
use App\Mail\EventRejectionMail;
use App\Models\Academy;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Nation;
use App\Models\User;
use App\Models\WeaponForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class EventController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        switch ($authRole) {
            case 'dean':
            case 'manager':
                $events = Event::where('school_id', $authUser->schools->first())->get();
                break;
            case 'technician':
                $events = Event::where('user_id', $authUser->id)->get();
                break;
            default:
                $events = Event::all();
                break;
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
        $viewPath = $authRole === 'admin' ? 'event.index' : 'event.' . $authRole . '.index';
        return view($viewPath, [
            'approved_events' => $approved,
            'pending_events' =>  $pending,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if ($authRole === 'admin') {
            $academies = Academy::all();
        } else {
            $academies = $authUser->academies()->get();
        }

        $viewPath = $authRole === 'admin' ? 'event.create' : 'event.' . $authRole . '.create';
        return view($viewPath, [
            'academies' => $academies
        ]);
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

        $authRole = User::find(auth()->user()->id)->getRole();
        $redirectRoute = $authRole === 'admin' ? 'events.edit' : $authRole . '.events.edit';
        
        return redirect()->route($redirectRoute, $event->id);
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

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if ($authRole !== 'admin' && $event->user_id !== $authUser->id) {
            return redirect()->route($authRole . '.events.index')->with('error', 'You are not authorized to edit this event');
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

        if ($authRole === 'technician') {
            $weaponForms = $authUser->weaponForms()->get();
        } else {
            $weaponForms = WeaponForm::all();
        }

        $viewPath = $authRole === 'admin' ? 'event.edit' : 'event.' . $authRole . '.edit';
        return view($viewPath, [
            'event' => $event,
            'results' => $results,
            'weaponForms' => $weaponForms,
        ]);
    }

    public function saveDescription(Request $request, Event $event) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if ($authRole !== 'admin' && $event->user_id !== $authUser->id) {
            return redirect()->route($authRole . '.events.index')->with('error', 'You are not authorized to edit this event');
        }

        $event->description = $request->description;
        $event->save();

        $redirectRoute = $authRole === 'admin' ? 'events.edit' : $authRole . '.events.edit';
        return redirect()->route($redirectRoute, $event->id);
    }

    public function saveLocation(Request $request, Event $event) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        if ($authRole !== 'admin' && $event->user_id !== $authUser->id) {
            return redirect()->route($authRole . '.events.index')->with('error', 'You are not authorized to edit this event');
        }
        $event->location = $request->location;
        $event->city = $request->city;
        $event->address = $request->address;
        $event->postal_code = $request->postal_code;

        $nation = Nation::where('name', $request->nation)->first();

        if ($nation) {
            $event->nation_id = $nation->id;
        }

        $event->save();

        
        $redirectRoute = $authRole === 'admin' ? 'events.edit' : $authRole . '.events.edit';

        return redirect()->route($redirectRoute, $event->id)->with('success', 'Location saved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event) {
        //
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        $auth = auth()->user();
        $user = User::find($auth->id);

        $request->validate([
            'name' => 'required',
            'event_type' => 'numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'price' => 'min:0',
        ]);
        
        if ($authRole !== 'admin' && $event->user_id !== $authUser->id) {
            return redirect()->route($authRole . '.events.index')->with('error', 'You are not authorized to edit this event');
        }

        $event->name = $request->name;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;

        if ($authRole === 'admin') {

            $event_type = EventType::where('id', $request->event_type)->first();



            $event->event_type = $event_type->id;




            if ($request->is_free == 'on') {
                $event->is_free = true;
                $event->price = 0;
            } else {
                $event->is_free = false;
                $event->price = $request->price;
            }
        }

        if (isset($request->weapon_form_id)) {
            $event->weapon_form_id = $request->weapon_form_id;
        }

        $event->save();

        $redirectRoute = $authRole === 'admin' ? 'events.edit' : $authRole . '.events.edit';
        return redirect()->route($redirectRoute, $event->id)->with('success', 'Event saved successfully');
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
        $authRole = User::find(auth()->user()->id)->getRole();
        $redirectRoute = $authRole === 'admin' ? 'events.edit' : $authRole . '.events.edit';
        if ($request->file('thumbnail') != null) {
            $file = $request->file('thumbnail');
            $file_name = time() . '_' . $file->getClientOriginalName();
            $path = "events/" . $id . "/" . $file_name;

            $storeFile = $file->storeAs("events/" . $id . "/", $file_name, "gcs");

            if ($storeFile) {
                $event = Event::find($id);
                $event->thumbnail = $path;
                $event->save();

                return redirect()->route($redirectRoute, $event->id)->with('success', 'Thumbnail uploaded successfully!');
            } else {
                return redirect()->route($redirectRoute, $id)->with('error', 'Error uploading thumbnail!');
            }
        } else {
            return redirect()->route($redirectRoute, $id)->with('error', 'Error uploading thumbnail!');
        }
    }

    public function calendar(Request $request) {

        header('Content-Type: application/json');

        $authRole = User::find(auth()->user()->id)->getRole();

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

            $eventUrl = $authRole === 'admin' ? "/events/{$event->id}" : "/{$authRole}/events/{$event->id}";
            $events_data[] = [
                'id' => $event->id,
                'title' => $event->name,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'url' => $eventUrl,
                'className' => $classname,
                'is_approved' => $event->is_approved,
                'is_published' => $event->is_published,
                'user' => $event->user->name . " " . $event->user->surname,
                'academy' => $event->academy ? $event->academy->name : '',
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

    public function reject(Event $event) {

        $announcement = Announcement::create([
            'object' => 'Event Rejected',
            'content' => 'The event ' . $event->name . ' has been rejected. Reason: ' . request()->reason,
            'user_id' => $event->user_id,
            'type' => 4,
        ]);

        Mail::to($event->user->email)->send(new EventRejectionMail(request()->reason));
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event rejected successfully!');
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

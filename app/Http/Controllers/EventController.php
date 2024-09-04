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
use App\Models\Order;
use App\Models\WeaponForm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Laravel\Cashier\Cashier;
use Maatwebsite\Excel\Facades\Excel;
use Srmklive\PayPal\Services\Paypal as PaypalClient;

class EventController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        switch ($authRole) {
            case 'rector':
            case 'dean':
            case 'manager':
                $events = Event::where('academy_id', $authUser->academies->first())->get();
                break;
            case 'technician':
                $events = Event::whereHas('personnel', function ($query) use ($authUser) {
                    $query->where('user_id', $authUser->id);
                })->get();
                break;
            default:
                $events = Event::all();
                break;
        }

        $approved = [];
        $pending = [];

        foreach ($events as $key => $event) {
            if ($event->is_approved) {
                $approved[] = $event;
            } else {
                $pending[] = $event;
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
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event) {
        //

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        // Questa parte si può spostare in una funzione tipo checkPermission
        // Può modificarlo solo l'admin, il rettore dell'accademia a cui è collegato. dean e manager interni all'accademia e tecnici possono aggiungere partecipanti. 
        if (!($authRole === 'admin' ||
            ($authRole === 'rector' && $event->academy_id === $authUser->academies->first()->id) ||
            (in_array($authRole, ['dean', 'manager']) && $event->academy_id === $authUser->academies->first()->id) ||
            ($event->personnel()->where('user_id', $authUser->id)->exists()))) {
            return redirect()->route($authRole . '.events.index')->with('error', 'You are not authorized to edit this event');
        }
        if ($event->is_approved && !in_array($authRole, ['admin', 'rector', 'dean', 'manager', 'technician'])) {
            return redirect()->route($authRole . '.events.index')->with('error', 'You are not authorized to edit this event');
        }

        if ($event->thumbnail) {
            $event->thumbnail = Storage::disk('gcs')->temporaryUrl(
                $event->thumbnail,
                now()->addMinutes(5)
            );
        }

        $rankingResults = $event->results()->with('user')->orderBy('war_points', 'desc')->get();

        foreach ($rankingResults as $key => $result) {
            $rankingResults[$key]['user_fullname'] = $result->user['name'] . ' ' . $result->user['surname'];
        }

        $enablingResults = $event->instructorResults()->with(['user', 'weaponForm'])->orderBy('stage', 'asc')->get();

        foreach ($enablingResults as $key => $result) {
            $enablingResults[$key]['user_fullname'] = $result->user['name'] . ' ' . $result->user['surname'];
            $enablingResults[$key]['weapon_form_name'] = ($result->weaponForm ? $result->weaponForm['name'] : '');
            $enablingResults[$key]['notes'] = $result->notes ? $result->notes : '';
        }

        if ($authRole === 'technician') {
            $weaponForms = $authUser->weaponForms()->get();
        } else {
            $weaponForms = WeaponForm::all();
        }

        $viewPath = $authRole === 'admin' ? 'event.edit' : 'event.' . $authRole . '.edit';
        return view($viewPath, [
            'event' => $event,
            'rankingResults' => $rankingResults,
            'enablingResults' => $enablingResults,
            'weaponForms' => $weaponForms,
        ]);
    }

    public function saveDescription(Request $request, Event $event) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        // Può modificarlo solo l'admin, il rettore dell'accademia a cui è collegato, l'utente che lo ha creato. 
        if (!($authRole === 'admin' ||
            ($authRole === 'rector' && $event->academy_id === $authUser->academies->first()->id) ||
            $event->user_id === $authUser->id)) {
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

        // Può modificarlo solo l'admin, il rettore dell'accademia a cui è collegato, l'utente che lo ha creato. 
        if (!($authRole === 'admin' ||
            ($authRole === 'rector' && $event->academy_id === $authUser->academies->first()->id) ||
            $event->user_id === $authUser->id)) {
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

        Log::info('Request result_type: ' . $request->result_type);
        $request->validate([
            'name' => 'required',
            'event_type' => 'string',
            'start_date' => 'required',
            'end_date' => 'required',
            'price' => 'min:0',
            'result_type' => 'required|in:ranking,enabling',
        ]);

        // Può modificarlo solo l'admin, il rettore dell'accademia a cui è collegato, l'utente che lo ha creato. 
        if (!($authRole === 'admin' ||
            ($authRole === 'rector' && $event->academy_id === $authUser->academies->first()->id) ||
            $event->user_id === $authUser->id)) {
            return redirect()->route($authRole . '.events.index')->with('error', 'You are not authorized to edit this event');
        }

        // Da quando è approvato può modificarlo solo l'admin
        if ($event->is_approved && $authRole !== 'admin') {
            return redirect()->route($authRole . '.events.index')->with('error', 'You are not authorized to edit this event');
        }

        $event->name = $request->name;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;



        if ($authRole === 'admin') {

            $event_type = EventType::where('name', $request->event_type)->first();
            $event->event_type = $event_type->id;

            if ($request->is_free == 'on') {
                $event->is_free = true;
                $event->price = 0;
            } else {
                $event->is_free = false;
                $event->price = $request->price;
            }

            // Si può modificare il tipo di risultato solo se non ci sono già iscritti in nessuno dei due tipi
            if (!$event->is_approved || $event->results()->count() == 0 || $event->instructorResults()->count() == 0) {
                $event->result_type = $request->result_type;
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

    public function resultTypesList(Request $request) {

        // $resultTypes = [
        //     [
        //         'label' => 'Ranking',
        //         'value' => 'ranking',
        //     ],
        //     [
        //         'label' => 'Enabling',
        //         'value' => 'enabling',
        //     ],
        // ];

        // return response()->json([
        //     'resultTypes' => $resultTypes
        // ]);

        return response()->json([
            'ranking',
            'enabling',
        ]);
    }


    public function calendar(Request $request) {

        header('Content-Type: application/json');

        $authRole = User::find(auth()->user()->id)->getRole();

        switch ($authRole) {
            case 'admin':
                $events = Event::where('start_date', '>=', $request->start)
                    ->where('end_date', '<=', $request->end)
                    ->with('user')
                    ->get();
                break;
            case 'rector':
            case 'dean':
            case 'manager':
                $events = Event::where('academy_id', User::find(auth()->user()->id)->academies->first()->id)
                    ->where('start_date', '>=', $request->start)
                    ->where('end_date', '<=', $request->end)
                    ->with('user')
                    ->get();
                break;
            case 'technician':
                $events = Event::whereHas('personnel', function ($query) {
                    $query->where('user_id', auth()->user()->id);
                })->where('start_date', '>=', $request->start)
                    ->where('end_date', '<=', $request->end)
                    ->with('user')
                    ->get();
                break;
            default:
                $events = Event::where('start_date', '>=', $request->start)
                    ->where('end_date', '<=', $request->end)
                    ->with('user')
                    ->get();
                break;
        }

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
        $users = User::where('is_disabled', '0')->get();
        return response()->json($users);
    }

    public function availablePersonnel(Event $event) {
        $users = User::where('is_disabled', '0')->whereHas('roles', function ($query) {
            $query->where('name', 'technician');
        })->get();
        return response()->json($users);
    }

    public function personnel(Event $event) {
        $users = $event->personnel;
        return response()->json($users);
    }

    public function addPersonnel(Request $request) {
        $authRole = User::find(auth()->user()->id)->getRole();
        if (!in_array($authRole, ['admin', 'rector', 'dean', 'manager'])) {
            return response()->json(['error' => 'You are not authorized to add personnel']);
        }

        $event = Event::find($request->event_id);
        $personnel = json_decode($request->personnel);

        $event->personnel()->whereNotIn('user_id', $personnel)->detach();

        foreach ($personnel as $person) {
            // Aggiunge la persona all'accademia se non è già presente
            if (!$event->academy->personnel()->where('user_id', $person)->exists()) {
                $event->academy->personnel()->attach($person);
            }
            // Aggiunge la persona all'evento se non è già presente
            if ($event->personnel()->where('user_id', $person)->exists()) {
                continue;
            }
            $event->personnel()->attach($person);
        }


        return response()->json(['success' => 'Personnel added successfully!']);
    }

    public function participants(Event $event) {
        if ($event->result_type === 'enabling') {
            $participants = $event->instructorResults()->with('user')->get();
        } else if ($event->result_type === 'ranking') {
            $participants = $event->results()->with('user')->get();
        }

        $users = [];

        foreach ($participants as $key => $participant) {
            $users[] = $participant->user;
        }

        return response()->json($users);
    }

    public function selectParticipants(Request $request) {
        $event = Event::find($request->event_id);

        $participants = json_decode($request->participants, true);

        Log::info('Participants:', ['participants' => $participants]);
        if (!is_array($participants)) {
            Log::error('Invalid participants data', ['participants' => $participants]);
            return response()->json(['error' => 'Invalid participants data'], 400);
        }

        if ($event->result_type === 'enabling') {
            // Elimina i partecipanti che non sono più presenti (possibile rischio di perdita di dati, cioè elimiinazione di eventuali risultati già presenti)
            $event->instructorResults()->whereNotIn('user_id', $participants)->whereNotIn('stage', ['confirmed'])->delete();

            foreach ($participants as $participant) {
                if ($event->instructorResults()->where('user_id', $participant)->exists()) {
                    continue;
                }
                $event->instructorResults()->create([
                    'user_id' => $participant,
                    'weapon_form_id' => $event->weaponForm ? $event->weaponForm->id : null,
                ]);
            }
        } else if ($event->result_type === 'ranking') {
            // Elimina i partecipanti che non sono più presenti (possibile rischio di perdita di dati, cioè elimiinazione di eventuali risultati già presenti)
            $event->results()->whereNotIn('user_id', $participants)->delete();

            foreach ($participants as $participant) {
                if ($event->results()->where('user_id', $participant)->exists()) {
                    continue;
                }

                $event->results()->create([
                    'user_id' => $participant,
                    'war_points' => 0,
                    'style_points' => 0,
                ]);
            }
        }

        return response()->json(['success' => 'Participants modified successfully!']);
    }

    public function confirmEventInstructorResult(Event $event, Request $request) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();
        if ($authRole !== 'admin') {
            return response()->json(['error' => 'You are not authorized to confirm results']);
        }
        $request->validate([
            'result_id' => 'required|exists:event_instructor_results,id',
            'result' => 'string|in:passed,failed',
        ]);

        $result = $event->instructorResults()->find($request->result_id);
        if (!$result) {
            return response()->json(['error' => 'Result not related to this event']);
        }

        $result->result = $request->result;
        $result->stage = 'confirmed';

        if ($result->weaponForm && $result->result === 'passed') {
            // Aggiunge la forma da atleta all'utente se non ce l'ha già (deve aggiungere anche il ruolo?)
            if (!$result->weaponForm->users()->where('user_id', $result->user->id)->exists()) {
                $result->weaponForm->users()->attach($result->user->id);
            }
            // Aggiunge la forma da istruttore all'utente se non ce l'ha già
            if (!$result->weaponForm->personnel()->where('user_id', $result->user->id)->exists()) {
                $result->weaponForm->personnel()->attach($result->user->id, [
                    'event_id' => $event->id,
                    'admin_id' => $authUser->id,
                ]);
            }
        }

        $result->save();

        return response()->json([
            'success' => 'Result confirmed successfully!',
            'result' => $result
        ]);
    }

    public function exportParticipants(Event $event) {
        $name = "event_" . $event->name . '-' . $event->result_type . '_participants.xlsx';

        return Excel::download(new EventParticipantsExport($event->id, $event->result_type), $name);
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

    // Sito web 

    public function eventsList() {
        $date = Carbon::parse(now());

        $events = Event::where([
            ['is_approved', '=', 1],
            ['is_published', '=', 1],
            ['end_date', '>=', $date->format('Y-m-d')],
        ])->get();

        foreach ($events as $key => $value) {
            $events[$key]['full_address'] = $value['address'] . ", " .  $value['postal_code'] . ", " .  $value['city'] . ", " .  $value['nation']['name'];
        }


        return view('website.events-list', [
            'events' => $events
        ]);
    }

    public function list(Request $request) {

        $date = Carbon::parse($request->date);

        $events = Event::where([
            ['is_approved', '=', 1],
            ['is_published', '=', 1],
            ['end_date', '<=', $date->format('Y-m-d')],
        ])->get();


        return response()->json($events);
    }

    public function general(Request $request) {

        $date = Carbon::parse($request->date);
        $events = Event::where('end_date', '<', $date->format('Y-m-d'))->get();

        $results = [];

        foreach ($events as $event) {
            $event_result = $event->results()->with('user')->orderBy('war_points', 'desc')->get();

            foreach ($event_result as $key => $value) {

                if (!isset($results[$value->user_id])) {
                    $results[$value->user_id] = [
                        'user_id' => $value->user_id,
                        'user_name' => $value->user->name . ' ' . $value->user->surname,
                        'total_war_points' => 0,
                        'total_style_points' => 0,
                    ];
                }

                $results[$value->user_id]['total_war_points'] += $value->total_war_points;
                $results[$value->user_id]['total_style_points'] += $value->total_style_points;
            }
        }

        usort($results, function ($a, $b) {
            return $b['total_war_points'] - $a['total_war_points'];
        });

        return response()->json($results);
    }

    public function nation(Request $request) {

        $date = Carbon::parse($request->date);
        $events = Event::where('end_date', '<', $date->format('Y-m-d'))->get();

        $results = [];

        foreach ($events as $event) {

            $event_result = $event->results()->with('user')->orderBy('war_points', 'desc')->get();

            foreach ($event_result as $key => $value) {

                if ($value->user->nation_id == $request['nation_id']) {

                    if (!isset($results[$value->user_id])) {
                        $results[$value->user_id] = [
                            'user_id' => $value->user_id,
                            'user_name' => $value->user->name . ' ' . $value->user->surname,
                            'total_war_points' => 0,
                            'total_style_points' => 0,
                        ];
                    }

                    $results[$value->user_id]['total_war_points'] += $value->total_war_points;
                    $results[$value->user_id]['total_style_points'] += $value->total_style_points;
                }
            }
        }

        usort($results, function ($a, $b) {
            return $b['total_war_points'] - $a['total_war_points'];
        });

        return response()->json([
            'results' => $results,
            'nation' => Nation::find($request['nation_id']),
        ]);
    }

    public function eventResult(Event $event) {

        $results = [];

        $event_results = $event->results()->with('user')->orderBy('war_points', 'desc')->get();

        foreach ($event_results as $key => $value) {
            if (!isset($results[$value->user_id])) {
                $results[$value->user_id] = [
                    'user_id' => $value->user_id,
                    'user_name' => $value->user->name . ' ' . $value->user->surname,
                    'total_war_points' => 0,
                    'total_style_points' => 0,
                ];
            }

            $results[$value->user_id]['total_war_points'] += $value->total_war_points;
            $results[$value->user_id]['total_style_points'] += $value->total_style_points;
        }

        usort($results, function ($a, $b) {
            return $b['total_war_points'] - $a['total_war_points'];
        });

        return response()->json($results);
    }

    /**
     * Display the specified resource.
     */

    public function show(Event $event) {
        //

        $canpurchase = false;

        $user = User::find(auth()->user()->id);

        if ($user->has_paid_fee) {

            if ($event->results->count() < 64) {

                $founduser = false;

                foreach ($event->results as $result) {
                    if ($result->user_id === auth()->user()->id) {
                        $founduser = true;
                        break;
                    }
                }

                if (!$founduser) {
                    $canpurchase = true;
                }
            }
        }

        return view('website.event-detail', [
            'event' => $event,
            'canpurchase' => $canpurchase
        ]);
    }

    public function purchase(Event $event) {

        $user = User::find(Auth()->user()->id);

        if ($user->has_paid_fee === 0) {
            return redirect()->route('event-detail', $event->slug)->with('error', __('website.must_pay_fee'));
        }

        $invoice = $user->invoices->first();

        if (!$invoice) {
            $invoice = $user->invoices()->create([
                'user_id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname ? $user->surname : '',
                'address' => json_encode([
                    'address' => '',
                    'zip' => '',
                    'city' => '',
                    'country' => 'Italy',
                ]),
                'vat' => '',
            ]);
        }

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 0,
            'total' => $event->price,
            'payment_method' => '',
            'order_number' => Str::orderedUuid(),
            'result' => '{}',
            'invoice_id' => $invoice->id,
        ]);

        $order->items()->create([
            'product_type' => 'event_participation',
            'product_name' => $event->name,
            'product_code' => $event->id,
            'quantity' => 1,
            'price' => $event->price,
            'vat' => 0,
            'total' => $event->price
        ]);

        session(['order_id' => $order->id]);

        return view('website.shop.event-purchase', [
            'order' => $order,
            'invoice' => $invoice,
            'event' => $event,
        ]);
    }

    public function userCheckoutStripe(Event $event, Request $request) {
        $user = User::find(Auth()->user()->id);

        $order_id = $request->session()->get('order_id');
        $order = Order::findOrFail($order_id);


        $order->update([
            'payment_method' => 'stripe',
        ]);

        return $request->user()->checkoutCharge(($event->price * 100), $event->name, 1, [
            'success_url' => route('shop.event.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('shop.event.cancel')  . '?session_id={CHECKOUT_SESSION_ID}',
            'metadata' => ['order_id' => $order->id],
        ]);
    }

    public function userCheckoutPaypal(Event $event, Request $request) {

        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $order_id = $request->session()->get('order_id');
        $order = Order::findOrFail($order_id);

        $response = $provider->createOrder([
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'EUR',
                        'value' => $event->price,
                    ],
                ],
            ],
            'application_context' => [
                'cancel_url' => route('shop.event.paypal-cancel') . '?order_id=' . $order->id,
                'return_url' => route('shop.event.paypal-success') . '?order_id=' . $order->id,
                'order_id' => $order->id,
            ],
        ]);

        if (isset($response['id']) && $response['id'] !== null) {
            session(['paypal_order_id' => $response['id']]);

            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $order->update([
                        'status' => 1,
                        'payment_method' => 'paypal',
                        'total' => number_format($event->price, 2),
                        'result' => json_encode($response),
                    ]);

                    session()->put('product_name', $event->name);

                    return response()->json([
                        'success' => true,
                        'url' => $link['href'],
                    ]);
                }
            }
        } else {
            $order->update([
                'status' => 4,
                'payment_method' => 'paypal',
                'total' => number_format($event->price, 2),
                'result' => 'Error creating order',
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error creating order',
                'url' => route('shop.event.paypal-cancel') . '?order_id=' . $order->id,
            ]);
        }
    }

    public function successUser(Request $request) {

        $sessionId = $request->get('session_id');

        if ($sessionId === null) {
            return;
        }

        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            return;
        }

        $orderId = $session['metadata']['order_id'] ?? null;
        $order = Order::findOrFail($orderId);

        if ($order->status !== 0) {
        } else {

            $order->update([
                'status' => 2,
                'result' => json_encode($session),
            ]);

            $event = Event::find($order->items->first()->product_code);

            $event->results()->create([
                'user_id' => $order->user_id,
                'war_points' => 0,
                'style_points' => 0,
                'total_points' => 0,
            ]);

            return view('website.shop.event-success', [
                'event' => $event,
            ]);
        }
    }

    public function cancelUser(Request $request) {
        $sessionId = $request->get('session_id');

        if ($sessionId === null) {
            return;
        }

        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

        $orderId = $session['metadata']['order_id'] ?? null;
        $order = Order::findOrFail($orderId);

        if ($order->status !== 0) {
        } else {
            $order->update(['status' => 4, 'result' => json_encode($session)]);
        }

        return view('website.shop.event-cancel');
    }

    public function successUserPaypal(Request $request) {
        $orderId = $request->order_id;

        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $result = $provider->capturePaymentOrder($request->token);
        $order = Order::findOrFail($orderId);

        if ($order->status !== 1) {
            $event = Event::find($order->items->first()->product_code);
        } else {
            $order->update([
                'status' => 2,
                'result' => json_encode($result),
            ]);

            $event = Event::find($order->items->first()->product_code);

            $event->results()->create([
                'user_id' => $order->user_id,
                'war_points' => 0,
                'style_points' => 0,
                'total_points' => 0,
            ]);
        }

        return view('website.shop.event-success', [
            'event' => $event,
        ]);
    }

    public function cancelUserPaypal(Request $request) {
        $orderId = $request->order_id;

        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $result = $provider->capturePaymentOrder($request->token);
        $order = Order::findOrFail($orderId);

        if ($order->status !== 1) {
        } else {
            $order->update([
                'status' => 4,
                'result' => json_encode($result),
            ]);
        }

        return view('website.shop.event-cancel');
    }

    public function rankings() {

        $countries = Nation::all();
        $continents = [];

        foreach ($countries as $key => $country) {

            $continent = $country['continent'];

            if (!isset($continents[$continent])) {
                $continents[$continent] = [];
            }

            $continents[$continent][] = [
                'value' => $country->id,
                'label' => $country->name,
            ];
        }

        foreach ($continents as $key => $value) {
            $options = [];

            foreach ($value as $country) {
                $options[] = [
                    "value" => $country['value'],
                    "label" => $country['label']
                ];
            }

            $continents[$key] = [
                "label" => $key,
                "options" => $options
            ];
        }


        $europe = $continents['Europe'];
        unset($continents['Europe']);
        $continents = ['Europe' => $europe] + $continents;


        return view('website.rankings', [
            'continents' => $continents,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Events\EventPaid;
use App\Events\ParticipantsUpdated;
use App\Exports\EventParticipantsExport;
use App\Mail\EventRejectionMail;
use App\Models\Academy;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventType;
use App\Models\EventWaitingList;
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
use Srmklive\PayPal\Services\PayPal as PaypalClient;
use Stripe\PaymentIntent;
use Stripe\Stripe;

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
                $events = Event::where('academy_id', $authUser->primaryAcademy())->get();
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

        $primaryAcademy = $authUser->primaryAcademy();
        // Questa parte si può spostare in una funzione tipo checkPermission
        // Può modificarlo solo l'admin, il rettore dell'accademia a cui è collegato. dean e manager interni all'accademia e tecnici possono aggiungere partecipanti. 
        if (!($authRole === 'admin' ||
            (in_array($authRole, ['rector', 'dean', 'manager']) && isset($event->academy_id) && ($event->academy_id === ($primaryAcademy ? $primaryAcademy->id : null))) ||
            ($event->personnel()->where('user_id', $authUser->id)->exists()))) {
            return redirect()->route($authRole . '.events.index')->with('error', 'You are not authorized to edit this event');
        }
        if ($event->is_approved && !in_array($authRole, ['admin', 'rector', 'dean', 'manager', 'technician'])) {
            return redirect()->route($authRole . '.events.index')->with('error', 'You are not authorized to edit this event');
        }

        if ($event->thumbnail) {
            /** 
             * @disregard Intelephense non rileva il metodo temporaryurl
             * 
             * @see https://github.com/spatie/laravel-google-cloud-storage
             */
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

        $waitingList = [];
        if ($authRole === 'admin') {
            $waitingList = EventWaitingList::where('event_id', $event->id)->with('order')->with('user')->get();

            foreach ($waitingList as $key => $waiting) {
                $waitingList[$key]['user_id'] = $waiting->user['id'];
                $waitingList[$key]['user_email'] = $waiting->user['email'];
                $waitingList[$key]['order_id'] = $waiting->order ? $waiting->order->id : '';
                $waitingList[$key]['payment_method'] = $waiting->order ? $waiting->order->payment_method : '';
                $waitingList[$key]['order_status'] = $waiting->order ? __('orders.status' . $waiting->order->status) : '';
            }
        }

        $viewPath = $authRole === 'admin' ? 'event.edit' : 'event.' . $authRole . '.edit';
        return view($viewPath, [
            'event' => $event,
            'rankingResults' => $rankingResults,
            'enablingResults' => $enablingResults,
            'weaponForms' => $weaponForms,
            'waitingList' => $waitingList,
        ]);
    }

    public function saveDescription(Request $request, Event $event) {
        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

        // Può modificarlo solo l'admin, il rettore dell'accademia a cui è collegato, l'utente che lo ha creato. 
        if (!($authRole === 'admin' ||
            ($authRole === 'rector' && isset($event->academy_id) && ($event->academy_id === ($authUser->primaryAcademy() ? $authUser->primaryAcademy()->id : null))) ||
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
            ($authRole === 'rector' && isset($event->academy_id) && ($event->academy_id === ($authUser->primaryAcademy() ? $authUser->primaryAcademy()->id : null))) ||
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

        if (!$event->is_approved){
            $request->validate([
                'name' => 'required',
                'event_type' => 'string',
                'start_date' => 'required',
                'end_date' => 'required',
                'price' => 'min:0',
            ]);
        }

        // Può modificarlo solo l'admin, il rettore dell'accademia a cui è collegato, l'utente che lo ha creato. 
        if (!($authRole === 'admin' ||
            ($authRole === 'rector' && isset($event->academy_id) && ($event->academy_id === ($authUser->primaryAcademy() ? $authUser->primaryAcademy()->id : null))) ||
            $event->user_id === $authUser->id)) {
                return back()->with('error', 'You are not authorized to edit this event');
        }

        // Da quando è approvato non si può modificare. Ad eccezione di block_subscriptions, modificabile solo dall'admin
        if ($event->is_approved) {
            if ($authRole !== 'admin') {
                return back()->with('error', 'You are not authorized to edit this event');
            }
            $newValue = $request->block_subscriptions == 'on' ? true : false;
            if ($event->block_subscriptions != $newValue) {
                $event->block_subscriptions = $newValue;
                $event->save();
                return back()->with('success', 'Block subscriptions saved successfully');
            }
            
            return back()->with('error', 'After approval, you can only modify "block subscriptions" value');
        }

        $event->name = $request->name;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;

        if ($authRole === 'admin') {

            $event->max_participants = $request->max_participants ?? null;

            $event_type = EventType::where('name', $request->event_type)->first();
            $event->event_type = $event_type->id;

            if ($request->is_free == 'on') {
                $event->is_free = true;
                $event->price = 0;
            } else {
                $event->is_free = false;
                $event->price = $request->price;
            }

            $event->block_subscriptions = $request->block_subscriptions == 'on' ? true : false;
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

        $authUser = User::find(auth()->user()->id);
        $authRole = $authUser->getRole();

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
                $primaryAcademy = $authUser->primaryAcademy();
                $events = Event::where('academy_id', ($primaryAcademy ? $primaryAcademy->id : null))
                    ->where('start_date', '>=', $request->start)
                    ->where('end_date', '<=', $request->end)
                    ->with('user')
                    ->get();
                break;
            case 'technician':
                $events = Event::whereHas('personnel', function ($query) use ($authUser) {
                    $query->where('user_id', $authUser->id);
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
                $event->academy->personnel()->syncWithoutDetaching($person);
            }
            // Aggiunge la persona all'evento se non è già presente
            if ($event->personnel()->where('user_id', $person)->exists()) {
                continue;
            }
            $event->personnel()->syncWithoutDetaching($person);
        }


        return response()->json(['success' => 'Personnel added successfully!']);
    }

    public function participants(Event $event) {
        if ($event->resultType() === 'enabling') {
            $participants = $event->instructorResults()->with('user')->get();
        } else if ($event->resultType() === 'ranking') {
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

        if (!is_array($participants)) {
            return response()->json(['error' => 'Invalid participants data'], 400);
        }

        if ($event->resultType() === 'enabling') {
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
        } else if ($event->resultType() === 'ranking') {
            // Elimina i partecipanti che non sono più presenti, solo se non hanno risultati > 0 (war_points, style_points)
            $event->results()->whereNotIn('user_id', $participants)
                ->where('war_points', '=', "0")->where('style_points', '=', "0")->delete();

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

        // Esegui l'evento per aggiornare partecipanti e waiting list (se ci sono posti liberi)
        event(new ParticipantsUpdated($event->id));

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
            // Aggiunge la forma da atleta all'utente se non ce l'ha già. NON DEVE AGGIUNGERE IL RUOLO. 
            if (!$result->weaponForm->users()->where('user_id', $result->user->id)->exists()) {
                $result->weaponForm->users()->syncWithoutDetaching($result->user->id);
            }
            // Aggiunge la forma da istruttore all'utente se non ce l'ha già
            if (!$result->weaponForm->personnel()->where('user_id', $result->user->id)->exists()) {
                $result->weaponForm->personnel()->syncWithoutDetaching($result->user->id, [
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
        $name = "event_" . $event->name . '-' . $event->resultType() . '_participants.xlsx';

        return Excel::download(new EventParticipantsExport($event->id, $event->resultType()), $name);
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

    public function dashboardEvents() {
        $events = Event::where('is_approved', 1)
            ->whereHas('personnel', function ($query) {
                $query->where('user_id', auth()->user()->id);
            })
            ->where(function ($query) {
                $query->whereYear('start_date', date('Y'))
                    ->orWhereYear('end_date', date('Y'));
            })
            ->with(['type', 'results', 'instructorResults'])
            ->get();

        $formatted_events = [];

        foreach ($events as $event) {
            $formatted_events[] = [
                'id' => $event->id,
                'name' => $event->name,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'type' => $event->type->name,
                'result_type' => $event->resultType(),
                'results' => $event->results ? $event->results()->count() : null,
                'instructor_results' => $event->instructor_results ? $event->instructorResults()->count() : null,
                'participants' => $event->resultType() == 'enabling' ? $event->instructorResults()->count() : $event->results()->count(),
            ];
        }

        usort($formatted_events, function ($a, $b) {
            return $b['participants'] - $a['participants'];
        });

        return response()->json($formatted_events);
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

    public function eventsList(Request $request) {

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

        $date = Carbon::parse(now());

        if (isset($request->nation)) {
            $events = Event::where([
                ['is_approved', '=', 1],
                ['is_published', '=', 1],
                ['end_date', '>=', $date->format('Y-m-d')],
                ['nation_id', '=', $request->nation],
            ])->get();
        } else {
            $events = Event::where([
                ['is_approved', '=', 1],
                ['is_published', '=', 1],
                ['end_date', '>=', $date->format('Y-m-d')],
            ])->get();
        }


        foreach ($events as $key => $value) {
            $events[$key]['full_address'] = $value['address'] . ", " .  $value['postal_code'] . ", " .  $value['city'] . ", " .  $value['nation']['name'];
        }


        return view('website.events-list', [
            'events' => $events,
            'continents' => $continents,
            'nationFilter' => $request->nation,
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

                    $primaryAcademyAthlete = $value->user->primaryAcademyAthlete();

                    $results[$value->user_id] = [
                        'user_id' => $value->user_id,
                        'user_name' => $value->user->name . ' ' . $value->user->surname,
                        'user_battle_name' => $value->user->battle_name,
                        'user_battle_name' => $value->user->battle_name,
                        'user_academy' => $primaryAcademyAthlete ? $primaryAcademyAthlete->name : '',
                        'user_school' => $value->user->primarySchoolAthlete()->name ?? '',
                        'school_slug' => $value->user->primarySchoolAthlete()->slug ?? '',
                        'nation' => $value->user->nation->name,
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

                        $primaryAcademyAthlete = $value->user->primaryAcademyAthlete();

                        $results[$value->user_id] = [
                            'user_id' => $value->user_id,
                            'user_name' => $value->user->name . ' ' . $value->user->surname,
                            'user_academy' => $primaryAcademyAthlete ? $primaryAcademyAthlete->name : '',
                            'user_school' => $value->user->primarySchoolAthlete()->name ?? '',
                            'school_slug' => $value->user->primarySchoolAthlete()->slug ?? '',
                            'nation' => $value->user->nation->name,
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

            $primaryAcademyAthlete = $value->user->primaryAcademyAthlete();

            if (!isset($results[$value->user_id])) {
                $results[$value->user_id] = [
                    'user_id' => $value->user_id,
                    'user_name' => $value->user->name . ' ' . $value->user->surname,
                    'user_battle_name' => $value->user->battle_name,
                    'user_academy' => $primaryAcademyAthlete ? $primaryAcademyAthlete->name : '',
                    'user_school' => $value->user->primarySchoolAthlete()->name ?? '',
                    'school_slug' => $value->user->primarySchoolAthlete()->slug ?? '',
                    'nation' => $value->user->nation->name,
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
        $isParticipating = false;
        $isInWaitingList = false;
        $onlyWaitingList = $event->isWaitingList();

        $user = User::find(auth()->user()->id);

        if ($user->has_paid_fee) {
            // Controlla il tipo di evento
            if ($event->resultType() === 'enabling') {
                $isParticipating = $event->instructorResults()->where('user_id', $user->id)->exists();
                $isInWaitingList = EventWaitingList::where('event_id', $event->id)->where('user_id', $user->id)->exists();
                $canpurchase = !$event->block_subscriptions && !$isParticipating && !$isInWaitingList;
            } else if ($event->resultType() === 'ranking') {
                $isParticipating = $event->results()->where('user_id', $user->id)->exists();
                $isInWaitingList = EventWaitingList::where('event_id', $event->id)->where('user_id', $user->id)->exists();
                $canpurchase = !$event->block_subscriptions && !$isParticipating && !$isInWaitingList;
            }
        }

        return view('website.event-detail', [
            'event' => $event,
            'canpurchase' => $canpurchase,
            'only_waiting_list' => $onlyWaitingList,
            'is_participating' => $isParticipating,
            'is_in_waiting_list' => $isInWaitingList,
            'block_subscriptions' => $event->block_subscriptions,
        ]);
    }

    public function purchase(Event $event) {

        $user = User::find(Auth()->user()->id);

        if ($user->has_paid_fee === 0) {
            return redirect()->route('event-detail', $event->slug)->with('error', __('website.must_pay_fee'));
        }

        // Se ha già un ordine in completato o in attesa (status 1, 2, 3) per questo evento, non può acquistare un altro
        $rejectOrder = Order::where('user_id', $user->id)
            ->whereIn('status', [2, 3])
            ->whereHas('items', function ($query) use ($event) {
                $query->where(['product_type' => 'event_participation', 'product_code' => $event->id]);
            })->first();
        if ($rejectOrder) {
            return redirect()->route('event-detail', $event->slug)->with('error', __('website.events_already_ordered'));
        }

        // Se l'ordine dell'utente esiste già (è entrato in questa pagina ed è uscito senza terminare) allora recupera quello esistente, altrimenti ne crea un altro
        $order = Order::where(['user_id' => $user->id, 'status' => 0])
            ->whereHas('items', function ($query) use ($event) {
                $query->where(['product_type' => 'event_participation', 'product_code' => $event->id]);
            })->first();

        $invoice = null;

        if ($order) {
            $invoice = $order->invoice;
            if (!$invoice) {
                $lastInvoice = $user->invoices()->latest()->first();
                $invoice = $user->invoices()->create([
                    'user_id' => $user->id,
                    'name' => $lastInvoice ? ($lastInvoice->name ?: $user->name) : $user->name,
                    'surname' => $lastInvoice ? ($lastInvoice->surname ?: ($user->surname ?: '')) : ($user->surname ?: ''),
                    'address' => $lastInvoice ? ($lastInvoice->address ?: json_encode([
                        'address' => '',
                        'zip' => '',
                        'city' => '',
                        'country' => 'Italy',
                    ])) : json_encode([
                        'address' => '',
                        'zip' => '',
                        'city' => '',
                        'country' => 'Italy',
                    ]),
                    'vat' => $lastInvoice ? ($lastInvoice->vat ?: '') : '',
                    'sdi' => $lastInvoice ? ($lastInvoice->sdi ?: '') : '',
                ]);
            }
        } else {
            $lastInvoice = $user->invoices()->latest()->first();
            $invoice = $user->invoices()->create([
                'user_id' => $user->id,
                'name' => $lastInvoice ? ($lastInvoice->name ?: $user->name) : $user->name,
                'surname' => $lastInvoice ? ($lastInvoice->surname ?: ($user->surname ?: '')) : ($user->surname ?: ''),
                'address' => $lastInvoice ? ($lastInvoice->address ?: json_encode([
                    'address' => '',
                    'zip' => '',
                    'city' => '',
                    'country' => 'Italy',
                ])) : json_encode([
                    'address' => '',
                    'zip' => '',
                    'city' => '',
                    'country' => 'Italy',
                ]),
                'vat' => $lastInvoice ? ($lastInvoice->vat ?: '') : '',
                'sdi' => $lastInvoice ? ($lastInvoice->sdi ?: '') : '',
            ]);

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
        }

        session(['order_id' => $order->id]);

        return view('website.shop.event-purchase', [
            'order' => $order,
            'invoice' => $invoice,
            'event' => $event,
        ]);
    }

    // STRIPE - Acquisto

    // l'utente ha scelto stripe per pagare l'iscrizione all'evento
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

    // L'utente ha completato l'acquisto con stripe
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

            if ($event->resultType() === 'enabling') {
                $event->instructorResults()->create([
                    'user_id' => $order->user_id,
                    'weapon_form_id' => $event->weapon_form_id,
                ]);
            } else if ($event->resultType() === 'ranking') {
                $event->results()->create([
                    'user_id' => $order->user_id,
                    'war_points' => 0,
                    'style_points' => 0,
                    'total_points' => 0,
                ]);
            }

            event(new EventPaid($order, $event));

            return view('website.shop.event-success', [
                'event' => $event,
            ]);
        }
    }

    // Errore acquisto con stripe
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

    // STRIPE - Preautorizzazione

    // Per la waiting list
    // public function userPreauthorizeStripe(Event $event, Request $request) {
    //     $user = User::find(Auth()->user()->id);

    //     $order_id = $request->session()->get('order_id');
    //     $order = Order::findOrFail($order_id);

    //     $order->update([
    //         'payment_method' => 'stripe',
    //     ]);

    //     // Non sembra esservi un metodo di preautorizzazione, si deve usare quello delle subscriptions probabilmente.
    //     return view('preauthorize-payment', [
    //         $request->user()->createSetupIntent()
    //     ])

    //     return $request->user()->checkoutCharge(($event->price * 100), $event->name, 1, [
    //         'success_url' => route('shop.event.success') . '?session_id={CHECKOUT_SESSION_ID}',
    //         'cancel_url' => route('shop.event.cancel')  . '?session_id={CHECKOUT_SESSION_ID}',
    //         'metadata' => ['order_id' => $order->id],
    //     ]);

    //     $paymentIntent = PaymentIntent::create([
    //         'amount' => $event->price * 100, // L'importo in centesimi
    //         'currency' => 'eur',
    //         'capture_method' => 'manual',
    //         'metadata' => ['order_id' => $order->id],
    //     ], [
    //         'api_key' => env('STRIPE_SECRET'),
    //     ]);

    //     return response()->json([
    //         'client_secret' => $paymentIntent->client_secret,
    //         'success_url' => route('shop.event.stripe-preauth-success') . '?session_id={CHECKOUT_SESSION_ID}',
    //         'cancel_url' => route('shop.event.stripe-preauth-cancel') . '?session_id={CHECKOUT_SESSION_ID}',
    //     ]);

    // }

    // Successo preautorizzazione con stripe
    // public function preauthSuccessUserStripe(Request $request) {
    //     $user = Auth::user();
    //     $order_id = $request->session()->get('order_id');
    //     $order = Order::findOrFail($order_id);

    //     // Recupera l'ID del PaymentIntent dalla richiesta
    //     $paymentIntentId = $request->input('payment_intent_id');

    //     $order->update([
    //         'status' => 3, // Stato 3 = Preauthorized
    //         'stripe_payment_intent_id' => $paymentIntentId, 
    //     ]);

    //     // Salva i dati nella tabella event_waiting_list
    //     EventWaitingList::create([
    //         'user_id' => $user->id,
    //         'event_id' => $order->event_id,
    //         'order_id' => $order->id,
    //     ]);

    //     return response()->json(['success' => true, 'message' => 'Preauthorization successful.']);
    // }

    // Errore preautorizzazione con stripe
    // public function preauthCancelUserStripe(Request $request) {
    //     $sessionId = $request->get('session_id');

    //     if ($sessionId === null) {
    //         return;
    //     }

    //     $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

    //     $orderId = $session['metadata']['order_id'] ?? null;
    //     $order = Order::findOrFail($orderId);

    //     if ($order->status !== 0) {
    //     } else {
    //         $order->update(['status' => 4, 'result' => json_encode($session)]);
    //     }

    //     return view('website.shop.event-cancel');
    // }

    // Finalizzazione acquisto preautorizzato con stripe
    // public function capturePreauthorizedPaymentStripe(Request $request) {
    //     $paymentIntentId = $request->input('payment_intent_id');

    //     try {
    //         $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
    //         $paymentIntent->capture();

    //         // Aggiorna lo stato dell'ordine a "pagato"
    //         $order = Order::where('payment_intent_id', $paymentIntentId)->first();
    //         $order->update(['status' => 'paid']);

    //         return response()->json(['success' => true]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }


    // PAYPAL - Acquisto

    // L'utente ha scelto paypal per pagare l'iscrizione all'evento
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

    // L'utente ha completato l'acquisto con paypal
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

            if ($event->resultType() === 'enabling') {
                $event->instructorResults()->create([
                    'user_id' => $order->user_id,
                    'weapon_form_id' => $event->weapon_form_id,
                ]);
            } else if ($event->resultType() === 'ranking') {
                $event->results()->create([
                    'user_id' => $order->user_id,
                    'war_points' => 0,
                    'style_points' => 0,
                    'total_points' => 0,
                ]);
            }

            event(new EventPaid($order, $event));
        }

        return view('website.shop.event-success', [
            'event' => $event,
        ]);
    }

    // Errore acquisto con paypal
    public function cancelUserPaypal(Request $request) {

        $orderId = $request->order_id;

        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        // Annulla l'ordine PayPal
        $result = $provider->voidAuthorizedPayment($request->token);
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

    // PAYPAL - Preautorizzazione

    // L'utente ha scelto paypal per preautorizzare il pagamento dell'iscrizione all'evento ed entrare in waiting list
    public function userPreauthorizePaypal(Event $event, Request $request) {

        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $order_id = $request->session()->get('order_id');
        $order = Order::findOrFail($order_id);

        $response = $provider->createOrder([
            'intent' => 'AUTHORIZE', // Per indicare che va preautorizzato il pagamento
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'EUR',
                        'value' => $event->price,
                    ],
                ],
            ],
            'application_context' => [
                'cancel_url' => route('shop.event.paypal-preauth-cancel') . '?order_id=' . $order->id, // Link della pagina di errore preautorizzazione pagamento (dopo la pagina di paypal)
                'return_url' => route('shop.event.paypal-preauth-success') . '?order_id=' . $order->id, // Link della pagina di successo preautorizzazione pagamento (dopo la pagina di paypal)
                'order_id' => $order->id,
            ],
        ]);

        Log::info($response);

        if ($response['status'] === 'CREATED' && isset($response['id']) && $response['id'] !== null) {
            session(['paypal_order_id' => $response['id']]);

            $order->update([
                'status' => 1,
                'payment_method' => 'paypal',
                'total' => number_format($event->price, 2),
                'result' => json_encode($response),
                'paypal_order_id' => $response['id'],
            ]);

            session()->put('product_name', $event->name);

            $link = null;
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $link = $link['href'];
                    break;
                }
            }
            return response()->json([
                'success' => true,
                'url' => $link, // Link della pagina di paypal di preautorizzazione pagamento (il frontend deve reindirizzare l'utente a questa pagina)
            ]);
        } else {
            // Gestisci altri stati come ('COMPLETED', 'APPROVED', 'FAILED') - In questo caso solo 'FAILED'
            $order->update([
                'status' => 4,
                'payment_method' => 'paypal',
                'total' => number_format($event->price, 2),
                'result' => 'Error creating order',
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error creating order',
                'url' => route('shop.event.paypal-preauth-cancel') . '?order_id=' . $order->id,
            ]);
        }
    }

    // Successo preautorizzazione con paypal. L'utente ha preautorizzato con successo da paypal il pagamento della quota di iscrizione all'evento ed è stato reindirizzato qui da paypal
    public function preauthSuccessUserPaypal(Request $request) {
        $orderId = $request->order_id;

        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        // Crea la voce in mailing list (id ordine, token)
        $order = Order::findOrFail($orderId);

        $authorization = $provider->authorizePaymentOrder($request->token);

        Log::info('AUTHORIZATION RESPONSE', $authorization);

        // Se l'ordine è già stato autorizzato, non fare nulla
        if (isset($authorization['error']) && $authorization['error']['details'][0]['issue'] === 'ORDER_ALREADY_AUTHORIZED') {
            if ($order->status === 4) {
                return redirect()->route('event-detail', Event::find($order->items->first()->product_code)->slug)->with('error', __('Order has been cancelled.'));
            } else if (!in_array($order->status, [0, 1])) {
                return redirect()->route('event-detail', Event::find($order->items->first()->product_code)->slug)->with('error', __('Order has been already processed.'));
            }
        }

        if ($authorization['status'] !== 'COMPLETED') {
            return response()->json(['success' => false, 'error' => 'Error authorizing payment']);
        }

        if ($order->status !== 1) {
            $event = Event::find($order->items->first()->product_code);
        } else {
            $order->update([
                'status' => 3, // Stato 3 = Preauthorized
            ]);

            $event = Event::find($order->items->first()->product_code);

            // Crea la voce in lista d'attesa
            EventWaitingList::create([
                'user_id' => $order->user_id,
                'event_id' => $event->id,
                'order_id' => $order->id,
            ]);
        }

        return view('website.shop.event-preauth-success', [
            'event' => $event,
        ]);
    }

    // Errore preautorizzazione con paypal
    public function preauthCancelUserPaypal(Request $request) {
        $orderId = $request->order_id;

        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        if ($request->token) {
            $result = $provider->voidAuthorizedPayment($request->token);
        }
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

    // La parte di finalizzazione dell'acquisto preautorizzato con paypal è gestita da un event listener che si attiva quando si libera un posto nell'evento
    // Finalizzazione acquisto preautorizzato con paypal (quando si libera un posto nell'evento)
    // public function capturePreauthorizedPaymentPaypal(EventWaitingList $eventWaitingList) {
    //     $provider = new PaypalClient;
    //     $provider->setApiCredentials(config('paypal'));
    //     $provider->getAccessToken();

    //     $paypalOrderId = null;

    //     if($eventWaitingList->order->payment_method === 'paypal' && isset($eventWaitingList->order->paypal_order_id)){
    //         $paypalOrderId = $eventWaitingList->order->paypal_order_id;
    //     } else {
    //         return response()->json(['success' => false, 'message' => 'Not a PayPal order.']);
    //     }

    //     $order = Order::where('paypal_order_id', $paypalOrderId)->first();

    //     // $paypalOrderDetails = $provider->showOrderDetails($paypalOrderId);
    //     $result = $provider->captureAuthorizedPayment(
    //         $paypalOrderId, // $paypalOrderDetails['id'],
    //         $order->invoice_id,
    //         $order->total, // $paypalOrderDetails['purchase_units'][0]['amount']['value'],
    //         'Finalized payment for LudoSport event "' . $eventWaitingList->event->name . '"',
    //     );


    //     if ($result['status'] === 'COMPLETED') {
    //         $order->update(['status' => 2, 'result' => json_encode($result)]);

    //         $event = $eventWaitingList->event;

    //         if($event->resultType() === 'enabling') {
    //             $event->instructorResults()->create([
    //                 'user_id' => $order->user_id,
    //                 'weapon_form_id' => $event->weapon_form_id,
    //             ]);
    //         } else if($event->resultType() === 'ranking') {
    //             $event->results()->create([
    //                 'user_id' => $order->user_id,
    //                 'war_points' => 0,
    //                 'style_points' => 0,
    //                 'total_points' => 0,
    //             ]);
    //         }

    //         $eventWaitingList->delete();

    //         return response()->json(['success' => true]);
    //     } else {
    //         $order->update(['status' => 3, 'result' => json_encode($result)]); // Stato fallito o annullato
    //         return response()->json(['success' => false, 'message' => 'Payment capture failed.']);
    //     }
    // }

    public function rankings() {

        $countries = Nation::all();
        $continents = [];

        foreach ($countries as $key => $country) {

            if ($country->academies->count() == 0) {
                continue;
            }

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

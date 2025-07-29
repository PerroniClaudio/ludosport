<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Event extends Model {
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    // resultType: 'ranking', 'enabling'

    protected $fillable = [
        'name',
        'description',
        'thumbnail',
        'user_id',
        'is_approved',
        'is_published',
        'start_date',
        'end_date',
        'location',
        'nation_id',
        'academy_id',
        'school_id',
        'user_id',
        'slug',
        'city',
        'address',
        'postal_code',
        'event_type',
        'price',
        'weapon_form_id',
        'max_participants',
        'block_subscriptions',
        'waiting_list_close_date',
        'internal_shop',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'waiting_list_close_date' => 'datetime',
    ];

    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    public function academy() {
        return $this->belongsTo(Academy::class);
    }

    public function school() {
        return $this->belongsTo(School::class);
    }

    public function results() {
        return $this->hasMany(EventResult::class);
    }

    public function instructorResults() {
        return $this->hasMany(EventInstructorResult::class);
    }

    public function waitingList() {
        return $this->hasMany(EventWaitingList::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function personnel() {
        return $this->belongsToMany(User::class, 'events_personnel', 'event_id', 'user_id')->where('is_disabled', '0');
    }

    public function type() {
        return $this->belongsTo(EventType::class, 'event_type');
    }

    public function resultType() {
        return strtolower($this->type->name ?? '') == 'training course' ? 'enabling' : 'ranking';
    }

    public function weaponForm() {
        return $this->belongsTo(WeaponForm::class);
    }

    public function eventTypes() {

        $types = EventType::all();

        $eventTypes = [];

        foreach ($types as $type) {
            $eventTypes[] = [
                'value' => $type->id,
                'label' => $type->name,
            ];
        }

        return $eventTypes;
    }

    public function isFree() {
        return !isset($this->price) || $this->price == 0;
    }

    public function isWaitingList() {
        $isEnabling = $this->resultType() === 'enabling';
        $participants = 0;
        if ($isEnabling) {
            $participants = $this->instructorResults->count();
        } else {
            $participants = $this->results->count();
        }
        // Se max_participants è null o 0 allora non c'è limite di partecipanti, quindi nemmeno la waiting list
        return (!!$this->max_participants && ($participants >= $this->max_participants));
    }

    public function eventMultiplier() {
        switch ($this->event_type) {
            case 4:
                return 2;
                break;
            default:
                return 0.5;
                break;
        }
    }

    public function eventBonusPoints($type_of_bonus) {
        switch ($type_of_bonus) {
            case "FIRST_IN_WAR":
                switch ($this->event_type) {
                    case 2:
                        return 15;
                        break;
                    case 3:
                        return 45;
                        break;
                    case 4:
                        return 75;
                        break;
                    default:
                        return 0;
                        break;
                }
                break;
            case "SECOND_IN_WAR":
                switch ($this->event_type) {
                    case 2:
                        return 10;
                        break;
                    case 3:
                        return 30;
                        break;
                    case 4:
                        return 50;
                        break;
                    default:
                        return 0;
                        break;
                }
                break;
            case "THIRD_IN_WAR":
                switch ($this->event_type) {
                    case 2:
                        return 5;
                        break;
                    case 3:
                        return 15;
                        break;
                    case 4:
                        return 25;
                        break;
                    default:
                        return 0;
                        break;
                }
                break;
            case "FIRST_IN_STYLE":
                switch ($this->event_type) {
                    case 2:
                        return 15;
                        break;
                    case 3:
                        return 45;
                        break;
                    case 4:
                        return 75;
                        break;
                    default:
                        return 0;
                        break;
                }
                break;
            case "SECOND_IN_STYLE":
                switch ($this->event_type) {
                    case 2:
                        return 10;
                        break;
                    case 3:
                        return 30;
                        break;
                    case 4:
                        return 50;
                        break;
                    default:
                        return 0;
                        break;
                }
                break;
            case "THIRD_IN_STYLE":
                switch ($this->event_type) {
                    case 2:
                        return 5;
                        break;
                    case 3:
                        return 15;
                        break;
                    case 4:
                        return 25;
                        break;
                    default:
                        return 0;
                        break;
                }
                break;
            default:
                return 0;
                break;
        }
    }

    // Recupera la posizione dell'utente nel ranking di questo evento
    public function getUserRankPosition (User $user) {
        if (!$user || !$this->results()->where('user_id', $user->id)->exists()) {
            return null; // Se l'utente non è fornito, ritorna null
        }
        if(!in_array($this->type->name, ['School Tournament', 'Academy Tournament', 'National Tournament'])) {
            return null; // Se il tipo di evento non è tra quelli che hanno un ranking, ritorna null
        }
        if ( $this->results->where('total_war_points', '>', 0)->count() == 0 && $this->results->where('total_style_points', '>', 0)->count() == 0 ) {
            return null; // Se non ci sono risultati con punti > 0, ritorna null
        }

        // Ordina i risultati in base al punteggio e poi per nome e cognome utente
        $sortedResults = $this->results()
            ->with('user')
            ->leftJoin('users', 'event_results.user_id', '=', 'users.id')
            ->orderByRaw('(total_war_points + total_style_points) DESC')
            ->orderBy('total_style_points', 'DESC')
            ->orderBy('users.name')
            ->orderBy('users.surname')
            ->get();

        // Trova la posizione dell'utente
        $index = $sortedResults->search(function($result) use ($user) {
            return $result->user_id == $user->id;
        });
        return $index !== false ? $index + 1 : null;
    }
}

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
        'is_free',
        'price',
        'weapon_form_id',
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

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function type() {
        return $this->belongsTo(EventType::class, 'event_type');
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

    public function eventMultiplier() {
        switch ($this->event_type) {
            case 3:
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
}

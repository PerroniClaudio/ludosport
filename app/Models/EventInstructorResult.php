<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventInstructorResult extends Model {
    use HasFactory;

    // result: ['passed', 'review', 'failed'] default: 'null';
    // stage: ['registered', 'pending', 'confirmed'] default: 'registered';
    // notes: max 100 chars;

    protected $fillable = [
        'event_id',
        'user_id',
        'result',
        'notes',
        'stage',
        'weapon_form_id',
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function weaponForm() {
        return $this->belongsTo(WeaponForm::class);
    }

}

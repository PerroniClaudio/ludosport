<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventResult extends Model {
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'war_points',
        'style_points',
        'bonus_war_points',
        'bonus_style_points',
        'total_war_points',
        'total_style_points',
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}

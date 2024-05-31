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
        'total_points'
    ];
}

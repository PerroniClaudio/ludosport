<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Export extends Model {
    use HasFactory;

    protected $fillable = [
        'file',
        'status',
        'type',
        'log',
        'filters',
        'user_id',
    ];

    private $export_types = [
        'users',
        'user_roles',
        'users_course',
        'users_academy',
        'users_school',
        'event_participants',
        'event_war',
        'event_style',
    ];


    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getExportTypes() {
        return collect($this->export_types);
    }
}

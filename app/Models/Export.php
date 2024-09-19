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
        'instructor_event_results',
        'event_war',
        'event_style',
        'orders',
    ];


    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getExportTypes() {
        return collect($this->export_types);
    }

    public static function getAvailableExportsByRole($role) {
        switch ($role) {
            case 'admin':
                return ['users', 'user_roles', 'users_course', 'users_academy', 'users_school', 'event_participants', 'instructor_event_results', 'event_war', 'event_style', 'orders'];
            case 'rector':
                return ['users', 'user_roles', 'users_course', 'users_academy', 'users_school', 'event_participants'];
            case 'dean':
            case 'manager':
                return ['users', 'user_roles', 'users_course', 'users_school', 'event_participants'];
            case 'technician':
                return ['event_participants', 'instructor_event_results', 'event_war', 'event_style'];
            default:
                return [];
        }
    }
}

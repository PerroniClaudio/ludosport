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
        'users_nation',
        'users_academy',
        'users_school',
        'users_course',
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
                return ['users', 'user_roles', 'users_nation', 'users_academy', 'users_school', 'users_course', 'event_participants', 'instructor_event_results', 'event_war', 'event_style', 'orders'];
            case 'rector':
            case 'manager':
                return ['users', 'user_roles', 'users_academy', 'users_school', 'users_course', 'event_participants'];
            case 'dean':
                return ['users', 'user_roles', 'users_school', 'users_course', 'event_participants'];
            case 'technician':
                return ['event_participants', 'instructor_event_results'];
            default:
                return [];
        }
    }
}

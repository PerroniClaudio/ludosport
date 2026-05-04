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
        'user_role_id',
        'user_academy_id',
        'user_school_id',
    ];

    private $export_types = [
        'users',
        'user_roles',
        'users_nation',
        'users_academy',
        'schools',
        'users_school',
        'users_course',
        'event_participants',
        'instructor_event_results',
        'event_war',
        'event_style',
        'orders',
    ];

    // Utente che ha richiesto l'export
    public function user() {
        return $this->belongsTo(User::class);
    }
    // Ruolo dell'utente che ha richiesto l'export
    public function userRole() {
        return $this->belongsTo(Role::class, 'user_role_id');
    }
    // Accademia dell'utente che ha richiesto l'export
    public function userAcademy() {
        return $this->belongsTo(Academy::class, 'user_academy_id');
    }
    // Scuola dell'utente che ha richiesto l'export
    public function userSchool() {
        return $this->belongsTo(School::class, 'user_school_id');
    }

    public function getExportTypes() {
        return collect($this->export_types);
    }

    public static function getAvailableExportsByRole($role) {
        switch ($role) {
            case 'admin':
                return ['users', 'user_roles', 'users_nation', 'users_academy', 'schools', 'users_school', 'users_course', 'event_participants', 'instructor_event_results', 'event_war', 'event_style', 'orders'];
            case 'rector':
                return ['user_roles', 'users_academy', 'schools', 'users_school', 'users_course', 'event_participants'];
            case 'manager':
                return ['user_roles', 'users_academy', 'users_school', 'users_course', 'event_participants'];
            case 'dean':
                return ['user_roles', 'users_school', 'users_course', 'event_participants'];
            case 'technician':
                return ['event_participants', 'instructor_event_results'];
            default:
                return [];
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model {
    use HasFactory;

    protected $fillable = [
        'file',
        'status',
        'type',
        'log',
        'user_id',
    ];

    private $import_types = [
        'new_users',
        'users_course',
        'users_academy',
        'users_school',
        'event_participants',
        'event_war',
        'event_style',
        'event_instructor_results',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getImportTypes() {
        return collect($this->import_types);
    }

    public static function getAvailableImportsByRole($role) {
        switch ($role) {
            case 'admin':
                return ['new_users', 'users_course', 'users_academy', 'users_school', 'event_participants', 'event_war', 'event_style', 'event_instructor_results'];
            case 'rector':
                return ['new_users', 'users_course', 'users_academy', 'users_school', 'event_participants'];
            case 'dean':
            case 'manager':
                return ['new_users', 'users_course', 'users_school'];
            case 'technician':
                return ['event_war', 'event_style', 'event_instructor_results'];
            default:
                return [];
        }
    }
}

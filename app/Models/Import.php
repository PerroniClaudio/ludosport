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
        'users_school'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getImportTypes() {
        return collect($this->import_types);
    }
}

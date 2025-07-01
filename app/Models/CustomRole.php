<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomRole extends Model {
    use HasFactory;

    protected $fillable = ['name'];

    function users() {
        return $this->belongsToMany(User::class, 'custom_roles_users', 'custom_role_id', 'user_id');
    }
}

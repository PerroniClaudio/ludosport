<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clan extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'school_id',
        'slug'
    ];

    public function school() {
        return $this->belongsTo(School::class);
    }

    public function users() {
        return $this->belongsToMany(User::class, 'clans_users', 'clan_id', 'user_id');
    }
}

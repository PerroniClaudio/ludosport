<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nation extends Model {
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'code',
        'flag',
        'continent'
    ];

    public function academies() {
        return $this->hasMany(Academy::class);
    }

    public function users() {
        return $this->hasMany(User::class);
    }

    public function clans() {
        return $this->hasMany(Clan::class);
    }
}

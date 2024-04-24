<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nation extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'flag'
    ];

    public function academies() {
        return $this->hasMany(Academy::class);
    }

    public function users() {
        return $this->hasMany(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class School extends Model {
    use HasFactory, Searchable;

    protected $fillable = [
        'name',
        'nation_id',
        'academy_id',
        'slug'
    ];

    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    public function athletes() {
        return $this->belongsToMany(User::class, 'schools_athletes', 'school_id', 'user_id');
    }

    public function personnel() {
        return $this->belongsToMany(User::class, 'schools_personnel', 'school_id', 'user_id');
    }

    public function academy() {
        return $this->belongsTo(Academy::class);
    }

    public function clan() {
        return $this->hasMany(Clan::class);
    }
}

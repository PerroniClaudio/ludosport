<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Academy extends Model {
    use HasFactory, Searchable;

    protected $fillable = [
        'name',
        'nation_id',
        'slug',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'coordinates'
    ];

    public function toSearchableArray() {
        return [
            'name' => $this->name,
            'school' => $this->nation->name,
            'slug' => $this->slug
        ];
    }



    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    public function users() {
        return $this->hasMany(User::class);
    }

    public function schools() {
        return $this->hasMany(School::class);
    }

    public function athletes() {
        return $this->belongsToMany(User::class, 'academies_athletes', 'academy_id', 'user_id');
    }

    public function personnel() {
        return $this->belongsToMany(User::class, 'academies_personnel', 'academy_id', 'user_id');
    }
}

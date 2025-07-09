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
        'slug',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'coordinates',
        'email',
        'main_dean',
    ];

    public function toSearchableArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'academy' => $this->academy->name,
            'slug' => $this->slug
        ];
    }

    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    public function athletes() {
        return $this->belongsToMany(User::class, 'schools_athletes', 'school_id', 'user_id')->where('is_disabled', '0')->withPivot('is_primary');
    }

    public function personnel() {
        return $this->belongsToMany(User::class, 'schools_personnel', 'school_id', 'user_id')->where('is_disabled', '0')->withPivot('is_primary');
    }

    public function academy() {
        return $this->belongsTo(Academy::class);
    }

    public function clan() {
        return $this->hasMany(Clan::class)->where('is_disabled', '0');
    }

    public function mainDean() {
        return $this->belongsTo(User::class, 'main_dean', 'id');
    }

    public function dean() {
        // If main_dean is set, return it
        if ($this->main_dean) {
            return $this->mainDean;
        }

        // Otherwise, find the first dean in the personnel
        $deans = $this->personnel()->whereHas('roles', function ($query) {
            $query->where('name', 'dean');
        })->get();

        return $deans->first();
    }
}

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
        'coordinates',
        'picture',
        'email'
    ];

    public function toSearchableArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'school' => $this->nation->name,
            'slug' => $this->slug
        ];
    }



    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    public function users() {
        return $this->hasMany(User::class)->where('is_disabled', '0');
    }

    public function schools() {
        return $this->hasMany(School::class)->where('is_disabled', '0');
    }

    public function athletes() {
        return $this->belongsToMany(User::class, 'academies_athletes', 'academy_id', 'user_id')->where('is_disabled', '0')->withPivot('is_primary');
    }

    public function personnel() {
        return $this->belongsToMany(User::class, 'academies_personnel', 'academy_id', 'user_id')->where('is_disabled', '0')->withPivot('is_primary');
    }

    public function rector() {
        $rectors = $this->personnel()->whereHas('roles', function ($query) {
            $query->where('name', 'rector');
        })->get();
        // Se lo trova tra quelli che hanno l'accademia come principale restituisce quello
        foreach ($rectors as $r) {
            $primaryAcademy = $r->primaryAcademy();
            if ($primaryAcademy && ($primaryAcademy->id == $this->id)) {
                return $r;
            }
        }
        // Per ora si considera rettore solo chi ha l'accademia come principale.
        // Altrimenti cerca tra tutto il personale (anche se non ha l'accademia come principale)
        // foreach ($rectors as $r) {
        //     if ($r->academies->firstWhere('id', $this->id)) {
        //         return $r;
        //     }
        // }
        return null;
    }
}

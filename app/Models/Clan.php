<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Clan extends Model {
    use HasFactory, Searchable;

    protected $fillable = [
        'name',
        'school_id',
        'slug',
        'weapon_form_id',
    ];

    public function toSearchableArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'school' => $this->school->name,
            'slug' => $this->slug
        ];
    }

    public function school() {
        return $this->belongsTo(School::class);
    }

    public function users() {
        return $this->belongsToMany(User::class, 'clans_users', 'clan_id', 'user_id')->where('is_disabled', '0');
    }

    public function personnel() {
        return $this->belongsToMany(User::class, 'clans_personnel', 'clan_id', 'user_id')->where('is_disabled', '0');
    }

    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    public function academy() {
        // return $this->school->belongsTo(Academy::class);
        return $this->school ? $this->school->belongsTo(Academy::class) : null;

        // Questa soluzione richiede un pacchetto aggiuntivo: composer require staudenmeir/eloquent-has-many-deep
        // E questo codice nel modello: use Staudenmeir\EloquentHasManyDeep\HasRelationships;    class Clan extends Model { use HasRelationships;
        // return $this->belongsToThrough(Academy::class, School::class, 'school_id', 'id', 'school_id', 'academy_id');
    }

    public function weaponForm() {
        return $this->belongsTo(WeaponForm::class);
    }
}

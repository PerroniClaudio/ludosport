<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeaponForm extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
    ];

    public function users() {
        return $this->belongsToMany(User::class, 'weapon_forms_users', 'weapon_form_id', 'user_id')
        ->withPivot('awarded_at as awarded_at')
        ->withTimestamps();
    }


    // Weapon forms personnel è per gli istruttori
    public function personnel() {
        return $this->belongsToMany(User::class, 'weapon_forms_personnel', 'weapon_form_id', 'user_id')
            ->withPivot('awarded_at as awarded_at')
            ->withTimestamps();
    }

    // Weapon forms technicians è per i tecnici
    public function technicians() {
        return $this->belongsToMany(User::class, 'weapon_forms_technicians', 'weapon_form_id', 'user_id')
            ->withPivot('awarded_at as awarded_at')
            ->withTimestamps();
    }

    public function events() {
        return $this->hasMany(Event::class);
    }

    public function clans() {
        return $this->hasMany(Clan::class);
    }
}

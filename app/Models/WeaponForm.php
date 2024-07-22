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
        return $this->belongsToMany(User::class, 'weapon_forms_users', 'weapon_form_id', 'user_id');
    }

    public function events() {
        return $this->hasMany(Event::class);
    }
}

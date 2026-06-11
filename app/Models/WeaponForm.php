<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeaponForm extends Model {
    use HasFactory;

    public const DEFAULT_BASE_FORM_NAMES = [
        'Form 1',
        'Form 2',
        'Form Y',
    ];

    public const DEFAULT_LONG_SABER_FORM_NAMES = [
        'Form 3 Long Saber',
        'Form 4 Long Saber',
        'Form 5 Long Saber',
    ];

    public const DEFAULT_DUAL_SABER_FORM_NAMES = [
        'Form 3 Dual Sabers',
        'Form 4 Dual Sabers',
        'Form 5 Dual Sabers',
    ];

    public const DEFAULT_SABERSTAFF_FORM_NAMES = [
        'Form 3 Saberstaff',
        'Form 4 Saberstaff',
        'Form 5 Saberstaff',
    ];

    public const POSITION_FIELDS = [
        'position_before_specific',
        'position_long_saber',
        'position_dual_saber',
        'position_saberstaff',
        'position_after_specific',
    ];

    protected $fillable = [
        'name',
        'image',
        'position_before_specific',
        'position_long_saber',
        'position_dual_saber',
        'position_saberstaff',
        'position_after_specific',
    ];

    protected $casts = [
        'position_before_specific' => 'boolean',
        'position_long_saber' => 'boolean',
        'position_dual_saber' => 'boolean',
        'position_saberstaff' => 'boolean',
        'position_after_specific' => 'boolean',
    ];

    public static function defaultFormNames(): array
    {
        return [
            ...self::DEFAULT_BASE_FORM_NAMES,
            ...self::DEFAULT_LONG_SABER_FORM_NAMES,
            ...self::DEFAULT_DUAL_SABER_FORM_NAMES,
            ...self::DEFAULT_SABERSTAFF_FORM_NAMES,
        ];
    }

    public static function isDefaultLayoutName(string $name): bool
    {
        return in_array($name, self::defaultFormNames(), true);
    }

    public function isDefaultLayoutForm(): bool
    {
        return self::isDefaultLayoutName($this->name);
    }

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

<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;

class User extends Authenticatable implements MustVerifyEmail {
    use HasFactory, Notifiable, HasApiTokens, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'subscription_year',
        'academy_id',
        'school_id',
        'nation_id',
        'unique_code',
        'profile_picture',
    ];

    public function toSearchableArray() {
        return [
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
        ];
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    public function academies() {
        return $this->belongsToMany(Academy::class, 'academies_personnel', 'user_id', 'academy_id');
    }

    public function academyAthletes() {
        return $this->belongsToMany(Academy::class, 'academies_athletes', 'user_id', 'academy_id');
    }

    public function schools() {
        return $this->belongsToMany(School::class, 'schools_personnel', 'user_id', 'school_id');
    }

    public function schoolAthletes() {
        return $this->belongsToMany(School::class, 'schools_athletes', 'user_id', 'school_id');
    }

    public function clans() {
        return $this->belongsToMany(Clan::class, 'clans_users', 'user_id', 'clan_id');
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function imports() {
        return $this->hasMany(Import::class);
    }

    public function exports() {
        return $this->hasMany(Export::class);
    }

    public function events() {
        return $this->hasMany(Event::class);
    }

    public function eventResults() {
        return $this->hasMany(EventResult::class);
    }

    public function routes() {

        $role = $this->getRole();


        switch ($role) {
            case 'admin':
                return collect([
                    (object)[
                        'label' => 'any',
                        'name' => 'admin.any.index',
                    ]
                ]);
            case 'athlete':
                return collect([]);
            case 'rector':
                return collect([
                    (object)[
                        'label' => 'users',
                        'active' => 'users.*',
                        'name' => 'dashboard',
                    ],

                ]);
            case 'dean':
                return collect([
                    (object)[
                        'label' => 'users',
                        'active' => 'users.*',
                        'name' => 'preside.users.index',
                    ],
                    (object)[
                        'label' => 'scuola',
                        'active' => 'schools.*',
                        'name' => 'preside.scuola.index',
                    ]
                ]);
            case 'manager':
                return collect([
                    (object)[
                        'label' => 'users',
                        'active' => 'users.*',
                        'name' => 'manager.users.index',
                    ],
                    (object)[
                        'label' => 'scuola',
                        'active' => 'schools.*',
                        'name' => 'manager.scuola.index',
                    ]
                ]);
            case 'technician':
                return collect([
                    (object)[
                        'label' => 'users',
                        'active' => 'users.*',
                        'name' => 'dashboard',
                    ],
                    (object)[
                        'label' => 'eventi',
                        'active' => 'events.*',
                        'name' => 'technician.events.index',
                    ],
                    (object)[
                        'label' => 'istruttori',
                        'active' => 'istruttori.*',
                        'name' => 'dashboard',
                    ],
                ]);
            case 'instructor':
                return collect([
                    (object)[
                        'label' => 'users',
                        'active' => 'users.*',
                        'name' => 'istruttore.users.index',
                    ],
                    (object)[
                        'label' => 'eventi',
                        'active' => 'events.*',
                        'name' => 'istruttore.events.index',
                    ],
                    (object)[
                        'label' => 'clan',
                        'active' => 'clans.*',
                        'name' => 'istruttore.clan.index',
                    ],
                ]);
            default:
                return collect([]);
        }
    }

    public function allowedRoles(): array {
        return $this->roles()->get()->map(function ($role) {
            return $role->name;
        })->toArray();
    }

    public function hasRole(string $role): bool {
        $selectedRole = Role::where('name', $role)->first();
        $user = $selectedRole->users()->where('user_id', $this->id)->get();
        return $user->count() > 0;
    }

    public function getRole() {
        return session('role', $this->roles()->first()->name);
    }
}

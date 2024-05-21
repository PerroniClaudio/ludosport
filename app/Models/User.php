<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail {
    use HasFactory, Notifiable, HasApiTokens;

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
        'role',
        'subscription_year',
        'academy_id',
        'school_id',
        'nation_id',
    ];

    private $allowedRoles = [
        'admin',
        'user',
        'rettore',
        'preside',
        'manager',
        'tecnico',
        'istruttore'
    ];

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

    public function routes() {
        switch ($this->role) {
            case 'admin':
                return collect([
                    (object)[
                        'label' => 'any',
                        'name' => 'admin.any.index',
                    ]
                ]);
            case 'user':
                return collect([
                    (object)[
                        'label' => 'customization',
                        'name' => 'user.customization.index',
                    ]
                ]);
            case 'rettore':
                return collect([
                    (object)[
                        'label' => 'users',
                        'name' => 'rettore.users.index',
                    ],
                    (object)[
                        'label' => 'accademie',
                        'name' => 'rettore.accademie.index',
                    ]
                ]);
            case 'preside':
                return collect([
                    (object)[
                        'label' => 'users',
                        'name' => 'preside.users.index',
                    ],
                    (object)[
                        'label' => 'scuola',
                        'name' => 'preside.scuola.index',
                    ]
                ]);
            case 'manager':
                return collect([
                    (object)[
                        'label' => 'users',
                        'name' => 'manager.users.index',
                    ],
                    (object)[
                        'label' => 'scuola',
                        'name' => 'manager.scuola.index',
                    ]
                ]);
            case 'tecnico':
                return collect([
                    (object)[
                        'label' => 'users',
                        'name' => 'dashboard',
                    ],
                    (object)[
                        'label' => 'eventi',
                        'name' => 'technician.events.index',
                    ],
                    (object)[
                        'label' => 'istruttori',
                        'name' => 'dashboard',
                    ],
                ]);
            case 'istruttore':
                return collect([
                    (object)[
                        'label' => 'users',
                        'name' => 'istruttore.users.index',
                    ],
                    (object)[
                        'label' => 'eventi',
                        'name' => 'istruttore.events.index',
                    ],
                    (object)[
                        'label' => 'clan',
                        'name' => 'istruttore.clan.index',
                    ],
                ]);
            default:
                return collect([]);
        }
    }

    public function academy() {
        return $this->belongsTo(Academy::class);
    }

    public function school() {
        return $this->belongsTo(School::class);
    }

    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    public function getAllowedRolesWithoutAdmin(): array {
        $allowedRoles = $this->allowedRoles;
        $key = array_search('admin', $allowedRoles);
        if ($key !== false) {
            unset($allowedRoles[$key]);
        }
        return array_values($allowedRoles);
    }

    public function clans() {
        return $this->belongsToMany(Clan::class, 'clans_users', 'user_id', 'clan_id');
    }
}

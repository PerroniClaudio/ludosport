<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
                return [
                    'any'
                ];
            case 'user':
                return [
                    'customization'
                ];
            case 'rettore':
                return [
                    'users',
                    'accademie',
                ];
            case 'preside':
                return [
                    'users',
                    'scuola', // Solo la sua scuola!
                ];
            case 'manager':
                return [
                    'users',
                    'scuola', // Solo la sua scuola!
                ];
            case 'tecnico':
                return [
                    'users',
                    'eventi',
                    'istruttori'
                ];
            case 'istruttore':
                return [
                    'users',
                    'eventi',
                    'clan',
                ];
            default:
                return [];
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

    public function getAllowedRolesWithoutAdmin(): array
    {
        $allowedRoles = $this->allowedRoles;
        $key = array_search('admin', $allowedRoles);
        if ($key !== false) {
            unset($allowedRoles[$key]);
        }
        return array_values($allowedRoles);
    }
}

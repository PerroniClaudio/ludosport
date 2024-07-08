<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model {
    use HasFactory;

    protected $types = [
        1 => 'info',
        2 => 'priority_info',
        3 => 'danger',
        4 => 'direct_message',
    ];

    protected $fillable = [
        'object',
        'content',
        'type',
        'user_id',
        'role_id',
        'is_deleted'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function getType() {
        return $this->types[$this->type];
    }

    public function getTypes() {
        $xdd = array_filter($this->types, function ($key) {
            return $key !== 'direct_message';
        });

        return $xdd;
    }
}

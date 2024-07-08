<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementUser extends Model {
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'user_id',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chart extends Model {
    use HasFactory;

    protected $fillable = ['note', 'data', 'created_at'];

    protected $casts = [
        'data' => 'array',
    ];
}

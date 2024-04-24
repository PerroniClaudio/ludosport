<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Academy extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'nation_id',
        'slug'
    ];

    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    public function users() {
        return $this->hasMany(User::class);
    }
}

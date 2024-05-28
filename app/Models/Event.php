<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
        'description',
        'thumbnail',
        'user_id',
        'is_approved',
        'is_published',
        'start_date',
        'end_date',
        'location',
        'nation_id',
        'academy_id',
        'school_id',
        'user_id',
        'slug',
        'city',
        'address',
        'postal_code',
    ];

    public function nation() {
        return $this->belongsTo(Nation::class);
    }

    public function academy() {
        return $this->belongsTo(Academy::class);
    }

    public function school() {
        return $this->belongsTo(School::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model {
    use HasFactory;

    protected $fillable = ['type', 'start_date', 'end_date'];

    protected function academy() {
        return $this->belongsTo(Academy::class);
    }

    protected function user() {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {
    use HasFactory;

    protected $fillable = ['name', 'surname', 'address', 'vat', 'user_id', 'sdi', 'want_invoice', 'is_business', 'business_name'];

    protected function user() {
        return $this->belongsTo(User::class);
    }
}

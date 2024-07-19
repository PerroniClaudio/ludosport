<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Order extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'payment_method',
        'order_number',
        'result',
        'invoice_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function invoice() {
        return $this->belongsTo(\App\Models\Invoice::class);
    }

    public function items() {
        return $this->hasMany(OrderItem::class);
    }
}

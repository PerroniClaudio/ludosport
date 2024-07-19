<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model {
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_type',
        'product_name',
        'product_code',
        'quantity',
        'price',
        'vat',
        'total',
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }
}

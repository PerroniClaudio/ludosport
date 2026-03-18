<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'academy_id',
        'status',
        'total',
        'payment_method',
        'order_number',
        'result',
        'invoice_id',
        'paypal_order_id',
        'stripe_payment_intent_id',
        'approved_by',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function academy() {
        return $this->belongsTo(Academy::class);
    }

    public function invoice() {
        return $this->belongsTo(\App\Models\Invoice::class);
    }

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function approvedBy() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            if ($order->isDirty('approved_by') && !is_null($order->approved_by)) {
                if (!User::where('id', $order->approved_by)->exists()) {
                    throw new \InvalidArgumentException('The approved_by user does not exist.');
                }
            }
        });
    }
}

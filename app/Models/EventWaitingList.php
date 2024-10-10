<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventWaitingList extends Model
{
    use HasFactory;

    protected $table = 'event_waiting_list';

    protected $fillable = [
        'user_id',
        'event_id',
        'order_id',
        'payment_intent_id',
        'paypal_order_id',
        'payment_deadline',
        'is_waiting_payment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
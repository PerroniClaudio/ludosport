<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankRequest extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rank_id',
        'status',
        'user_to_promote_id',
        'approved_by',
        'approved_at',
        'reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    protected $statusTypes = [
        'pending',
        'approved',
        'rejected',
    ];

    public function requestedBy() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userToPromote() {
        return $this->belongsTo(User::class, 'user_to_promote_id');
    }

    public function rank() {
        return $this->belongsTo(Rank::class);
    }

    public function approvedBy() {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

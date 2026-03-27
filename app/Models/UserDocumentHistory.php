<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocumentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_path',
        'was_admin_approved',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'was_admin_approved' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTerm extends Model
{
    protected $fillable = [
        'version',
        'original_name',
        'stored_name',
        'path',
        'disk',
        'mime_type',
        'size_bytes',
        'uploaded_by',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

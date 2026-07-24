<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StallApplicationDocument extends Model
{
    protected $fillable = [
        'stall_application_id',
        'document_type',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(StallApplication::class, 'stall_application_id');
    }
}

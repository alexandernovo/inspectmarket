<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StallApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_number',
        'tenant_id',
        'request_source',
        'stall_id',
        'business_name',
        'business_category',
        'business_address',
        'preferred_section',
        'preferred_stall_number',
        'status',
        'remarks',
        'reviewed_by',
        'reviewed_at',
        'birth_date',
        'civil_status',
        'sex',
        'contact_number',
        'email',
        'business_owner',
        'tin_number',
        'business_nature',
        'trade_name',
        'permit_issued_at',
        'other_business',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'birth_date' => 'date',
            'permit_issued_at' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function stall(): BelongsTo
    {
        return $this->belongsTo(Stall::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StallApplicationDocument::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivestockInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'tenant_id',
        'inspector_id',
        'livestock_type',
        'owner_name',
        'address',
        'contact_number',
        'email',
        'request_source',
        'scheduled_at',
        'status',
        'animal_count',
        'inspection_result',
        'findings',
        'inspected_at',
        'remarks',
        'breed',
        'sex',
        'animal_age',
        'live_weight',
        'carcass_weight',
        'source_location',
        'purpose',
        'ante_mortem_findings',
        'post_mortem_findings',
        'certificate_number',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'inspected_at' => 'datetime',
            'live_weight' => 'decimal:2',
            'carcass_weight' => 'decimal:2',
            'ante_mortem_findings' => 'array',
            'post_mortem_findings' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}

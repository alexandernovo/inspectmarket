<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashTicketAssignment extends Model
{
    protected $fillable = [
        'assignment_number',
        'collector_id',
        'assigned_by',
        'stall_section',
        'ticket_start',
        'ticket_end',
        'ticket_quantity',
        'assigned_date',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
        ];
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function collections(): HasMany
    {
        return $this->hasMany(CashTicketCollection::class);
    }
}

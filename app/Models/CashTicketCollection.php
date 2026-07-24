<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTicketCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_number',
        'collector_id',
        'recorded_by',
        'stall_section',
        'ticket_quantity',
        'amount',
        'collection_date',
        'status',
        'remarks',
        'cash_ticket_assignment_id',
        'ticket_start',
        'ticket_end',
        'shortage_amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'collection_date' => 'date',
            'shortage_amount' => 'decimal:2',
        ];
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(CashTicketAssignment::class, 'cash_ticket_assignment_id');
    }
}

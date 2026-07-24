<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'tenant_id',
        'stall_application_id',
        'amount',
        'period_month',
        'due_date',
        'paid_at',
        'payment_method',
        'status',
        'receipt_path',
        'recorded_by',
        'or_number',
        'shortage_amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'period_month' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'shortage_amount' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function stallApplication(): BelongsTo
    {
        return $this->belongsTo(StallApplication::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

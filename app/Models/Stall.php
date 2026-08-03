<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stall extends Model
{
    use HasFactory;

    protected $fillable = [
        'section',
        'stall_number',
        'monthly_rate',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'monthly_rate' => 'decimal:2',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(StallApplication::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketStall extends Model
{
    protected $fillable = [
        'section',
        'stall_no',
        'status',
        'tenant_name',
        'business_name'
    ];
}
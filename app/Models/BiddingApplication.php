<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiddingApplication extends Model
{
    protected $fillable = [
        'name_owner',
        'tin',
        'address',
        'cellphone_no',
        'business_type',
        'nature_business',
        'category',
        'business_trade_name',
        'stall_applied',
        'alternative_stall',
        'other_business',
        'date_filed',
        'received_by',
        'remarks',
        'signature_name',
    ];
}
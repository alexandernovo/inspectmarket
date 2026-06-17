<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class WasteCollection extends Model
{
    protected $table = 'wastecollection';

    protected $primaryKey = 'wastecollect_id';

    public $timestamps = true;

    protected $fillable = [
        'barangay',
        'schedule_from',
        'schedule_to',
        'recyclable',
        'biodegradable',
        'nonbio',
        'specialwaste',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $table = 'records';

    protected $primaryKey = 'record_id';

    public $timestamps = true;

    protected $fillable = [
        "staff_id",
        'typeincident',
        'datetimeoccurence',
        'barangay',
        'specificlocation',
        'detaileddesc',
        'involvedinjured',
        'involveddead',
        'filesubmitted',
        'status',
        'affectedfamilies',
        'individuals',
        'evacuationfamilies',
        'evacuationindividuals',
        'remarks',
        'clearingoperations',
        'quantity',
        'unit',
        'description',
        'propertyno',
        'dateacquired',
        'amount',
        'typeOfRecord',
        'profile',
        'background',
        'cause',
    ];
}

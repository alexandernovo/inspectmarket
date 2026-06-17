<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $table = 'users';

    public $timestamps = true;

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'username',
        'designation',
        'email',
        'address',
        'phone_num',
        'status',
        'password',
        'usertype',
    ];

    protected $hidden = [
        'password'
    ];
}

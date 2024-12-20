<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'address',
        'venue',
        'phone',
        'email',
        'created_by',
        'updated_by'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{


    protected $fillable = [
        'name',
        'address',
        'venue',
        'number',
        'email',
        'venue',
        'date',
        'message',
        'created_by',
        'updated_by'
    ];

    // public function booking(): HasOne
    // {
    //     return $this->hasOne(Booking::class);
    // }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{

    protected  $fillable = [
        'description',
        'name',
        'user_id',
        'milestone_id',
        'is_in_progress',
        'is_completed',
        'start_date',
        'completion_date'
    ];
    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['name', 'drive_id', 'mime_type', 'view_link', 'folder_id', 'user_id', 'drive_link'];

    protected $except = [
        'admin/files/*'  // Add this line
    ];
}

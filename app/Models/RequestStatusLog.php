<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestStatusLog extends Model
{
    protected $fillable = [
        'request_id',
        'from_status',
        'to_status',
        'changed_by',
    ];
}


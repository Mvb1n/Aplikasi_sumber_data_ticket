<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'status',
        'response_code',
        'response_body',
    ];
}

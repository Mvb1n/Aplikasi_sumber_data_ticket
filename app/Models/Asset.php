<?php

namespace App\Models;

use App\Events\AssetDeleted;
use App\Events\AssetUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Events\AssetCreated;

class Asset extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'serial_number', 'category', 'status', 'site_location_code'];
    protected $dispatchesEvents = [
    'created' => AssetCreated::class,
];
}

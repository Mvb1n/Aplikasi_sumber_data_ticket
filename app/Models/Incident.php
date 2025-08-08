<?php

namespace App\Models;

use App\Events\IncidentReported;
use App\Events\IncidentCancelled;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Incident extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'reporter_email', 'site_location_code', 'specific_location', 'chronology', 'involved_asset_sn'];

    protected $dispatchesEvents = [
    'created' => IncidentReported::class,
];
}


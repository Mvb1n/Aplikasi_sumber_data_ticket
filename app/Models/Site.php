<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = ['name', 'location_code', 'address'];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function incidents(): HasMany
    {
        // Pastikan Anda memiliki model bernama 'Incident'
        return $this->hasMany(Incident::class);
    }
}

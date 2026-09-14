<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    protected $fillable = [
        'vehicle_id',
        'started_at',
        'ended_at',
        'avg_speed',
        'max_speed',
        'avg_coolant_temp_c',
        'max_coolant_temp_c',
        'distance_km',
        'start_address',
        'end_address',
        'route',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'route' => 'array',
        'avg_speed' => 'float',
        'max_speed' => 'float',
        'avg_coolant_temp_c' => 'float',
        'max_coolant_temp_c' => 'float',
        'distance_km' => 'float',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}

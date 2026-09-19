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
        'health_events',
        'fuel_used_liters',
        'avg_fuel_consumption_l100km',
        'eco_score',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'route' => 'array',
        'health_events' => 'array',
        'avg_speed' => 'float',
        'max_speed' => 'float',
        'avg_coolant_temp_c' => 'float',
        'max_coolant_temp_c' => 'float',
        'distance_km' => 'float',
        'fuel_used_liters' => 'float',
        'avg_fuel_consumption_l100km' => 'float',
        'eco_score' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelemetryReading extends Model
{
    protected $fillable = [
        'vehicle_id',
        'rpm',
        'speed',
        'coolant_temp_c',
        'battery_voltage',
        'engine_load',
        'throttle_position',
        'intake_temp_c',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'rpm' => 'float',
        'speed' => 'float',
        'coolant_temp_c' => 'float',
        'battery_voltage' => 'float',
        'engine_load' => 'float',
        'throttle_position' => 'float',
        'intake_temp_c' => 'float',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}

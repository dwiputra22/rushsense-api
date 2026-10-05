<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    protected $fillable = [
        'vehicle_id',
        'title',
        'category',
        'status',
        'notes',
        'performed_at',
        'odometer',
        'cost',
        'workshop',
        'next_odometer',
        'next_date',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'next_date' => 'datetime',
        'odometer' => 'integer',
        'next_odometer' => 'integer',
        'cost' => 'float',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}

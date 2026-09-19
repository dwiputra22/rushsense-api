<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDiagnosis extends Model
{
    protected $fillable = ['vehicle_id', 'input_context', 'explanation'];

    protected $casts = [
        'input_context' => 'array',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}

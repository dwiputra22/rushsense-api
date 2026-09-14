<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TelemetryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'rpm' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'coolant_temp_c' => 'nullable|numeric',
            'battery_voltage' => 'nullable|numeric',
            'engine_load' => 'nullable|numeric',
            'throttle_position' => 'nullable|numeric',
            'intake_temp_c' => 'nullable|numeric',
            'recorded_at' => 'required|date',
        ]);

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        $reading = $vehicle->telemetryReadings()->create($data);

        return response()->json($reading, 201);
    }

    public function index(Vehicle $vehicle)
    {
        return $vehicle->telemetryReadings()
            ->orderByDesc('recorded_at')
            ->paginate(50);
    }
}

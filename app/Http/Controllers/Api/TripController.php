<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function store(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after_or_equal:started_at',
            'avg_speed' => 'nullable|numeric',
            'max_speed' => 'nullable|numeric',
            'avg_coolant_temp_c' => 'nullable|numeric',
            'max_coolant_temp_c' => 'nullable|numeric',
            'distance_km' => 'nullable|numeric',
            'start_address' => 'nullable|string|max:255',
            'end_address' => 'nullable|string|max:255',
            'route' => 'nullable|array',
            'route.*.lat' => 'required_with:route|numeric',
            'route.*.lng' => 'required_with:route|numeric',
            'fuel_used_liters' => 'nullable|numeric|min:0',
            'avg_fuel_consumption_l100km' => 'nullable|numeric|min:0',
            'eco_score' => 'nullable|integer|min:0|max:100',
            'health_events' => 'nullable|array',
            'health_events.*.lat' => 'required_with:health_events|numeric',
            'health_events.*.lng' => 'required_with:health_events|numeric',
            'health_events.*.sensor_label' => 'required_with:health_events|string',
            'health_events.*.severity' => 'required_with:health_events|string',
        ]);

        $trip = $vehicle->trips()->create($data);

        return response()->json($trip, 201);
    }

    public function index(Vehicle $vehicle)
    {
        return $vehicle->trips()
            ->orderByDesc('started_at')
            ->get()
            ->makeHidden(['route', 'health_events']);
    }

    public function show(Vehicle $vehicle, $tripId)
    {
        return $vehicle->trips()->findOrFail($tripId);
    }
}

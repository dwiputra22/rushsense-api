<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function store(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'performed_at' => 'required|date',
            'odometer' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0|max:999999999999.99',
            'workshop' => 'nullable|string|max:255',
            'next_odometer' => 'nullable|integer|min:0',
            'next_date' => 'nullable|date',
        ]);

        foreach (['category', 'status', 'cost'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === null) {
                unset($data[$key]);
            }
        }

        $record = $vehicle->maintenanceRecords()->create($data);

        return response()->json($record, 201);
    }

    public function index(Vehicle $vehicle)
    {
        return $vehicle->maintenanceRecords()->orderByDesc('performed_at')->get();
    }
}

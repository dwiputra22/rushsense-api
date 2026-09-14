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
            'notes' => 'nullable|string',
            'performed_at' => 'required|date',
            'odometer' => 'nullable|integer|min:0',
        ]);

        $record = $vehicle->maintenanceRecords()->create($data);

        return response()->json($record, 201);
    }

    public function index(Vehicle $vehicle)
    {
        return $vehicle->maintenanceRecords()->orderByDesc('performed_at')->get();
    }
}

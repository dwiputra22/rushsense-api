<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        return Vehicle::latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vin' => 'nullable|string|size:17',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'nickname' => 'nullable|string|max:100',
            'engine_name' => 'nullable|string|max:100',
        ]);

        if (!empty($data['vin'])) {
            $vehicle = Vehicle::updateOrCreate(
                ['vin' => $data['vin']],
                $data,
            );
            return response()->json($vehicle, $vehicle->wasRecentlyCreated ? 201 : 200);
        }

        $vehicle = Vehicle::create($data);

        return response()->json($vehicle, 201);
    }

    public function show(Vehicle $vehicle)
    {
        return $vehicle;
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'vin' => 'nullable|string|size:17|unique:vehicles,vin,' . $vehicle->id,
            'brand' => 'sometimes|string|max:100',
            'model' => 'sometimes|string|max:100',
            'year' => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'nickname' => 'nullable|string|max:100',
            'engine_name' => 'nullable|string|max:100',
        ]);

        $vehicle->update($data);

        return $vehicle;
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return response()->noContent();
    }
}

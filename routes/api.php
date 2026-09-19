<?php

use App\Http\Controllers\Api\AiMechanicController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\TelemetryController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware('device.key')->group(function () {
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);

    Route::post('/telemetry', [TelemetryController::class, 'store']);
    Route::get('/vehicles/{vehicle}/telemetry', [TelemetryController::class, 'index']);

    Route::post('/vehicles/{vehicle}/trips', [TripController::class, 'store']);
    Route::get('/vehicles/{vehicle}/trips', [TripController::class, 'index']);
    Route::get('/vehicles/{vehicle}/trips/{tripId}', [TripController::class, 'show']);

    Route::post('/vehicles/{vehicle}/maintenance', [MaintenanceController::class, 'store']);
    Route::get('/vehicles/{vehicle}/maintenance', [MaintenanceController::class, 'index']);

    Route::post('/vehicles/{vehicle}/ai-mechanic', [AiMechanicController::class, 'analyze']);
    Route::get('/vehicles/{vehicle}/ai-mechanic', [AiMechanicController::class, 'history']);
});

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telemetry_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->decimal('rpm', 8, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('coolant_temp_c', 6, 2)->nullable();
            $table->decimal('battery_voltage', 5, 2)->nullable();
            $table->decimal('engine_load', 6, 2)->nullable();
            $table->decimal('throttle_position', 6, 2)->nullable();
            $table->decimal('intake_temp_c', 6, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['vehicle_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetry_readings');
    }
};

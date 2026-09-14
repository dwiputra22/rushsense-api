<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->decimal('avg_speed', 8, 2)->nullable();
            $table->decimal('max_speed', 8, 2)->nullable();
            $table->decimal('avg_coolant_temp_c', 6, 2)->nullable();
            $table->decimal('max_coolant_temp_c', 6, 2)->nullable();
            $table->decimal('distance_km', 8, 2)->default(0);
            $table->string('start_address')->nullable();
            $table->string('end_address')->nullable();
            $table->json('route')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};

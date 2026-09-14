<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Kolom BARU untuk fitur konsumsi BBM & eco-driving score. Angka ini
    // ESTIMASI dari kalkulasi MAF/fuel-rate PID di sisi Flutter, bukan
    // pengukuran flow meter BBM langsung - lihat komentar di
    // FuelEfficiencyCalculator (Flutter) untuk detail metodenya.
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('fuel_used_liters', 8, 3)->default(0)->after('distance_km');
            $table->decimal('avg_fuel_consumption_l100km', 6, 2)->default(0)->after('fuel_used_liters');
            $table->unsignedTinyInteger('eco_score')->default(100)->after('avg_fuel_consumption_l100km');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['fuel_used_liters', 'avg_fuel_consumption_l100km', 'eco_score']);
        });
    }
};

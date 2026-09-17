<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('vin');
        });
    }
    
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('vin', 17)->nullable()->unique()->after('id');
        });
    }
};

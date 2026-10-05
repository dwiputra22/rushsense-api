<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->string('category', 100)->default('Servis Berkala')->after('title');
            $table->string('status', 50)->default('Selesai')->after('category');
            $table->decimal('cost', 14, 2)->default(0)->after('odometer');
            $table->string('workshop')->nullable()->after('cost');
            $table->unsignedInteger('next_odometer')->nullable()->after('workshop');
            $table->timestamp('next_date')->nullable()->after('next_odometer');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'status',
                'cost',
                'workshop',
                'next_odometer',
                'next_date',
            ]);
        });
    }
};

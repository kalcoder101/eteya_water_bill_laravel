<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_location', function (Blueprint $table) {
            $table->string('customer_code', 50)->primary();
            $table->string('latitude_val', 30)->nullable();
            $table->string('longitude_val', 30)->nullable();
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_location');
    }
};

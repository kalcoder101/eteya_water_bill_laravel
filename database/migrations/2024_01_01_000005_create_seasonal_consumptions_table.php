<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasonal_consumptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('meter_reading_id', 50)->unique();
            $table->string('meter_serial', 50);
            $table->string('reading_date', 30)->nullable();
            $table->double('current_reading')->default(0);
            $table->string('collector', 100)->nullable();
            $table->string('reading_year', 10)->nullable();
            $table->string('reading_month', 30)->nullable()->comment('Afaan Oromo month name');
            $table->string('sync_status', 20)->default('New');
            $table->string('reading_branch', 50)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('meter_serial')
                  ->references('meter_serial')
                  ->on('active_customers')
                  ->onDelete('cascade');

            $table->index('meter_serial', 'idx_sc_meter_serial');
            $table->index(['reading_year', 'reading_month'], 'idx_sc_year_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasonal_consumptions');
    }
};

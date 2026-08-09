<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_printing', function (Blueprint $table) {
            $table->string('bill_print_id', 50)->primary();
            $table->string('meter_serial', 50);
            $table->string('bill_year', 10)->nullable();
            $table->string('bill_month', 30)->nullable();
            $table->string('print_date', 30)->nullable();
            $table->string('print_person', 100)->nullable();
            $table->string('bill_number', 50)->nullable();
            $table->string('window_number', 20)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index('meter_serial', 'idx_bp_meter_serial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_printing');
    }
};

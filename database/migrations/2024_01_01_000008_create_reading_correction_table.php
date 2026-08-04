<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_correction', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer_code', 50);
            $table->string('reading_year', 10)->nullable();
            $table->string('reading_month', 30)->nullable();
            $table->string('sending_department', 150)->nullable()
                  ->comment('Full name of submitter (originally)');
            $table->string('complain_date_time', 30)->nullable();
            $table->string('correction_status', 20)->default('Pending')
                  ->comment('Pending | Approved | Rejected');
            $table->string('new_reading', 30)->default('NotInserted');
            $table->string('approved_name', 150)->default('Pending');
            $table->string('sync_status', 20)->default('New');
            $table->dateTime('created_at')->useCurrent();

            $table->index('customer_code', 'idx_customer_code');
            $table->index('correction_status', 'idx_status');
            $table->index('complain_date_time', 'idx_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_correction');
    }
};

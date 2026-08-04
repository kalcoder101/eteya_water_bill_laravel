<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_auditing', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_date', 30)->nullable();
            $table->string('log_reason', 500)->nullable();
            $table->string('done_by', 150)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index('done_by', 'idx_done_by');
            $table->index('log_date', 'idx_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_auditing');
    }
};

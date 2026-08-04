<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_period', function (Blueprint $table) {
            $table->increments('id');
            $table->string('work_year', 10)->nullable();
            $table->string('work_month', 30)->nullable();
            $table->boolean('is_active')->default(false);
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_period');
    }
};

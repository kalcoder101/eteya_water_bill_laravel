<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_account', function (Blueprint $table) {
            $table->string('user_id', 50)->primary();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable()->comment('middle name in original app');
            $table->string('phone_number', 30)->nullable();
            $table->string('email_id', 150)->nullable();
            $table->string('job_role', 50)
                  ->comment('Bill Reader | Secretary | Manager | System Admin | Customer Service');
            $table->string('user_name', 50)->unique();
            $table->string('user_password', 255);
            $table->binary('photo')->nullable();
            $table->rememberToken();
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_account');
    }
};

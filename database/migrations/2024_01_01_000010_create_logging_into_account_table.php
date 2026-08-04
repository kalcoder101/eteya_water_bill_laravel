<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logging_into_account', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_date', 30)->nullable();
            $table->string('user', 150)->nullable();
            $table->string('task', 100)->nullable()
                  ->comment('Logging to System | Logout to System | Closed the system by Menu');
            $table->dateTime('created_at')->useCurrent();

            $table->index('user', 'idx_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logging_into_account');
    }
};

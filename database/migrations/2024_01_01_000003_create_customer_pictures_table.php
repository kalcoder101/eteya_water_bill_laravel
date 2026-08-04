<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_pictures', function (Blueprint $table) {
            $table->string('phone_number', 30)->primary();
            $table->binary('customer_photo')->nullable();
            $table->binary('id_f_card')->nullable()->comment('ID card front');
            $table->binary('id_b_card')->nullable()->comment('ID card back');
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_pictures');
    }
};

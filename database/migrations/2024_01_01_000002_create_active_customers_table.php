<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_customers', function (Blueprint $table) {
            $table->string('meter_serial', 50)->primary()->comment('Customer code (primary identifier)');
            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('kebele', 50)->nullable()->comment('Sub-district');
            $table->string('sold_date', 30)->nullable()->comment('Date customer was registered (string)');
            $table->integer('meter_num')->default(0)->comment('Numeric meter number');
            $table->string('meter_size', 20)->nullable()->comment('1/2", 3/4", 1", 1 and 1/2", 2"');
            $table->string('customer_type', 60)->nullable()
                  ->comment('Dhunfaa | Daldaltoota fi Industry | Waajjira Motummaa | Waajjira Miti-Motummaa | Boonoo');
            $table->string('bill_num', 50)->nullable()->comment('Serial number printed on bill');
            $table->string('phone_number', 30)->nullable();
            $table->decimal('start_value', 18, 4)->default(0)->comment('Initial meter reading');
            $table->string('payment_way', 20)->nullable()->comment('BANK | NON_BANK');
            $table->string('customer_branch', 50)->nullable()->comment('Town / branch e.g. Eteya');
            $table->string('customer_status', 20)->default('Active')
                  ->comment('Active | DC | Updated | Deleted');
            $table->string('sync_status', 20)->default('New')->comment('New | Synced | Updated');
            $table->string('reader_block', 50)->nullable()->comment('Reader block / route group');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable()
                  ->useCurrentOnUpdate();

            $table->index('first_name', 'idx_first_name');
            $table->index('kebele', 'idx_kebele');
            $table->index('customer_status', 'idx_status');
            $table->index('phone_number', 'idx_phone');
            $table->index('customer_branch', 'idx_branch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_customers');
    }
};

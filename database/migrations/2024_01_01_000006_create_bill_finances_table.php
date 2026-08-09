<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_finances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bill_finance_id', 50)->unique();
            $table->string('meter_serial', 50);
            $table->double('meter_price')->default(0)->comment('Meter rent cost');
            $table->double('service_price')->default(0)->comment('Service cost');
            $table->double('consumption')->default(0)->comment('Water consumed (m3)');
            $table->double('penalty_cost')->default(0);
            $table->double('community_cost')->default(0);
            $table->decimal('total_monthly_cost', 18, 4)->default(0);
            $table->double('meter_price_d')->default(0)->comment('Discounted / decimal variant');
            $table->double('service_price_d')->default(0);
            $table->double('consumption_d')->default(0);
            $table->double('penalty_cost_d')->default(0);
            $table->double('community_cost_d')->default(0);
            $table->decimal('total_monthly_cost_d', 18, 4)->default(0);
            $table->double('consumption_cost')->default(0)
                  ->comment('Water bill cost (consumption * tariff)');
            $table->decimal('total_aggregation_cost', 18, 4)->default(0);
            $table->decimal('deposited_cost', 18, 4)->default(0)
                  ->comment('Deposit water bill (interest)');
            $table->string('payment_status', 30)->default('Unpaid')->comment('Paid | Unpaid');
            $table->string('bill_year', 10)->nullable();
            $table->string('bill_month', 30)->nullable()->comment('Afaan Oromo month');
            $table->decimal('state_price', 18, 4)->default(0)->comment('Water Fund cost');
            $table->decimal('deposit_fund', 18, 4)->default(0);
            $table->double('deposit_consumption_cost')->default(0);
            $table->string('calculate_status', 20)->default('Pending')
                  ->comment('Pending | Calculated');
            $table->string('bill_period', 30)->nullable();
            $table->decimal('vat_price', 18, 4)->default(0);
            $table->decimal('vat_price_d', 18, 4)->default(0);
            $table->string('full_name', 200)->nullable();
            $table->string('kebele', 50)->nullable();
            $table->integer('meter_num')->default(0);
            $table->string('customer_type', 60)->nullable();
            $table->string('print_date', 30)->nullable();
            $table->string('print_person', 100)->nullable();
            $table->string('bill_number', 50)->nullable();
            $table->string('window_number', 20)->nullable();
            $table->string('customer_branch', 50)->nullable();
            $table->double('dc_price')->default(0);
            $table->double('dc_price_d')->default(0);
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('meter_serial')
                  ->references('meter_serial')
                  ->on('active_customers')
                  ->onDelete('cascade');

            $table->index('meter_serial', 'idx_bf_meter_serial');
            $table->index(['bill_year', 'bill_month'], 'idx_bf_year_month');
            $table->index('payment_status', 'idx_payment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_finances');
    }
};

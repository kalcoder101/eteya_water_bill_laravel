<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillFinance extends Model
{
    protected $table = 'bill_finances';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'bill_finance_id', 'meter_serial', 'meter_price', 'service_price',
        'consumption', 'penalty_cost', 'community_cost', 'total_monthly_cost',
        'meter_price_d', 'service_price_d', 'consumption_d', 'penalty_cost_d',
        'community_cost_d', 'total_monthly_cost_d', 'consumption_cost',
        'total_aggregation_cost', 'deposited_cost', 'payment_status',
        'bill_year', 'bill_month', 'state_price', 'deposit_fund',
        'deposit_consumption_cost', 'calculate_status', 'bill_period',
        'vat_price', 'vat_price_d', 'full_name', 'kebele', 'meter_num',
        'customer_type', 'print_date', 'print_person', 'bill_number',
        'window_number', 'customer_branch', 'dc_price', 'dc_price_d',
    ];

    protected $casts = [
        'meter_price'         => 'float',
        'service_price'       => 'float',
        'consumption'         => 'float',
        'penalty_cost'        => 'float',
        'community_cost'      => 'float',
        'meter_price_d'       => 'float',
        'service_price_d'     => 'float',
        'consumption_d'       => 'float',
        'penalty_cost_d'      => 'float',
        'community_cost_d'    => 'float',
        'consumption_cost'    => 'float',
        'deposit_consumption_cost' => 'float',
        'dc_price'            => 'float',
        'dc_price_d'          => 'float',
        'meter_num'           => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(ActiveCustomer::class, 'meter_serial', 'meter_serial');
    }

    public function seasonalConsumption()
    {
        return $this->hasOne(SeasonalConsumption::class, 'meter_serial', 'meter_serial')
            ->whereColumn('reading_year', 'bill_year')
            ->whereColumn('reading_month', 'bill_month');
    }
}

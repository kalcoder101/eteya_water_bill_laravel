<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Master record of every water meter customer.
 *
 * @property string $meter_serial
 * @property string|null $first_name
 * @property string|null $middle_name
 * @property string|null $last_name
 * @property string|null $kebele
 * @property string|null $sold_date
 * @property int $meter_num
 * @property string|null $meter_size
 * @property string|null $customer_type
 * @property string|null $bill_num
 * @property string|null $phone_number
 * @property float $start_value
 * @property string|null $payment_way
 * @property string|null $customer_branch
 * @property string $customer_status
 * @property string $sync_status
 * @property string|null $reader_block
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ActiveCustomer extends Model
{
    protected $table = 'active_customers';

    protected $primaryKey = 'meter_serial';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = [
        'meter_serial', 'first_name', 'middle_name', 'last_name', 'kebele',
        'sold_date', 'meter_num', 'meter_size', 'customer_type', 'bill_num',
        'phone_number', 'start_value', 'payment_way', 'customer_branch',
        'customer_status', 'sync_status', 'reader_block',
    ];

    protected $casts = [
        'start_value' => 'float',
        'meter_num'   => 'integer',
    ];

    public function pictures()
    {
        return $this->hasOne(CustomerPicture::class, 'phone_number', 'phone_number');
    }

    public function meterLocation()
    {
        return $this->hasOne(MeterLocation::class, 'customer_code', 'meter_serial');
    }

    public function seasonalConsumptions()
    {
        return $this->hasMany(SeasonalConsumption::class, 'meter_serial', 'meter_serial');
    }

    public function billFinances()
    {
        return $this->hasMany(BillFinance::class, 'meter_serial', 'meter_serial');
    }

    public function readingCorrections()
    {
        return $this->hasMany(ReadingCorrection::class, 'customer_code', 'meter_serial');
    }

    public function getFullNameAttribute(): string
    {
        return trim(
            ($this->first_name ?? '').' '.
            ($this->middle_name ?? '').' '.
            ($this->last_name ?? '')
        );
    }
}

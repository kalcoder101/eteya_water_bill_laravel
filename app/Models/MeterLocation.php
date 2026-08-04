<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeterLocation extends Model
{
    protected $table = 'meter_location';

    protected $primaryKey = 'customer_code';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['customer_code', 'latitude_val', 'longitude_val'];

    public function customer()
    {
        return $this->belongsTo(ActiveCustomer::class, 'customer_code', 'meter_serial');
    }
}

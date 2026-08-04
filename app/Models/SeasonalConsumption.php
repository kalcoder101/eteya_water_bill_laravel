<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalConsumption extends Model
{
    protected $table = 'seasonal_consumptions';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'meter_reading_id', 'meter_serial', 'reading_date', 'current_reading',
        'collector', 'reading_year', 'reading_month', 'sync_status',
        'reading_branch',
    ];

    protected $casts = [
        'current_reading' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(ActiveCustomer::class, 'meter_serial', 'meter_serial');
    }
}

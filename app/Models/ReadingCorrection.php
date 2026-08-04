<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingCorrection extends Model
{
    protected $table = 'reading_correction';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'customer_code', 'reading_year', 'reading_month',
        'sending_department', 'complain_date_time', 'correction_status',
        'new_reading', 'approved_name', 'sync_status',
    ];
}

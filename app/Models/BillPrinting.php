<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPrinting extends Model
{
    protected $table = 'bill_printing';

    protected $primaryKey = 'bill_print_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'bill_print_id', 'meter_serial', 'bill_year', 'bill_month',
        'print_date', 'print_person', 'bill_number', 'window_number',
    ];
}

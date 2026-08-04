<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingPeriod extends Model
{
    protected $table = 'working_period';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = ['work_year', 'work_month', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

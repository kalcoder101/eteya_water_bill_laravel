<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationAuditing extends Model
{
    protected $table = 'operation_auditing';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = ['log_date', 'log_reason', 'done_by'];
}

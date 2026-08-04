<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoggingIntoAccount extends Model
{
    protected $table = 'logging_into_account';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = ['log_date', 'user', 'task'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobRole extends Model
{
    protected $table = 'job_roles';

    protected $primaryKey = 'role_name';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['role_name', 'display_name', 'color_badge', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}

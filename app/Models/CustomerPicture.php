<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPicture extends Model
{
    protected $table = 'customer_pictures';

    protected $primaryKey = 'phone_number';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'phone_number', 'customer_photo', 'id_f_card', 'id_b_card',
    ];
}

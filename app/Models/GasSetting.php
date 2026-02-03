<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GasSetting extends Model
{
     protected $fillable = [
        'gas_normal',
        'gas_darurat'
    ];
}

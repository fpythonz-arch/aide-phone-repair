<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairCounter extends Model
{
    protected $fillable = [
        'year',
        'last_number',
    ];
}

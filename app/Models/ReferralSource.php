<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralSource extends Model
{
    //
    protected $fillable = [
        'name',
        'type',
        'contact_info',
    ];
}

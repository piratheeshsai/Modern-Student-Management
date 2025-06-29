<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    //
    protected $fillable = [
        'name',
        'location',
         // optional, if you want to track status
        'created_by', // optional, if you want to track who created the branch
    ];
}

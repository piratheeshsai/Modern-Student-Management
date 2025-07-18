<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    protected $fillable = [
        'enrollment_id',
        'amount',
        'due_date',
        'payment_type',
        'status',
    ];

    // Relationship to Enrollment
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Additional methods or relationships can be added here
}

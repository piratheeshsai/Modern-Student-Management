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
        'paid_date',
        'notes',
        'refund_amount',
        'paid_amount',

    ];

    // Relationship to Enrollment
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }


}

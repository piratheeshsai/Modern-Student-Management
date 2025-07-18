<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'status',
        'course_id',
        'branch_id',
        'referral_source_id',
        'enrollment_date',
        'discount_amount',
        'total_amount',
        'discount_value',
        'discount_type',// Assuming this is a new field for the discount type
        'payment_type',
        'note',
        'created_by',

    ];

    // Relationship to Student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relationship to Course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Relationship to Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // If you have a ReferralSource model and column
    public function referralSource()
    {
        return $this->belongsTo(ReferralSource::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'enrollment_id');
    }

}

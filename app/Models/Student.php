<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_no',
        'title',
        'first_name',
        'last_name',
        'id_type',
        'id_no',
        'dob',
        'address',
        'school_name',
        'company_name',
        'course_id',
        'branch_id',
        'referral_source_id',
        'email',
        'mobile',
        'phone_residence',
        'phone_whatsapp',
        'qualification',
        'is_approved',
        'is_active',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'dob' => 'date',
        'is_approved' => 'boolean',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Add these relationship methods
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function referralSource()
    {
        return $this->belongsTo(ReferralSource::class);
    }

    // If you have an approved_by field
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

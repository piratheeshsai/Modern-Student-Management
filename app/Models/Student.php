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
        'is_verified',
        'is_active',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'dob' => 'date',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
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
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
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
        'is_approved',
        'is_active',
        'approved_by',
        'approved_at',
        'qualification'
    ];
}

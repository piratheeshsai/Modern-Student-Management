<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example: create 10 random students
        Student::factory()->count(10)->create([
            'course_id' => 1,
            'branch_id' => 1,
            'referral_source_id' => 1,
        ]);

        // Example: create a specific student
        Student::create([
            'student_no' => 'S0001',
            'title' => 'Mr.',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'id_type' => 'NIC',
            'id_no' => '123456789V',
            'dob' => '2000-01-01',
            'address' => '123 Main St, City, Country',
            'school_name' => 'Central College',
            'company_name' => null,
            'course_id' => 1,
            'branch_id' => 1,
            'referral_source_id' => 1,
            'email' => 'john.doe@example.com',
            'mobile' => '0757480809',
            'phone_residence' => null,
            'phone_whatsapp' => '0771234567',
            'is_verified' => false,
            'is_active' => true,
            'verified_by' => null,
            'verified_at' => null,
            'qualification' => 'A/L',
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_no' => 'S' . $this->faker->unique()->numerify('####'),
            'title' => $this->faker->randomElement(['Mr.', 'Ms.', 'Mrs.']),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'id_type' => 'NIC',
            'id_no' => $this->faker->unique()->numerify('#########V'),
            'dob' => $this->faker->date(),
            'address' => $this->faker->address,
            'school_name' => $this->faker->company,
            'company_name' => null,
            'course_id' => 1, // or random from your courses table
            'branch_id' => 1,
            'referral_source_id' => 1,
            'email' => $this->faker->unique()->safeEmail,
            'mobile' => $this->faker->phoneNumber,
            'phone_residence' => null,
            'phone_whatsapp' => $this->faker->phoneNumber,
            'is_verified' => false,
            'is_active' => true,
            'verified_by' => null,
            'verified_at' => null,
            'qualification' => 'A/L',
        ];
    }
}

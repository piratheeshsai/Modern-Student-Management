<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Course::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Sample Course',
                'duration' => '6 months',
                'fees' => 50000.00,
                'description' => 'This is a sample course for seeding.',
            ]
        );
    }
}

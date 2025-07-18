<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Example: create some branches
        \App\Models\Branch::create([
            'name' => 'Main Branch',
            'location' => '123 Main St, City, Country',
        ]);


    }
}

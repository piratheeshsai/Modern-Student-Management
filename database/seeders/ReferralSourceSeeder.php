<?php

namespace Database\Seeders;

use App\Models\ReferralSource;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReferralSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Example: create some referral sources
        ReferralSource::create(['name' => 'Online Search',
        'type' => 'Online',
        'contact_info' => 'info@onlineseach.com',
]);


    }
}

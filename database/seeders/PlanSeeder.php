<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        Plan::create([
            'slug' => 'monthly',
            'price' => 12.00,
            'duration_in_days' => 30,
        ]);
        
        Plan::create([
            'slug' => 'yearly',
            'price' => 99.99,
            'duration_in_days' => 365,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        Service::updateOrCreate(
            ['name' => 'Direction des Systèmes d’Information et de la Digitalisation'],
            ['is_active' => true],
        );
    }
}

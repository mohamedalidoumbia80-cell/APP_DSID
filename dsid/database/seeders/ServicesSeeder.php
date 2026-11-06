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

        foreach ([
            [
                'name' => 'Service Maintenance et Support (SMS)',
                'description' => 'Maintenance des équipements et assistance aux utilisateurs.',
            ],
            [
                'name' => 'Service Réseau et Sécurité (SRS)',
                'description' => 'Gestion des réseaux informatiques et de la sécurité des systèmes.',
            ],
            [
                'name' => 'Service Production et Revue documentaire',
                'description' => 'Production et revue des documents de la DSID.',
            ],
        ] as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                ['description' => $service['description'], 'is_active' => true],
            );
        }
    }
}

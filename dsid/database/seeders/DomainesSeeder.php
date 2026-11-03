<?php

namespace Database\Seeders;

use App\Models\Domaine;
use Illuminate\Database\Seeder;

class DomainesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Gestion informatique et documentaire',
            'Planification des systèmes d’information',
            'Suivi du SDSI',
            'Projets de digitalisation',
            'Sécurité des systèmes d’information',
        ] as $name) {
            Domaine::updateOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}

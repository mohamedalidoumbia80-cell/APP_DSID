<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'administrateur' => 'Administrateur',
            'directeur' => 'Directeur',
            'chef_service' => 'Chef de service',
            'agent' => 'Agent',
            'lecteur' => 'Lecteur',
        ] as $name => $label) {
            Role::updateOrCreate(['name' => $name], ['label' => $label]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $service = Service::firstOrCreate(['name' => 'Direction des Systèmes d’Information et de la Digitalisation']);
        $password = (string) env('DSID_SEED_PASSWORD', 'ChangeMe123!');

        foreach ([
            ['Administrateur', 'DSID', 'admin@dsid.test', 'administrateur'],
            ['Directeur', 'DSID', 'directeur@dsid.test', 'directeur'],
            ['Chef', 'Service', 'chef@dsid.test', 'chef_service'],
            ['Agent', 'DSID', 'agent@dsid.test', 'agent'],
            ['Lecteur', 'DSID', 'lecteur@dsid.test', 'lecteur'],
        ] as [$firstName, $lastName, $email, $roleName]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'password' => $password,
                    'role_id' => Role::where('name', $roleName)->value('id'),
                    'service_id' => $service->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\AxeSdsi;
use Illuminate\Database\Seeder;

class AxeSdsiSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['title' => 'Modernisation du SI', 'description' => 'Projets visant la modernisation et la transformation numérique du système d\'information.'],
            ['title' => 'Sécurisation et gouvernance', 'description' => 'Renforcement de la sécurité des systèmes et mise en place des politiques de gouvernance.'],
            ['title' => 'Intégration et interopérabilité', 'description' => 'Amélioration de l\'échange de données entre les systèmes internes et partenaires.'],
            ['title' => 'Infrastructure et cloud', 'description' => 'Développement de l\'infrastructure numérique et migration vers des solutions cloud.'],
            ['title' => 'Usages numériques et formation', 'description' => 'Développement des usages numériques et accompagnement des agents.'],
        ] as $axe) {
            AxeSdsi::updateOrCreate(['title' => $axe['title']], ['description' => $axe['description'], 'is_active' => true]);
        }
    }
}

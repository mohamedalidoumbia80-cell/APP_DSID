<?php

namespace App\Enums;

enum ActivityType: string
{
    case PROJET = 'projet';
    case ACTIVITE_COURANTE = 'activite_courante';

    public function label(): string
    {
        return $this === self::PROJET ? 'Projet' : 'Activité courante';
    }
}

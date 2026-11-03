<?php

namespace App\Enums;

enum ActivityStatus: string
{
    case A_DEMARRER = 'a_demarrer';
    case EN_COURS = 'en_cours';
    case EN_ATTENTE = 'en_attente';
    case TERMINE = 'termine';
    case ANNULE = 'annule';

    public function label(): string
    {
        return match ($this) {
            self::A_DEMARRER => 'À démarrer',
            self::EN_COURS => 'En cours',
            self::EN_ATTENTE => 'En attente',
            self::TERMINE => 'Terminé',
            self::ANNULE => 'Annulé',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

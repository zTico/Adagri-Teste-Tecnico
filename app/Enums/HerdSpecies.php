<?php

namespace App\Enums;

enum HerdSpecies: string
{
    case SWINE = 'swine';
    case GOATS = 'goats';
    case CATTLE = 'cattle';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(
            fn (self $species): array => [
                'label' => str($species->value)->headline()->value(),
                'value' => $species->value,
            ],
            self::cases(),
        );
    }
}

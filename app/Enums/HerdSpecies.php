<?php

namespace App\Enums;

enum HerdSpecies: string
{
    case SWINE = 'swine';
    case GOATS = 'goats';
    case CATTLE = 'cattle';

    public function label(): string
    {
        return match ($this) {
            self::SWINE => 'Suinos',
            self::GOATS => 'Caprinos',
            self::CATTLE => 'Bovinos',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(
            fn (self $species): array => [
                'label' => $species->label(),
                'value' => $species->value,
            ],
            self::cases(),
        );
    }
}

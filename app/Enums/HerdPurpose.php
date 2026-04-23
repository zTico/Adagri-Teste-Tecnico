<?php

namespace App\Enums;

enum HerdPurpose: string
{
    case BREEDING = 'breeding';
    case MEAT = 'meat';
    case MILK = 'milk';
    case MIXED = 'mixed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(
            fn (self $purpose): array => [
                'label' => str($purpose->value)->headline()->value(),
                'value' => $purpose->value,
            ],
            self::cases(),
        );
    }
}

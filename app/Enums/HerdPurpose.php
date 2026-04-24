<?php

namespace App\Enums;

enum HerdPurpose: string
{
    case BREEDING = 'breeding';
    case MEAT = 'meat';
    case MILK = 'milk';
    case MIXED = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::BREEDING => 'Criacao',
            self::MEAT => 'Corte',
            self::MILK => 'Leite',
            self::MIXED => 'Misto',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(
            fn (self $purpose): array => [
                'label' => $purpose->label(),
                'value' => $purpose->value,
            ],
            self::cases(),
        );
    }
}

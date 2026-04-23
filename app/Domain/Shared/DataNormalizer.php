<?php

namespace App\Domain\Shared;

class DataNormalizer
{
    public static function digitsOnly(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        return $digits === '' ? null : $digits;
    }

    public static function normalizeCpfCnpj(?string $value): ?string
    {
        return self::digitsOnly($value);
    }

    public static function normalizePhone(?string $value): ?string
    {
        return self::digitsOnly($value);
    }

    public static function normalizePostalCode(?string $value): ?string
    {
        return self::digitsOnly($value);
    }

    public static function normalizeState(?string $value): ?string
    {
        return $value === null ? null : str($value)->upper()->limit(2, '')->value();
    }
}

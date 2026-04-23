<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfCnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if (! $digits || ! in_array(strlen($digits), [11, 14], true)) {
            $fail('O campo :attribute deve conter um CPF ou CNPJ valido.');

            return;
        }

        if (preg_match('/^(\d)\1+$/', $digits) === 1) {
            $fail('O campo :attribute deve conter um CPF ou CNPJ valido.');

            return;
        }

        $isValid = strlen($digits) === 11
            ? $this->isValidCpf($digits)
            : $this->isValidCnpj($digits);

        if (! $isValid) {
            $fail('O campo :attribute deve conter um CPF ou CNPJ valido.');
        }
    }

    private function isValidCpf(string $cpf): bool
    {
        for ($t = 9; $t < 11; $t++) {
            $sum = 0;

            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }

            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    private function isValidCnpj(string $cnpj): bool
    {
        $firstWeights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $secondWeights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $firstDigit = $this->calculateCnpjDigit(substr($cnpj, 0, 12), $firstWeights);
        $secondDigit = $this->calculateCnpjDigit(substr($cnpj, 0, 13), $secondWeights);

        return $cnpj[12] === (string) $firstDigit
            && $cnpj[13] === (string) $secondDigit;
    }

    private function calculateCnpjDigit(string $base, array $weights): int
    {
        $sum = 0;

        foreach (str_split($base) as $index => $digit) {
            $sum += (int) $digit * $weights[$index];
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}

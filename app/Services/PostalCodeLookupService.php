<?php

namespace App\Services;

use App\Support\DataNormalizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PostalCodeLookupService
{
    /**
     * @return array<string, string|null>
     */
    public function lookup(string $postalCode): array
    {
        $normalizedPostalCode = DataNormalizer::normalizePostalCode($postalCode);

        if ($normalizedPostalCode === null || strlen($normalizedPostalCode) !== 8) {
            throw ValidationException::withMessages([
                'postal_code' => ['The postal code must contain 8 digits.'],
            ]);
        }

        $response = Http::timeout(5)
            ->acceptJson()
            ->get("https://viacep.com.br/ws/{$normalizedPostalCode}/json/");

        if ($response->failed() || $response->json('erro')) {
            throw ValidationException::withMessages([
                'postal_code' => ['Unable to locate this postal code.'],
            ]);
        }

        return [
            'postal_code' => $normalizedPostalCode,
            'street' => $response->json('logradouro'),
            'complement' => $response->json('complemento'),
            'district' => $response->json('bairro'),
            'city' => $response->json('localidade'),
            'state' => DataNormalizer::normalizeState($response->json('uf')),
        ];
    }
}

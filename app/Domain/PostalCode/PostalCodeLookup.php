<?php

namespace App\Domain\PostalCode;

use App\Domain\Shared\DataNormalizer;
use Illuminate\Validation\ValidationException;

class PostalCodeLookup
{
    public function __construct(
        private readonly PostalCodeGatewayInterface $gateway,
    ) {
    }

    public function lookup(string $postalCode): array
    {
        $normalizedPostalCode = DataNormalizer::normalizePostalCode($postalCode);

        if ($normalizedPostalCode === null || strlen($normalizedPostalCode) !== 8) {
            throw ValidationException::withMessages([
                'postal_code' => ['O CEP deve conter 8 digitos.'],
            ]);
        }

        $payload = $this->gateway->lookup($normalizedPostalCode);

        if (($payload['erro'] ?? false) === true) {
            throw ValidationException::withMessages([
                'postal_code' => ['Nao foi possivel localizar este CEP.'],
            ]);
        }

        return [
            'postal_code' => $normalizedPostalCode,
            'street' => $payload['logradouro'] ?? null,
            'complement' => $payload['complemento'] ?? null,
            'district' => $payload['bairro'] ?? null,
            'city' => $payload['localidade'] ?? null,
            'state' => DataNormalizer::normalizeState($payload['uf'] ?? null),
        ];
    }
}

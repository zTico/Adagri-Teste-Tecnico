<?php

namespace App\Infra\PostalCode;

use App\Domain\PostalCode\PostalCodeGatewayInterface;
use Illuminate\Support\Facades\Http;

class ViaCepGateway implements PostalCodeGatewayInterface
{
    public function lookup(string $postalCode): array
    {
        $response = Http::timeout(5)
            ->acceptJson()
            ->get("https://viacep.com.br/ws/{$postalCode}/json/");

        if ($response->failed()) {
            return ['erro' => true];
        }

        $payload = $response->json();

        return is_array($payload) ? $payload : ['erro' => true];
    }
}

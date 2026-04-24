<?php

namespace App\Infra\Location;

use App\Domain\Shared\DataNormalizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class IbgeLocationLookup
{
    public function states(): array
    {
        return Cache::remember('ibge:states', now()->addDays(30), function (): array {
            return collect($this->fetch('/estados?orderBy=nome'))
                ->map(fn (array $state): array => [
                    'label' => $state['nome'] ?? '',
                    'value' => $state['sigla'] ?? '',
                ])
                ->filter(fn (array $state): bool => $state['label'] !== '' && $state['value'] !== '')
                ->values()
                ->all()
            ;
        });
    }

    public function cities(string $state): array
    {
        $normalizedState = DataNormalizer::normalizeState($state);

        if ($normalizedState === null || strlen($normalizedState) !== 2) {
            throw ValidationException::withMessages([
                'state' => ['O estado deve conter 2 letras.'],
            ]);
        }

        return Cache::remember("ibge:cities:{$normalizedState}", now()->addDays(30), function () use ($normalizedState): array {
            return collect($this->fetch("/estados/{$normalizedState}/municipios?orderBy=nome"))
                ->map(fn (array $city): array => [
                    'label' => $city['nome'] ?? '',
                    'value' => $city['nome'] ?? '',
                ])
                ->filter(fn (array $city): bool => $city['label'] !== '' && $city['value'] !== '')
                ->values()
                ->all()
            ;
        });
    }

    private function fetch(string $path): array
    {
        $response = Http::timeout(8)
            ->acceptJson()
            ->get("https://servicodados.ibge.gov.br/api/v1/localidades{$path}")
        ;

        if ($response->failed()) {
            return [];
        }

        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }
}

<?php

namespace App\Domain\RuralProducers;

class RuralProducerFilters
{
    public function __construct(private readonly array $filters)
    {
    }

    public function perPage(int $default = 10): int
    {
        return (int) ($this->filters['per_page'] ?? $default);
    }

    public function search(): ?string
    {
        return $this->string('search');
    }

    public function city(): ?string
    {
        return $this->string('city');
    }

    public function state(): ?string
    {
        return $this->string('state');
    }

    private function string(string $key): ?string
    {
        $value = $this->filters[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}

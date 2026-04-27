<?php

namespace App\Domain\Herds;

class HerdFilters
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

    public function species(): ?string
    {
        return $this->string('species');
    }

    public function purpose(): ?string
    {
        return $this->string('purpose');
    }

    public function farmId(): ?string
    {
        return $this->string('farm_id');
    }

    public function ruralProducerId(): ?string
    {
        return $this->string('rural_producer_id');
    }

    private function string(string $key): ?string
    {
        $value = $this->filters[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}

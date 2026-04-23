<?php

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

class HerdFilters
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(private readonly array $filters)
    {
    }

    public function apply(Builder $query): Builder
    {
        return $query
            ->when(
                $this->filters['search'] ?? null,
                fn (Builder $builder, string $search) => $builder->whereHas('farm', function (Builder $farmQuery) use ($search): void {
                    $farmQuery->where('name', 'like', "%{$search}%");
                })
            )
            ->when(
                $this->filters['species'] ?? null,
                fn (Builder $builder, string $species) => $builder->where('species', $species)
            )
            ->when(
                $this->filters['purpose'] ?? null,
                fn (Builder $builder, string $purpose) => $builder->where('purpose', $purpose)
            )
            ->when(
                $this->filters['farm_id'] ?? null,
                fn (Builder $builder, int $farmId) => $builder->where('farm_id', $farmId)
            )
            ->when(
                $this->filters['rural_producer_id'] ?? null,
                fn (Builder $builder, int $producerId) => $builder->whereHas('farm', function (Builder $farmQuery) use ($producerId): void {
                    $farmQuery->where('rural_producer_id', $producerId);
                })
            );
    }
}

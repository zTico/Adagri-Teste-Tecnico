<?php

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

class RuralProducerFilters
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
                fn (Builder $builder, string $search) => $builder->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('cpf_cnpj', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
            )
            ->when(
                $this->filters['city'] ?? null,
                fn (Builder $builder, string $city) => $builder->where('city', 'like', "%{$city}%")
            )
            ->when(
                $this->filters['state'] ?? null,
                fn (Builder $builder, string $state) => $builder->where('state', $state)
            );
    }
}

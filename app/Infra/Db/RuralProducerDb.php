<?php

namespace App\Infra\Db;

use App\Domain\Shared\DataNormalizer;
use App\Domain\RuralProducers\RuralProducerFilters;
use App\Models\RuralProducer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class RuralProducerDb
{
    public function paginate(RuralProducerFilters $filters): LengthAwarePaginator
    {
        $query = RuralProducer::query()
            ->withCount('farms')
            ->latest();

        $this->applyFilters($query, $filters);

        return $query
            ->paginate($filters->perPage())
            ->withQueryString();
    }

    public function create(array $data): RuralProducer
    {
        return RuralProducer::query()->create($data);
    }

    public function update(RuralProducer $ruralProducer, array $data): RuralProducer
    {
        $ruralProducer->update($data);

        return $ruralProducer->refresh();
    }

    public function findForResource(int $producerId): RuralProducer
    {
        return RuralProducer::query()
            ->with(['farms.herds'])
            ->withCount('farms')
            ->findOrFail($producerId);
    }

    public function delete(RuralProducer $ruralProducer): void
    {
        $ruralProducer->delete();
    }

    private function applyFilters(Builder $query, RuralProducerFilters $filters): void
    {
        $query
            ->when(
                $filters->search(),
                fn (Builder $builder, string $search) => $builder->where(function (Builder $nested) use ($search): void {
                    $documentSearch = DataNormalizer::digitsOnly($search);

                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('cpf_cnpj', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");

                    if ($documentSearch !== null) {
                        $nested->orWhere('cpf_cnpj', 'like', "%{$documentSearch}%");
                    }
                })
            )
            ->when(
                $filters->city(),
                fn (Builder $builder, string $city) => $builder->where('city', 'like', "%{$city}%")
            )
            ->when(
                $filters->state(),
                fn (Builder $builder, string $state) => $builder->where('state', $state)
            );
    }
}

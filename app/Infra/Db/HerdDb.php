<?php

namespace App\Infra\Db;

use App\Domain\Herds\HerdFilters;
use App\Models\Herd;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class HerdDb
{
    public function paginate(HerdFilters $filters): LengthAwarePaginator
    {
        $query = Herd::query()
            ->with(['farm:id,name,rural_producer_id', 'farm.ruralProducer:id,name'])
            ->latest('updated_at');

        $this->applyFilters($query, $filters);

        return $query
            ->paginate($filters->perPage())
            ->withQueryString();
    }

    public function create(array $data): Herd
    {
        return Herd::query()->create($data);
    }

    public function update(Herd $herd, array $data): Herd
    {
        $herd->update($data);

        return $herd->refresh();
    }

    public function findForResource(int $herdId): Herd
    {
        return Herd::query()
            ->with(['farm:id,name,rural_producer_id', 'farm.ruralProducer:id,name'])
            ->findOrFail($herdId);
    }

    public function delete(Herd $herd): void
    {
        $herd->delete();
    }

    private function applyFilters(Builder $query, HerdFilters $filters): void
    {
        $query
            ->when(
                $filters->search(),
                fn (Builder $builder, string $search) => $builder->whereHas('farm', function (Builder $farmQuery) use ($search): void {
                    $farmQuery->where('name', 'like', "%{$search}%");
                })
            )
            ->when(
                $filters->species(),
                fn (Builder $builder, string $species) => $builder->where('species', $species)
            )
            ->when(
                $filters->purpose(),
                fn (Builder $builder, string $purpose) => $builder->where('purpose', $purpose)
            )
            ->when(
                $filters->farmId(),
                fn (Builder $builder, int $farmId) => $builder->where('farm_id', $farmId)
            )
            ->when(
                $filters->ruralProducerId(),
                fn (Builder $builder, int $producerId) => $builder->whereHas('farm', function (Builder $farmQuery) use ($producerId): void {
                    $farmQuery->where('rural_producer_id', $producerId);
                })
            );
    }
}

<?php

namespace App\Infra\Db;

use App\Domain\Farms\FarmFilters;
use App\Models\Farm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FarmDb
{
    public function paginate(FarmFilters $filters): LengthAwarePaginator
    {
        $query = Farm::query()
            ->with(['ruralProducer:id,name'])
            ->withCount('herds')
            ->withSum('herds as total_animals', 'quantity')
            ->latest();

        $this->applyFilters($query, $filters);

        return $query
            ->paginate($filters->perPage())
            ->withQueryString();
    }

    public function create(array $data): Farm
    {
        return Farm::query()->create($data);
    }

    public function update(Farm $farm, array $data): Farm
    {
        $farm->update($data);

        return $farm->refresh();
    }

    public function findForResource(int $farmId): Farm
    {
        return Farm::query()
            ->with(['ruralProducer', 'herds'])
            ->withCount('herds')
            ->withSum('herds as total_animals', 'quantity')
            ->findOrFail($farmId);
    }

    public function delete(Farm $farm): void
    {
        $farm->delete();
    }

    public function allForExport(FarmFilters $filters): Collection
    {
        $query = Farm::query()
            ->with(['ruralProducer:id,name'])
            ->withCount('herds')
            ->withSum('herds as total_animals', 'quantity')
            ->orderBy('name');

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    private function applyFilters(Builder $query, FarmFilters $filters): void
    {
        $query
            ->when(
                $filters->search(),
                fn (Builder $builder, string $search) => $builder->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('state_registration', 'like', "%{$search}%");
                })
            )
            ->when(
                $filters->city(),
                fn (Builder $builder, string $city) => $builder->where('city', 'like', "%{$city}%")
            )
            ->when(
                $filters->state(),
                fn (Builder $builder, string $state) => $builder->where('state', $state)
            )
            ->when(
                $filters->ruralProducerId(),
                fn (Builder $builder, int $producerId) => $builder->where('rural_producer_id', $producerId)
            );
    }
}

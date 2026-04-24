<?php

namespace App\Infra\Db;

use App\Models\Farm;
use App\Models\RuralProducer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LookupDb
{
    public function options(): array
    {
        return [
            'rural_producers' => RuralProducer::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'farms' => Farm::query()
                ->orderBy('name')
                ->get(['id', 'name', 'rural_producer_id']),
            'producer_locations' => $this->locations(RuralProducer::query()),
            'farm_locations' => $this->locations(Farm::query()),
        ];
    }

    private function locations(Builder $query): array
    {
        return $query
            ->select('state', 'city')
            ->whereNotNull('state')
            ->whereNotNull('city')
            ->orderBy('state')
            ->orderBy('city')
            ->get()
            ->groupBy('state')
            ->map(fn (Collection $rows, string $state): array => [
                'state' => $state,
                'cities' => $rows->pluck('city')->unique()->values()->all(),
            ])
            ->values()
            ->all()
        ;
    }
}

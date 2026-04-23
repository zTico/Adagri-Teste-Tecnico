<?php

namespace App\Infra\Db;

use App\Models\Farm;
use App\Models\Herd;
use App\Models\RuralProducer;

class ReportDb
{
    public function build(): array
    {
        return [
            'totals' => [
                'rural_producers' => RuralProducer::count(),
                'farms' => Farm::count(),
                'animals' => (int) Herd::sum('quantity'),
            ],
            'farms_by_city' => Farm::query()
                ->selectRaw('city, state, COUNT(*) as total')
                ->groupBy('city', 'state')
                ->orderByDesc('total')
                ->orderBy('city')
                ->get()
                ->map(fn (Farm $farm): array => [
                    'city' => $farm->city,
                    'state' => $farm->state,
                    'total' => (int) $farm->total,
                ])
                ->all(),
            'animals_by_species' => Herd::query()
                ->selectRaw('species, SUM(quantity) as total')
                ->groupBy('species')
                ->orderByDesc('total')
                ->get()
                ->map(fn (Herd $herd): array => [
                    'species' => $herd->species?->value ?? (string) $herd->getAttribute('species'),
                    'total' => (int) $herd->total,
                ])
                ->all(),
        ];
    }
}

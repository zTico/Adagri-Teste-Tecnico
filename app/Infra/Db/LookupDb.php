<?php

namespace App\Infra\Db;

use App\Models\Farm;
use App\Models\RuralProducer;

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
        ];
    }
}

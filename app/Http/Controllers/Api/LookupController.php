<?php

namespace App\Http\Controllers\Api;

use App\Enums\HerdPurpose;
use App\Enums\HerdSpecies;
use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\RuralProducer;
use App\Services\PostalCodeLookupService;
use Illuminate\Http\JsonResponse;

class LookupController extends Controller
{
    public function __construct(
        private readonly PostalCodeLookupService $postalCodeLookupService,
    ) {
    }

    public function options(): JsonResponse
    {
        return response()->json([
            'species' => HerdSpecies::options(),
            'purposes' => HerdPurpose::options(),
            'rural_producers' => RuralProducer::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'farms' => Farm::query()
                ->orderBy('name')
                ->get(['id', 'name', 'rural_producer_id']),
        ]);
    }

    public function postalCode(string $postalCode): JsonResponse
    {
        return response()->json(
            $this->postalCodeLookupService->lookup($postalCode)
        );
    }
}

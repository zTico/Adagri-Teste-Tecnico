<?php

namespace App\Http\Controllers\Api;

use App\Enums\HerdPurpose;
use App\Enums\HerdSpecies;
use App\Domain\PostalCode\PostalCodeLookup;
use App\Http\Controllers\Controller;
use App\Infra\Db\LookupDb;
use App\Infra\Location\IbgeLocationLookup;
use Illuminate\Http\JsonResponse;

class LookupController extends Controller
{
    public function __construct(
        private readonly LookupDb $lookupDb,
        private readonly IbgeLocationLookup $locationLookup,
        private readonly PostalCodeLookup $postalCodeLookup,
    ) {
    }

    public function options(): JsonResponse
    {
        return response()->json([
            'species' => HerdSpecies::options(),
            'purposes' => HerdPurpose::options(),
            ...$this->lookupDb->options(),
        ]);
    }

    public function brazilStates(): JsonResponse
    {
        return response()->json($this->locationLookup->states());
    }

    public function brazilCities(string $state): JsonResponse
    {
        return response()->json($this->locationLookup->cities($state));
    }

    public function postalCode(string $postalCode): JsonResponse
    {
        return response()->json(
            $this->postalCodeLookup->lookup($postalCode)
        );
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farm\IndexFarmRequest;
use App\Http\Requests\Farm\UpsertFarmRequest;
use App\Http\Resources\FarmResource;
use App\Models\Farm;
use App\QueryFilters\FarmFilters;
use App\Services\FarmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FarmController extends Controller
{
    public function __construct(
        private readonly FarmService $service,
    ) {
        $this->authorizeResource(Farm::class, 'farm');
    }

    public function index(IndexFarmRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 10);

        $farms = (new FarmFilters($validated))
            ->apply(
                Farm::query()
                    ->with(['ruralProducer:id,name'])
                    ->withCount('herds')
                    ->withSum('herds as total_animals', 'quantity')
                    ->latest()
            )
            ->paginate($perPage)
            ->withQueryString();

        return FarmResource::collection($farms);
    }

    public function store(UpsertFarmRequest $request): JsonResponse
    {
        $farm = $this->service->create($request->validated());

        return (new FarmResource($this->farmResourceQuery($farm->id)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Farm $farm): FarmResource
    {
        return new FarmResource($this->farmResourceQuery($farm->id));
    }

    public function update(UpsertFarmRequest $request, Farm $farm): FarmResource
    {
        $farm = $this->service->update($farm, $request->validated());

        return new FarmResource($this->farmResourceQuery($farm->id));
    }

    public function destroy(Farm $farm): Response
    {
        $farm->delete();

        return response()->noContent();
    }

    private function farmResourceQuery(int $farmId): Farm
    {
        return Farm::query()
            ->with(['ruralProducer', 'herds'])
            ->withCount('herds')
            ->withSum('herds as total_animals', 'quantity')
            ->findOrFail($farmId);
    }
}

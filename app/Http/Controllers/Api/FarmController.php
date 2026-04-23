<?php

namespace App\Http\Controllers\Api;

use App\Domain\Farms\FarmFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Farm\IndexFarmRequest;
use App\Http\Requests\Farm\UpsertFarmRequest;
use App\Http\Resources\FarmResource;
use App\Infra\Db\FarmDb;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FarmController extends Controller
{
    public function __construct(
        private readonly FarmDb $farms,
    ) {
        $this->authorizeResource(Farm::class, 'farm');
    }

    public function index(IndexFarmRequest $request): AnonymousResourceCollection
    {
        return FarmResource::collection(
            $this->farms->paginate(new FarmFilters($request->validated()))
        );
    }

    public function store(UpsertFarmRequest $request): JsonResponse
    {
        $farm = $this->farms->create($request->validated());

        return (new FarmResource($this->farms->findForResource($farm->id)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Farm $farm): FarmResource
    {
        return new FarmResource($this->farms->findForResource($farm->id));
    }

    public function update(UpsertFarmRequest $request, Farm $farm): FarmResource
    {
        $farm = $this->farms->update($farm, $request->validated());

        return new FarmResource($this->farms->findForResource($farm->id));
    }

    public function destroy(Farm $farm): Response
    {
        $this->farms->delete($farm);

        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Herd\IndexHerdRequest;
use App\Http\Requests\Herd\UpsertHerdRequest;
use App\Http\Resources\HerdResource;
use App\Models\Herd;
use App\QueryFilters\HerdFilters;
use App\Services\HerdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HerdController extends Controller
{
    public function __construct(
        private readonly HerdService $service,
    ) {
        $this->authorizeResource(Herd::class, 'herd');
    }

    public function index(IndexHerdRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 10);

        $herds = (new HerdFilters($validated))
            ->apply(
                Herd::query()
                    ->with(['farm:id,name,rural_producer_id', 'farm.ruralProducer:id,name'])
                    ->latest('updated_at')
            )
            ->paginate($perPage)
            ->withQueryString();

        return HerdResource::collection($herds);
    }

    public function store(UpsertHerdRequest $request): JsonResponse
    {
        $herd = $this->service->create($request->validated());

        return (new HerdResource($herd->load(['farm.ruralProducer'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Herd $herd): HerdResource
    {
        return new HerdResource($herd->load(['farm.ruralProducer']));
    }

    public function update(UpsertHerdRequest $request, Herd $herd): HerdResource
    {
        $herd = $this->service->update($herd, $request->validated());

        return new HerdResource($herd->load(['farm.ruralProducer']));
    }

    public function destroy(Herd $herd): Response
    {
        $herd->delete();

        return response()->noContent();
    }
}

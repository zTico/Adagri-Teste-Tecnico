<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RuralProducer\IndexRuralProducerRequest;
use App\Http\Requests\RuralProducer\UpsertRuralProducerRequest;
use App\Http\Resources\RuralProducerResource;
use App\Models\RuralProducer;
use App\QueryFilters\RuralProducerFilters;
use App\Services\RuralProducerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RuralProducerController extends Controller
{
    public function __construct(
        private readonly RuralProducerService $service,
    ) {
        $this->authorizeResource(RuralProducer::class, 'rural_producer');
    }

    public function index(IndexRuralProducerRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 10);

        $producers = (new RuralProducerFilters($validated))
            ->apply(
                RuralProducer::query()
                    ->withCount('farms')
                    ->latest()
            )
            ->paginate($perPage)
            ->withQueryString();

        return RuralProducerResource::collection($producers);
    }

    public function store(UpsertRuralProducerRequest $request): JsonResponse
    {
        $producer = $this->service->create($request->validated());

        return (new RuralProducerResource($producer->loadCount('farms')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(RuralProducer $ruralProducer): RuralProducerResource
    {
        return new RuralProducerResource(
            $ruralProducer->load(['farms.herds'])->loadCount('farms')
        );
    }

    public function update(UpsertRuralProducerRequest $request, RuralProducer $ruralProducer): RuralProducerResource
    {
        $producer = $this->service->update($ruralProducer, $request->validated());

        return new RuralProducerResource($producer->load(['farms.herds'])->loadCount('farms'));
    }

    public function destroy(RuralProducer $ruralProducer): Response
    {
        $ruralProducer->delete();

        return response()->noContent();
    }
}

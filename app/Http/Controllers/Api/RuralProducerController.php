<?php

namespace App\Http\Controllers\Api;

use App\Domain\RuralProducers\RuralProducerFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\RuralProducer\IndexRuralProducerRequest;
use App\Http\Requests\RuralProducer\UpsertRuralProducerRequest;
use App\Http\Resources\RuralProducerResource;
use App\Infra\Db\RuralProducerDb;
use App\Models\RuralProducer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RuralProducerController extends Controller
{
    public function __construct(
        private readonly RuralProducerDb $producers,
    ) {
        $this->authorizeResource(RuralProducer::class, 'rural_producer');
    }

    public function index(IndexRuralProducerRequest $request): AnonymousResourceCollection
    {
        return RuralProducerResource::collection(
            $this->producers->paginate(new RuralProducerFilters($request->validated()))
        );
    }

    public function store(UpsertRuralProducerRequest $request): JsonResponse
    {
        $producer = $this->producers->create($request->validated());

        return (new RuralProducerResource($this->producers->findForResource($producer->id)))
            ->response()
            ->setStatusCode(201)
        ;
    }

    public function show(RuralProducer $ruralProducer): RuralProducerResource
    {
        return new RuralProducerResource(
            $this->
            producers->
            findForResource($ruralProducer->id)
        );
    }

    public function update(UpsertRuralProducerRequest $request, RuralProducer $ruralProducer): RuralProducerResource
    {
        $producer = $this
            ->producers
            ->update(
                $ruralProducer,
                $request->validated()
            )
        ;

        return new RuralProducerResource(
            $this->
            producers->
            findForResource($producer->id)
        );
    }

    public function destroy(RuralProducer $ruralProducer): Response
    {
        $this->producers->delete($ruralProducer);

        return response()->noContent();
    }
}

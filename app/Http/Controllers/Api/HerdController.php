<?php

namespace App\Http\Controllers\Api;

use App\Domain\Herds\HerdFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Herd\IndexHerdRequest;
use App\Http\Requests\Herd\UpsertHerdRequest;
use App\Http\Resources\HerdResource;
use App\Infra\Db\HerdDb;
use App\Models\Herd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HerdController extends Controller
{
    public function __construct(
        private readonly HerdDb $herds,
    ) {
        $this->authorizeResource(Herd::class, 'herd');
    }

    public function index(IndexHerdRequest $request): AnonymousResourceCollection
    {
        return HerdResource::collection(
            $this->herds->paginate(new HerdFilters($request->validated()))
        );
    }

    public function store(UpsertHerdRequest $request): JsonResponse
    {
        $herd = $this->herds->create($request->validated());

        return (new HerdResource($this->herds->findForResource($herd->id)))
            ->response()
            ->setStatusCode(201)
        ;
    }

    public function show(Herd $herd): HerdResource
    {
        return new HerdResource($this->herds->findForResource($herd->id));
    }

    public function update(UpsertHerdRequest $request, Herd $herd): HerdResource
    {
        $herd = $this->herds->update($herd, $request->validated());

        return new HerdResource($this->herds->findForResource($herd->id));
    }

    public function destroy(Herd $herd): Response
    {
        $this->herds->delete($herd);

        return response()->noContent();
    }
}

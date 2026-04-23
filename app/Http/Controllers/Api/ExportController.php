<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farm\IndexFarmRequest;
use App\Models\Farm;
use App\Models\RuralProducer;
use App\Services\Exports\FarmExportService;
use App\Services\Exports\ProducerHerdPdfExportService;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(
        private readonly FarmExportService $farmExportService,
        private readonly ProducerHerdPdfExportService $producerHerdPdfExportService,
    ) {
    }

    public function farms(IndexFarmRequest $request): BinaryFileResponse
    {
        $this->authorize('viewAny', Farm::class);

        return $this->farmExportService->export($request->validated());
    }

    public function producerHerds(RuralProducer $ruralProducer): Response
    {
        $this->authorize('view', $ruralProducer);

        return $this->producerHerdPdfExportService->export($ruralProducer);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportsService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportsService $reportsService,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        return response()->json($this->reportsService->build());
    }
}

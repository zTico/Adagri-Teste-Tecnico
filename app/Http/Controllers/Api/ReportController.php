<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infra\Db\ReportDb;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportDb $reports,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        return response()->json($this->reports->build());
    }
}

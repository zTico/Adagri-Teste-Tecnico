<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\FarmController;
use App\Http\Controllers\Api\HerdController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RuralProducerController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('profile')->group(function (): void {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
        Route::post('/photo', [ProfileController::class, 'uploadPhoto']);
        Route::delete('/photo', [ProfileController::class, 'deletePhoto']);
    });

    Route::prefix('lookups')->group(function (): void {
        Route::get('/options', [LookupController::class, 'options']);
        Route::get('/brazil-states', [LookupController::class, 'brazilStates']);
        Route::get('/brazil-states/{state}/cities', [LookupController::class, 'brazilCities']);
        Route::get('/postal-code/{postalCode}', [LookupController::class, 'postalCode']);
    });

    Route::get('/reports', ReportController::class);

    Route::prefix('exports')->group(function (): void {
        Route::get('/farms', [ExportController::class, 'farms']);
        Route::get('/rural-producers/{rural_producer}/herds-pdf', [ExportController::class, 'producerHerds']);
    });

    Route::apiResource('rural-producers', RuralProducerController::class);
    Route::apiResource('farms', FarmController::class);
    Route::apiResource('herds', HerdController::class);
});

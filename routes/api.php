<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ApiReceiverController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/v1/assets', [ApiReceiverController::class, 'storeAsset']);
    Route::put('/v1/assets/{serial_number}', [ApiReceiverController::class, 'updateAsset']);
    Route::delete('/v1/assets/{serial_number}', [ApiReceiverController::class, 'deleteAsset']);
    Route::post('/v1/incidents', [ApiReceiverController::class, 'storeIncident']);
    Route::put('/v1/incidents/{uuid}', [ApiReceiverController::class, 'updateIncident']);
    Route::delete('/v1/incidents/{uuid}', [ApiReceiverController::class, 'deleteIncident']);
    Route::put('/v1/incidents/{incident:uuid}/cancel', [ApiReceiverController::class, 'cancelIncident']);

    
});

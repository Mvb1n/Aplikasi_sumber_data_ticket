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

    Route::put('/v1/assets/{serial_number}', [ApiReceiverController::class, 'updateAsset']);
    Route::delete('/v1/assets/{serial_number}', [ApiReceiverController::class, 'deleteAsset']);
    Route::put('/v1/incidents/{uuid}', [ApiReceiverController::class, 'updateIncident']);
    Route::delete('/v1/incidents/{uuid}', [ApiReceiverController::class, 'deleteIncident']);
    
});

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

    Route::put('/webhook/assets/{serial_number}', [ApiReceiverController::class, 'updateAsset']);
    Route::delete('/webhook/assets/{serial_number}', [ApiReceiverController::class, 'deleteAsset']);
    Route::put('/webhook/Incidents/{serial_number}', [ApiReceiverController::class, 'updateIncident']);
    Route::delete('/webhook/Incidenta/{serial_number}', [ApiReceiverController::class, 'deleteIncident']);
    
});

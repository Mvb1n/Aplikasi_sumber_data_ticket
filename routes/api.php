<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\IncidentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/v1/assets/{serial_number}', [AssetController::class, 'updateFromApi']);
    Route::delete('/v1/assets/{serial_number}', [AssetController::class, 'destroyFromApi']);
    Route::put('/v1/incidents/{serial_number}', [IncidentController::class, 'updateFromApi']);
    Route::delete('/v1/incidents/{serial_number}', [IncidentController::class, 'destroyFromApi']);
    
});

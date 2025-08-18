<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SyncLogController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ApiSenderController;

Route::get('/buat-token', function () {
    $user = User::where('email', 'admin@gmail.com')->first();
    $token = $user->createToken('token-dari-route')->plainTextToken;
    return response()->json(['token' => $token]);
});

Route::get('/', function () {
    return view('welcome');
});

// Setelah login, arahkan ke halaman input aset
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Rute untuk form kita
    Route::resource('assets', AssetController::class);
    Route::resource('incidents', IncidentController::class);

    // Route::get('/my-incidents', [IncidentController::class, 'index'])->name('incidents.index');
    // Route::delete('/incidents/{incident}', [IncidentController::class, 'cancelIncident'])->name('incidents.cancel');

    Route::get('/sync-logs', [SyncLogController::class, 'index'])->name('sync-logs.index');

    // Rute profil dari Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
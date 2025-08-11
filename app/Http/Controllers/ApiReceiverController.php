<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiReceiverController extends Controller
{

    private string $apiUrl;
    private string $apiToken;

    public function __construct()
    {
        // Ambil konfigurasi dari file config/services.php yang terhubung ke .env
        $this->apiUrl = config('services.ticketing.url');
        $this->apiToken = config('services.ticketing.token');
    }
    public function updateAsset(Request $request, $serial_number)
    {
        $asset = Asset::where('serial_number', $serial_number)->firstOrFail();
        // Lakukan update tanpa memicu event untuk mencegah infinite loop
        Asset::withoutEvents(function () use ($asset, $request) {
            $asset->update($request->all());
        });
        return response()->json(['message' => 'Asset updated in App 2']);
    }

    public function deleteAsset(Request $request, $serial_number)
    {
        $asset = Asset::where('serial_number', $serial_number)->firstOrFail();
        // Lakukan update tanpa memicu event untuk mencegah infinite loop
        Asset::withoutEvents(function () use ($asset) {
            $asset->delete();
        });
        return response()->json(['message' => 'Asset delete in App 2']);
    }

}

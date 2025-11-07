<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Asset;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

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

    public function storeAsset(Request $request)
    {

        $site = Site::where('location_code', $request->site_location_code)->first();
        $asset = Asset::create([
            'site_id' => $site->id,
            'name' => $request->name,
            'serial_number' => $request->serial_number,
            'category' => $request->category,
            'status' => $request->status,
            'site_location_code' => $request->site_location_code,
        ]);

        return response()->json([
            'message' => 'Asset created successfully via API!',
            'data' => $asset
        ], 201);
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

    public function deleteAsset($serial_number)
    {
        $asset = Asset::where('serial_number', $serial_number)->first();

        if ($asset) {
            // Hapus data tanpa memicu event lagi untuk mencegah infinite loop
            Asset::withoutEvents(function () use ($asset) {
                $asset->delete();
            });
            Log::info('Aset #' . $serial_number . ' berhasil dihapus via webhook dari Aplikasi 1.');
        } else {
            Log::warning('Menerima permintaan hapus untuk aset #' . $serial_number . ', tetapi tidak ditemukan di database lokal.');
        }

        return response()->json(['message' => 'OK']);
    }


    public function storeIncident(Request $request)
    {
        // Gunakan updateOrCreate untuk menghindari duplikasi
        // Kita menggunakan UUID sebagai kunci unik
        Incident::withoutEvents(function () use ($request) {
            Incident::updateOrCreate(
                ['uuid' => $request->uuid],
                [
                    'title' => $request->title,
                    'reporter_email' => $request->reporter_email,
                    'site_location_code' => $request->site_location_code,
                    'specific_location' => $request->specific_location,
                    'chronology' => $request->chronology,
                    'involved_asset_sn' => $request->involved_asset_sn,
                ]
            );
        });

        return response()->json(['message' => 'Incident received and synced.']);
    }

    public function updateIncident(Request $request, Incident $incident, $uuid)
    {
        $incident = Incident::where('uuid', $uuid)->firstOrFail();
        // Lakukan update tanpa memicu event untuk mencegah infinite loop
        Incident::withoutEvents(function () use ($incident, $request) {
            $incident->update($request->all());
        });
        return response()->json(['message' => 'Incident updated in App 2']);
    }

    public function deleteIncident(Incident $incident, $uuid)
    {
        $incident = Incident::where('uuid', $uuid)->firstOrFail();
        // Lakukan update tanpa memicu event untuk mencegah infinite loop
        Incident::withoutEvents(function () use ($incident) {
            $incident->delete();
        });
        return response()->json(['message' => 'Incident delete in App 2']);
    }

    public function cancelIncident(Incident $incident, $uuid)
    {
        $incident = Incident::where('uuid', $uuid)->firstOrFail();
        // Lakukan update tanpa memicu event untuk mencegah infinite loop
        Incident::withoutEvents(function () use ($incident) {
            $incident->put();
        });

        return response()->json(['message' => 'Incident cancelled and assets restored.']);
    }

}

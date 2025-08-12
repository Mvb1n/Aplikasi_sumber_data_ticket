<?php

namespace App\Listeners;

use Throwable;
use App\Models\Asset;
use App\Models\SyncLog;
use App\Events\IncidentReported;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use App\Models\IncidentSyncLog; // Model baru untuk mencatat log sinkronisasi

// 1. Implementasikan ShouldQueue agar listener berjalan di background
class SendIncidentToTicketApp implements ShouldQueue
{
    /**
     * Jumlah percobaan ulang jika job gagal.
     * @var int
     */
    public int $tries = 3;

    /**
     * Waktu (detik) sebelum job dianggap timeout.
     * @var int
     */
    public int $timeout = 60;

    private string $apiUrl;
    private string $apiToken;

    use InteractsWithQueue;

    /**
     * Ambil konfigurasi dari file .env, bukan hard-code.
     */
    public function __construct()
    {
        $this->apiUrl = config('services.ticketing.url');
        $this->apiToken = config('services.ticketing.token');
    }

    /**
     * Menangani event. Method ini akan dijalankan oleh Queue worker.
     * @throws RequestException
     */
    public function handle(IncidentReported $event): void
    {
        $incident = $event->incident;

        $dataToSync = [
            'uuid' => $incident->uuid, // KIRIM UUID
            'title' => $incident->title,
            'reporter_email' => $incident->reporter_email,
            'site_location_code' => $incident->site_location_code,
            'specific_location' => $incident->specific_location,
            'chronology' => $incident->chronology,
            'involved_asset_sn' => $incident->involved_asset_sn,
        ];

        $apiUrl = config('services.ticketing.url') . '/api/v1/incidents';
        $apiToken = config('services.ticketing.token');

        try {
            $response = Http::withToken($apiToken)->acceptJson()->post($apiUrl, $dataToSync);

            // 2. Jika pengiriman API berhasil, baru kita ubah status aset di Aplikasi 2
            if ($response->successful()) {
                if (!empty($incident->involved_asset_sn)) {
                    $serialNumbers = array_map('trim', explode(',', $incident->involved_asset_sn));

                    // Cari dan update semua aset yang terlibat di database LOKAL (Aplikasi 2)
                    Asset::whereIn('serial_number', $serialNumbers)
                         ->update(['status' => 'Stolen/Lost']);
                }
            }

            // Mencatat log ke database (tetap ada)
            SyncLog::create([
                'model_type' => 'Incident',
                'model_id' => $incident->uuid,
                'status' => $response->successful() ? 'success' : 'failed',
                'response_code' => $response->status(),
                'response_body' => $response->successful() ? 'OK' : json_encode($response->json()),
            ]);

        } catch (\Exception $e) {
            SyncLog::create([
                'model_type' => 'Incident',
                'model_id' => $incident->uuid,
                'status' => 'failed',
                'response_code' => 500,
                'response_body' => $e->getMessage(),
            ]);
        }
    }
}
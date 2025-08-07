<?php

namespace App\Listeners;

use App\Events\AssetCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use App\Models\SyncLog; // Kita akan gunakan ini untuk mencatat log ke database
use Throwable; // Import Throwable untuk menangani semua jenis error di method failed()

class SendAssetToTicketApp implements ShouldQueue
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
     * Gunakan constructor untuk mengambil konfigurasi.
     * Ini lebih bersih daripada mengambilnya di dalam method handle().
     */
    public function __construct()
    {
        // Ambil konfigurasi dari file config, bukan hard-code
        $this->apiUrl = config('services.ticketing.url');
        $this->apiToken = config('services.ticketing.token');
    }

    /**
     * Menangani event saat job dijalankan.
     *
     * @param AssetCreated $event
     * @return void
     * @throws RequestException
     */
    public function handle(AssetCreated $event): void
    {
        $asset = $event->asset;

        // Siapkan data ASET untuk dikirim
        $dataToSync = [
            'name' => $asset->name,
            'serial_number' => $asset->serial_number,
            'category' => $asset->category,
            'status' => $asset->status,
            'site_location_code' => $asset->site_location_code,
        ];

        // Ambil URL dan Token dari file config/services.php
        $apiUrl = config('services.ticketing.url') . '/api/v1/assets';
        $apiToken = config('services.ticketing.token');

        try {
            $response = Http::withToken($apiToken)->acceptJson()->post($apiUrl, $dataToSync);

            SyncLog::create([
                'model_type' => 'Asset',
                'model_id' => $asset->id,
                'status' => $response->successful() ? 'success' : 'failed',
                'response_code' => $response->status(),
                'response_body' => $response->successful() ? 'OK' : json_encode($response->json()),
            ]);

        } catch (\Exception $e) {
            SyncLog::create([
                'model_type' => 'Asset',
                'model_id' => $asset->id,
                'status' => 'failed',
                'response_code' => 500, // Kode untuk error koneksi
                'response_body' => $e->getMessage(),
            ]);
        }
    }
}
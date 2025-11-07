<?php

namespace App\Listeners;

use Exception;
use Throwable;
use App\Models\Asset;
use App\Models\SyncLog;
use App\Events\IncidentReported;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
    public int $timeout = 120;

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
     * @param IncidentReported $event
     * @return void
     * @throws RequestException
     */

    
    public function handle(IncidentReported $event): void
    {
        $incident = $event->incident;

        // 1. Dapatkan daftar nomor seri aset (Logika Anda sudah benar)
        $serialNumbers = [];
        if (!empty($incident->involved_asset_sn)) {
            $assets = is_array($incident->involved_asset_sn)
                ? $incident->involved_asset_sn
                : array_map('trim', explode(',', $incident->involved_asset_sn));
            
            $serialNumbers = array_filter($assets);
        }

        // 2. Siapkan data TEKS untuk dikirim.
        $dataToSync = [
            'uuid'                => $incident->uuid,
            'title'               => $incident->title,
            'reporter_email'      => $incident->reporter_email,
            'site_location_code'  => $incident->site_location_code,
            'specific_location'   => $incident->specific_location,
            'chronology'          => $incident->chronology,
            'involved_asset_sn'   => $serialNumbers,
        ];

        $apiUrl = config('services.ticketing.url') . '/api/v1/incidents';
        $apiToken = config('services.ticketing.token');

        try {
            // 3. Siapkan request HTTP
            $request = Http::timeout(1200)->withToken($apiToken)->acceptJson(); // 120 detik

            // --- REVISI LOGIKA FILE ---
            $filePaths = json_decode($incident->attachment_paths, true);

            if (is_array($filePaths) && !empty($filePaths)) {
            foreach ($filePaths as $path) {
                
                // 1. Cek di disk 'local'
                if (Storage::disk('local')->exists($path)) {
                    
                    // 2. Ambil path absolut dari disk 'local'
                    $absolutePath = Storage::disk('local')->path($path);
                    
                    // 3. Buka file sebagai 'resource' (stream)
                    $fileResource = @fopen($absolutePath, 'r');

                    if ($fileResource) {
                        $request->attach(
                            'attachments[]',
                            $fileResource, // Kirim sebagai resource
                            basename($path)
                        );
                    } else {
                        Log::warning("Gagal buka file resource (local): {$path} untuk Incident {$incident->id}");
                    }
                } else {
                    Log::warning("File tidak ditemukan (local): {$path} untuk Incident {$incident->id}");
                }
            }
        }
        // --- AKHIR PERUBAHAN ---

            // 4. Kirim data (sebagai multipart)
            $response = $request->post($apiUrl, $dataToSync);

            // 5. Jika pengiriman API berhasil, ubah status aset (Logika Anda sudah benar)
            if ($response->successful() && !empty($serialNumbers)) {
                Asset::whereIn('serial_number', $serialNumbers)
                    ->update(['status' => 'Stolen/Lost']);
            }

            // 6. Catat hasil sinkronisasi (Logika Anda sudah benar)
            SyncLog::create([
                'model_type'    => 'Incident',
                'model_id'      => $incident->id,
                'status'        => $response->successful() ? 'success' : 'failed',
                'response_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

        } catch (Exception $e) {
            // 7. Catat jika terjadi error (Logika Anda sudah benar)
            SyncLog::create([
                'model_type'    => 'Incident',
                'model_id'      => $incident->id,
                'status'        => 'failed',
                'response_code' => 500,
                'response_body' => $e->getMessage(),
            ]);
        }
    }
}
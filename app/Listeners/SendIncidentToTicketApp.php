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

    // public int $delay = 5; // <-- TAMBAHKAN BARIS INI (5 detik)

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
        $serialNumbers = null; // Default-nya null
        
        if (!empty($incident->involved_asset_sn)) {
            $assets = is_array($incident->involved_asset_sn)
                ? $incident->involved_asset_sn
                : array_map('trim', explode(',', $incident->involved_asset_sn));
            
            // Kita filter dan rapikan kuncinya
            $filteredAssets = array_values(array_filter($assets));
            
            // HANYA set $serialNumbers jika array hasil filter TIDAK KOSONG
            if (!empty($filteredAssets)) {
                $serialNumbers = $filteredAssets;
            }
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
            'status'              => $incident->status,
        ];

        $apiUrl = config('services.ticketing.url') . '/api/v1/incidents';
        $apiToken = config('services.ticketing.token');

        // PASTE KODE BARU INI SEBAGAI GANTINYA
        try {
            // 1. Dapatkan data serial number dari string (yang disimpan controller)
            $serialNumbers = [];
            if (!empty($incident->involved_asset_sn)) {
                $serialNumbers = array_values(array_filter(explode(',', $incident->involved_asset_sn)));
            }

            // 2. Siapkan data TEKS (SAMA SEPERTI SEBELUMNYA)
            $dataToSync = [
                'uuid'                => $incident->uuid,
                'title'               => $incident->title,
                'reporter_email'      => $incident->reporter_email,
                'site_location_code'  => $incident->site_location_code,
                'specific_location'   => $incident->specific_location,
                'chronology'          => $incident->chronology,
                'involved_asset_sn'   => !empty($serialNumbers) ? $serialNumbers : null, // Kirim array (atau null)
                'status'              => $incident->status,
            ];

            $apiUrl = config('services.ticketing.url') . '/api/v1/incidents';
            $apiToken = config('services.ticketing.token');

            // --- PERUBAHAN BESAR DI SINI ---
            
            // 3. Siapkan array MULTIPART manual
            $multipartData = [];

            // 4. Loop semua data TEKS dan "ratakan" (flatten)
            foreach ($dataToSync as $key => $value) {
                if (is_array($value)) {
                    // "Ratakan" involved_asset_sn[]
                    foreach ($value as $index => $item) {
                        $multipartData[] = ['name' => "{$key}[{$index}]", 'contents' => $item];
                    }
                } elseif (is_null($value)) {
                    $multipartData[] = ['name' => $key, 'contents' => ''];
                } else {
                    $multipartData[] = ['name' => $key, 'contents' => $value];
                }
            }

            // 5. Tambahkan semua FILE secara manual (STRUKTUR BARU)
            $attachmentStructure = json_decode($incident->attachment_paths, true) ?? [];
            
            // 5a. Proses "Incident Files"
            $incidentFiles = $attachmentStructure['incident_files'] ?? [];
            foreach ($incidentFiles as $path) {
                if (Storage::disk('public')->exists($path)) {
                    $multipartData[] = [
                        'name'     => 'incident_files[]', // Nama input file
                        'contents' => Storage::disk('public')->get($path),
                        'filename' => basename($path)
                    ];
                }
            }

            // 5b. Proses "Asset Files"
            $assetFiles = $attachmentStructure['asset_files'] ?? [];
            foreach ($assetFiles as $serialNumber => $files) {
                foreach ($files as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        $multipartData[] = [
                            'name'     => "asset_files[{$serialNumber}][]", // Nama input file terstruktur
                            'contents' => Storage::disk('public')->get($path),
                            'filename' => basename($path)
                        ];
                    }
                }
            }

            // 6. Kirim request sebagai MULTIPART
            $response = Http::timeout(1200)
                            ->withToken($apiToken)
                            ->acceptJson()
                            ->asMultipart() // <-- Kunci
                            ->post($apiUrl, $multipartData); // <-- Kirim array multipart
            
            // --- AKHIR PERUBAHAN ---

            // 7. Jika pengiriman API berhasil, ubah status aset
            if ($response->successful() && !empty($serialNumbers)) {
                Asset::whereIn('serial_number', $serialNumbers)
                        ->update(['status' => 'Stolen/Lost']);
            }

            // 8. Catat hasil sinkronisasi
            SyncLog::create([
                'model_type'    => 'Incident',
                'model_id'      => $incident->id,
                'status'        => $response->successful() ? 'success' : 'failed',
                'response_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

        } catch (Exception $e) {
            // 9. Catat jika terjadi error
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
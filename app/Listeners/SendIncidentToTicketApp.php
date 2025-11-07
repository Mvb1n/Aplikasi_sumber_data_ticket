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
        // 1. Tetap pertahankan sleep untuk mengatasi race condition
        sleep(2);
        
        $incident = $event->incident;

        // 2. Siapkan data aset & teks (Logika Anda sudah benar)
        $serialNumbers = [];
        if (!empty($incident->involved_asset_sn)) {
            $assets = is_array($incident->involved_asset_sn)
                ? $incident->involved_asset_sn
                : array_map('trim', explode(',', $incident->involved_asset_sn));
            $serialNumbers = array_filter($assets);
        }
        
        $dataToSync = [
            'uuid'                => $incident->uuid,
            'title'               => $incident->title,
            'reporter_email'      => $incident->reporter_email,
            'site_location_code'  => $incident->site_location_code,
            'specific_location'   => $incident->specific_location,
            'chronology'          => $incident->chronology,
            'involved_asset_sn'   => $serialNumbers,
        ];

        $fullApiUrl = rtrim($this->apiUrl, '/') . '/api/v1/incidents';

        try {
            $request = Http::withToken($this->apiToken)->acceptJson();
            $filePaths = json_decode($incident->attachment_paths, true);

            // 3. Ini adalah logika file yang benar
            if (is_array($filePaths) && !empty($filePaths)) {
                foreach ($filePaths as $path) {
                    
                    // Pastikan kita baca dari disk 'local'
                    if (Storage::disk('local')->exists($path)) {
                        
                        // Gunakan 'readStream'
                        $fileResource = Storage::disk('local')->readStream($path);

                        if ($fileResource) {
                            // Ini adalah cara attach yang paling sederhana dan benar
                            // Key-nya HANYA 'attachments' (tanpa [])
                            $request->attach(
                                'attachments',
                                $fileResource,
                                basename($path)
                            );
                        }
                    }
                }
            }

            // 4. Kirim request
            $response = $request->post($fullApiUrl, $dataToSync);

            // 5. Proses sisa logika (Logika Anda sudah benar)
            if ($response->successful() && !empty($serialNumbers)) {
                Asset::whereIn('serial_number', $serialNumbers)
                    ->update(['status' => 'Stolen/Lost']);
            }

            SyncLog::create([
                'model_type'    => 'Incident',
                'model_id'      => $incident->id,
                'status'        => $response->successful() ? 'success' : 'failed',
                'response_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

        } catch (Exception $e) {
            SyncLog::create([
                'model_type'    => 'Incident',
                'model_id'      => $incident->id,
                'status'        => 'failed',
                'response_code' => $e instanceof RequestException ? $e->response->status() : 500,
                'response_body' => $e->getMessage(),
            ]);
            throw $e; 
        }
    }
}
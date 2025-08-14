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
     * @param IncidentReported $event
     * @return void
     * @throws RequestException
     */

    
   public function handle(IncidentReported $event): void
    {
        $incident = $event->incident;

        // 1. Dapatkan daftar nomor seri aset.
        // Kode ini akan menangani jika data berupa string (dipisahkan koma) atau sudah dalam bentuk array.
        $serialNumbers = [];
        if (!empty($incident->involved_asset_sn)) {
            $assets = is_array($incident->involved_asset_sn)
                ? $incident->involved_asset_sn
                : array_map('trim', explode(',', $incident->involved_asset_sn));
            
            // Pastikan tidak ada nilai kosong setelah pemrosesan
            $serialNumbers = array_filter($assets);
        }

        // 2. Siapkan data untuk dikirim ke API eksternal.
        // Pastikan Anda mengirim data aktual dari insiden, bukan aturan validasi.
        $dataToSync = [
            'uuid'              => $incident->uuid,
            'title'             => $incident->title, // Menggunakan data aktual
            'reporter_email'    => $incident->reporter_email, // Menggunakan data aktual
            'site_location_code'=> $incident->site_location_code, // Menggunakan data aktual
            'specific_location' => $incident->specific_location, // Menggunakan data aktual
            'chronology'        => $incident->chronology, // Menggunakan data aktual
            'involved_asset_sn' => $serialNumbers, // Kirim sebagai array yang sudah bersih
        ];

        $apiUrl = config('services.ticketing.url') . '/api/v1/incidents';
        $apiToken = config('services.ticketing.token');

        try {
            // 3. Kirim data ke API sistem tiket.
            $response = Http::withToken($apiToken)
                            ->acceptJson()
                            ->post($apiUrl, $dataToSync);

            // 4. Jika pengiriman API berhasil, ubah status aset di database lokal.
            // Cek apakah ada nomor seri untuk diupdate.
            if ($response->successful() && !empty($serialNumbers)) {
                // Logika ini sudah benar untuk memperbarui banyak aset sekaligus.
                // Tidak ada perubahan yang diperlukan di sini.
                Asset::whereIn('serial_number', $serialNumbers)
                     ->update(['status' => 'Stolen/Lost']);
            }

            // 5. Catat hasil sinkronisasi ke dalam log.
            SyncLog::create([
                'model_type'    => 'Incident',
                'model_id'      => $incident->id,
                'status'        => $response->successful() ? 'success' : 'failed',
                'response_code' => $response->status(),
                'response_body' => $response->body(), // Simpan body response lengkap untuk debug
            ]);

        } catch (\Exception $e) {
            // 6. Catat jika terjadi error saat koneksi atau proses.
            SyncLog::create([
                'model_type'    => 'Incident',
                'model_id'      => $incident->id,
                'status'        => 'failed',
                'response_code' => 500, // Kode error internal
                'response_body' => $e->getMessage(),
            ]);
        }
    }
}
<?php

namespace App\Http\Controllers;


use App\Models\Incident;
// Incident model tidak digunakan untuk menyimpan, jadi bisa dihapus jika tidak ada keperluan lain.
// use App\Models\Incident; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\RequestException; // Lebih spesifik untuk menangani error HTTP

class IncidentController extends Controller
{

    private string $apiUrl;
    private string $apiToken;
    private int $cacheDuration; // Menambahkan durasi cache agar mudah diubah

    /**
     * Mengambil konfigurasi dari file .env daripada hard-coding.
     * Ini adalah praktik terbaik untuk keamanan dan fleksibilitas.
     */
    public function __construct()
    {
        // Ambil konfigurasi dari file config/services.php yang terhubung ke .env
        $this->apiUrl = config('services.ticketing.url');
        $this->apiToken = config('services.ticketing.token');
        $this->cacheDuration = 600; // Durasi cache dalam menit
    }

    /**
     * Mengambil daftar 'sites' dari API dan menyimpannya di cache.
     * Fungsi ini sekarang menjadi satu-satunya sumber untuk mengambil 'sites'.
     */
    private function fetchSites()
    {
        // Gunakan Cache::remember untuk efisiensi.
        // Kunci cache dibuat unik berdasarkan URL API.
        return Cache::remember('sites_list:' . $this->apiUrl, now()->addMinutes($this->cacheDuration), function () {
            try {
                $response = Http::withToken($this->apiToken)
                    ->acceptJson()
                    ->get($this->apiUrl . '/api/v1/sites');

                // Lemparkan exception jika request gagal, akan ditangkap oleh blok catch.
                $response->throw(); 

                return $response->json();

            } catch (\Exception $e) {
                Log::critical('Gagal mengambil daftar site dari API: ' . $e->getMessage());
                // Kembalikan array kosong jika gagal agar tidak error di view.
                return []; 
            }
        });
    }

    // Menampilkan daftar semua laporan insiden di Aplikasi 2
    public function index()
    {
        // 1. Ambil daftar insiden dari database LOKAL Aplikasi 2
        $incidents = Incident::latest()->paginate(15);

        // 2. Ambil semua UUID dari daftar tersebut
        $uuids = $incidents->pluck('uuid')->toArray();

        // 3. "Tanya" ke Aplikasi 1 untuk status & site terbaru
        $apiUrl = config('services.ticketing.url') . '/api/v1/incidents/statuses';
        $apiToken = config('services.ticketing.token');

        try {
            $response = Http::withToken($apiToken)->acceptJson()->post($apiUrl, ['uuids' => $uuids]);

            if ($response->successful()) {
                $latestData = $response->json();

                // 4. Perbarui setiap insiden lokal dengan data terbaru dari API
                $incidents->each(function ($incident) use ($latestData) {
                    if (isset($latestData[$incident->uuid])) {
                        $incident->status = $latestData[$incident->uuid]['status'];
                        $incident->site_name = $latestData[$incident->uuid]['site_name']; // Tambahkan nama site
                    }
                });
            }
        } catch (\Exception $e) {
            // Jika API gagal, biarkan saja, data lama akan ditampilkan
        }

        // 5. Kirim data yang sudah diperbarui ke view
        return view('incident.index', compact('incidents'));
    }

    /**
     * Menampilkan form untuk membuat laporan insiden baru.
     */
    public function create()
    {
        $sites = $this->fetchSites();
        return view('incident.create', compact('sites'));
    }

    /**
     * Mengirim data insiden baru langsung ke API eksternal.
     * CATATAN: Proses ini berjalan secara synchronous (menunggu respon API).
     */
    public function store(Request $request)
    {
        // 1. Validasi data (TERMASUK VALIDASI FILE)
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'reporter_email' => 'required|email',
            'site_location_code' => 'required|string',
            'specific_location' => 'required|string',
            'chronology' => 'required|string',
            'involved_asset_sn' => 'nullable|array',
            'involved_asset_sn.*' => 'string',
            
            // --- TAMBAHAN UNTUK FILE ---
            'attachments' => 'nullable|array', // Pastikan 'attachments' adalah array
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // Validasi setiap file (maks 5MB)
            // -------------------------------
        ]);

        // Gabungkan array nomor seri menjadi string jika ada
        if (!empty($validatedData['involved_asset_sn'])) {
            $validatedData['involved_asset_sn'] = implode(',', $validatedData['involved_asset_sn']);
        } else {
            $validatedData['involved_asset_sn'] = null;
        }

        // --- TAMBAHAN: LOGIKA UNTUK PROSES FILE ---
        $attachmentPaths = []; // Siapkan array kosong untuk menampung path file

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Simpan file ke 'storage/app/public/attachments'
                // dan simpan path-nya ke variabel $path
                $path = $file->store('attachments', 'public');
                
                // Kumpulkan semua path ke dalam array $attachmentPaths
                $attachmentPaths[] = $path;
            }
        }

        // Tambahkan array path file ke $validatedData untuk disimpan ke database
        // Kita gunakan json_encode agar array-nya bisa disimpan dalam satu kolom teks
        $validatedData['attachment_paths'] = !empty($attachmentPaths) ? json_encode($attachmentPaths) : null;
        // ----------------------------------------

        // 2. Simpan data ke database.
        // $validatedData sekarang sudah berisi 'attachment_paths'
        Incident::create($validatedData);

        // 3. Kembalikan pesan sukses.
        return back()->with('success', 'Laporan berhasil dibuat dan sedang disinkronisasi');
    }

    /**
     * Display the specified resource.
     */
    public function show(Incident $incident)
    {
        // Ambil data detail dari API Aplikasi 1 menggunakan UUID dari model
        $apiUrl = $this->apiUrl . '/api/v1/incidents/' . $incident->uuid;
        $incidentData = [];

        try {
            $response = Http::withToken($this->apiToken)->acceptJson()->get($apiUrl);
            if ($response->successful()) {
                $incidentData = $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengambil detail insiden: ' . $e->getMessage());
        }

        return view('incident.show', compact('incidentData', 'incident'));
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function edit(Incident $incident)
    {
        // Ambil daftar site dari API Aplikasi 1
        $sites = $this->fetchSites(); 
        return view('incident.edit', compact('incident', 'sites'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Incident $incident)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'reporter_email' => 'required|email',
            'site_location_code' => 'required|string',
            'specific_location' => 'required|string',
            'chronology' => 'required|string',
            'involved_asset_sn' => 'nullable|string',
        ]);
        $incident->update($validatedData);
        return redirect()->route('incidents.index')->with('success', 'Laporan berhasil diperbarui! Sinkronisasi sedang berjalan.');
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Incident $incident)
    {
        // Kirim permintaan cancel ke API Aplikasi 1 tanpa menghapus data lokal
        $response = Http::withToken($this->apiToken)
            ->timeout(240)
            ->post("{$this->apiUrl}/api/v1/incidents/{$incident->uuid}/cancel");

        if ($response->successful()) {
            return back()->with('success', 'Laporan berhasil dibatalkan! Proses sinkronisasi berjalan di latar belakang.');
        } else {
            // Tangani error jika permintaan gagal
            $errorMessage = $response->json('message') ?? 'Terjadi kesalahan saat membatalkan laporan insiden.';
            return back()->with('error', $errorMessage);
        }
    }

    // public function cancelIncident(Incident $incident)
    // {
    //     // Kirim permintaan DELETE ke API Aplikasi 1
    //     $response = Http::withToken($this->apiToken)
    //         ->post("{$this->apiUrl}/api/v1/incidents/{$incident->uuid}/cancel");

    //     if ($response->successful()) {
    //         // Hapus data incident di database lokal
    //         $incident->delete();
    //         return back()->with('success', 'Permintaan delete asset berhasil dikirim dan data dihapus dari database.');
    //     } else {
    //         // Tangani error jika permintaan gagal
    //         $errorMessage = $response->json('message') ?? 'Terjadi kesalahan saat mengirim permintaan delete asset.';
    //         return back()->with('error', $errorMessage);
    //     }
    // }

}

<?php

namespace App\Http\Controllers;

use App\Models\Incident; // <-- Pastikan Model Incident di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\RequestException;

class IncidentController extends Controller
{
    private string $apiUrl;
    private string $apiToken;
    private int $cacheDuration;

    public function __construct()
    {
        $this->apiUrl = config('services.ticketing.url');
        $this->apiToken = config('services.ticketing.token');
        $this->cacheDuration = 600; // Durasi cache 10 menit
    }

    /**
     * Mengambil daftar 'sites' dari API Aplikasi Ticket dan menyimpannya di cache.
     */
    private function fetchSites()
    {
        return Cache::remember('sites_list:' . $this->apiUrl, now()->addMinutes($this->cacheDuration), function () {
            try {
                $response = Http::withToken($this->apiToken)
                    ->acceptJson()
                    ->get($this->apiUrl . '/api/v1/sites');

                $response->throw(); 
                return $response->json();

            } catch (\Exception $e) {
                Log::critical('Gagal mengambil daftar site dari API: ' . $e->getMessage());
                return []; 
            }
        });
    }

    /**
     * PERBAIKAN: 
     * Menampilkan daftar insiden dari database LOKAL.
     * Ini cepat, aman, dan memperbaiki error 'foreach(null)'.
     * Database lokal akan otomatis ter-update oleh queue worker Anda.
     */
    public function index(Request $request)
    {
        // Ambil data dari database LOKAL, bukan API
        $incidents = Incident::latest()->paginate(15); 
        
        // Ambil sites (untuk filter, jika ada)
        $sites = $this->fetchSites(); 

        return view('incident.index', compact('incidents', 'sites'));
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
     * PERBAIKAN:
     * Menyimpan laporan baru ke database LOKAL.
     * Event 'IncidentReported' akan terpicu dan queue akan mengirimnya ke Aplikasi Ticket.
     */
    public function store(Request $request)
    {
        // 1. Validasi data (TERMASUK FILE BARU)
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'reporter_email' => 'required|email',
            'site_location_code' => 'required|string',
            'specific_location' => 'required|string',
            'chronology' => 'required|string',
            
            'incident_files'   => 'nullable|array',
            'incident_files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            
            'asset_files'   => 'nullable|array',
            'asset_files.*'   => 'nullable|array',
            'asset_files.*.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',

            'involved_asset_sn' => 'nullable|array',
            'involved_asset_sn.*' => 'string',
        ]);

        // 2. Siapkan struktur JSON baru
        $attachmentStructure = [
            'incident_files' => [],
            'asset_files'    => [],
        ];
        
        $involvedSerialNumbers = [];

        // 3. Proses "File Umum"
        if (!empty($request->file('incident_files'))) {
            foreach ($request->file('incident_files') as $file) {
                $path = $this->storeFileWithOriginalName($file, 'attachments'); // Panggil helper
                $attachmentStructure['incident_files'][] = $path;
            }
        }

        // 4. Proses "File per Aset"

        if (!empty($request->file('asset_files'))) {
            // Loop array asset_files (contoh: ['2323' => [file1, file2]])
            foreach ($request->file('asset_files') as $serialNumber => $files) {
                $involvedSerialNumbers[] = $serialNumber; // Kumpulkan SN dari file
                $pathsForThisAsset = [];
                
                // Loop array $files (contoh: [file1, file2])
                foreach ($files as $file) {
                    $path = $this->storeFileWithOriginalName($file, 'attachments'); // Panggil helper
                    $pathsForThisAsset[] = $path;
                }
                $attachmentStructure['asset_files'][$serialNumber] = $pathsForThisAsset;
            }
        }
        
        // 5. Gabungkan SN dari checkbox dan SN dari file upload
        $allSerialNumbers = array_unique(array_merge(
            $involvedSerialNumbers, 
            $request->input('involved_asset_sn', [])
        ));
        $serialNumbersString = !empty($allSerialNumbers) ? implode(',', $allSerialNumbers) : null;

        // 6. Simpan data ke database LOKAL
        Incident::create([
            'title' => $validatedData['title'],
            'reporter_email' => $validatedData['reporter_email'],
            'site_location_code' => $validatedData['site_location_code'],
            'specific_location' => $validatedData['specific_location'],
            'chronology' => $validatedData['chronology'],
            'involved_asset_sn' => $serialNumbersString,
            'status' => 'Open',
            'attachment_paths' => json_encode($attachmentStructure), // Simpan JSON
        ]);

        // 7. Kembalikan pesan sukses.
        return back()->with('success', 'Laporan berhasil dibuat dan sedang disinkronisasi');
    }

    /**
     * ====================================================================
     * PENTING: FUNGSI HELPER INI HARUS ADA DI DALAM CLASS
     * ====================================================================
     */
    private function storeFileWithOriginalName($file, $folder)
    {
        if (!$file || !$file->isValid()) {
            return null;
        }
        $originalName = $file->getClientOriginalName();
        // Bersihkan nama file dari karakter aneh
        $safeName = preg_replace("/[^A-Za-z0-9\._-]/", '', $originalName);
        // Tambahkan prefix unik
        $finalName = uniqid() . '_' . $safeName;
        return $file->storeAs($folder, $finalName, 'public'); 
    }



    /**
     * PERBAIKAN:
     * Menampilkan detail insiden dari database LOKAL.
     * Tidak perlu memanggil API lagi, karena data sudah sinkron.
     */
    public function show(Incident $incident)
    {
        // $incident adalah model lokal (untuk fallback)
        
        $apiUrl = $this->apiUrl . '/api/v1/incidents/' . $incident->uuid;
        $incidentData = null; // <-- PENTING: Inisialisasi sebagai null

        try {
            $response = Http::withToken($this->apiToken)->acceptJson()->get($apiUrl);
            
            if ($response->successful()) {
                $incidentData = $response->json(); // Data lengkap dari API
            } else {
                Log::error('Gagal mengambil detail insiden dari API: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Koneksi API gagal saat mengambil detail insiden: ' . $e->getMessage());
        }

        // Kirim $incidentData (dari API) dan $incident (lokal) ke view
        return view('incident.show', compact('incidentData', 'incident'));
    }

    /**
     * Menampilkan form untuk mengedit insiden.
     */
    public function edit(Incident $incident)
    {
        $sites = $this->fetchSites(); 
        return view('incident.edit', compact('incident', 'sites'));
    }

    /**
     * PERBAIKAN:
     * Mengupdate insiden di database LOKAL.
     * Event 'IncidentUpdated' akan terpicu dan queue akan mengirim perubahan.
     */
    public function update(Request $request, Incident $incident)
    {
        // Gunakan 'sometimes' agar validasi hanya berjalan jika field-nya ada
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string',
            'reporter_email' => 'sometimes|required|email',
            'site_location_code' => 'sometimes|required|string',
            'specific_location' => 'sometimes|required|string',
            'chronology' => 'sometimes|required|string',
            'involved_asset_sn' => 'nullable|string',
        ]);
        
        // Update data lokal
        $incident->update($validatedData);
        
        // Event 'IncidentUpdated' akan terpicu oleh Model Observer
        // dan Listener 'SyncIncidentUpdateToApp1' akan mengirimnya ke queue.
        return redirect()->route('incidents.index')->with('success', 'Laporan berhasil diperbarui! Sinkronisasi sedang berjalan.');
    }
    
    /**
     * Menghapus insiden dari database LOKAL.
     * Event 'IncidentDeleted' akan terpicu dan queue akan menangani sisanya.
     */
    public function destroy(Incident $incident)
    {
        $incident->delete();

        // Event 'IncidentDeleted' terpicu.
        // Listener 'SyncIncidentDeleteToApp1' (yang ada di queue) akan berjalan.
        return back()->with('success', 'Laporan berhasil dihapus! Sinkronisasi berjalan di latar belakang.');
    }

    /**
     * Membatalkan insiden di database LOKAL.
     * Event 'IncidentUpdated' akan terpicu dan queue akan menangani sisanya.
     */
    public function cancel(Incident $incident)
    {
        $incident->status = 'Cancelled';
        $incident->save(); 
        
        // Event 'IncidentUpdated' terpicu.
        // Listener 'SyncIncidentUpdateToApp1' (yang ada di queue) akan berjalan.
        return back()->with('success', 'Insiden dibatalkan! Sinkronisasi ke aplikasi ticket sedang berjalan.');
    }
}
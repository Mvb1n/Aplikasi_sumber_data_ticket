<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AssetController extends Controller
{
    private string $apiUrl;
    private string $apiToken;
    private int $cacheDuration;

    public function __construct()
    {
        // GANTI DENGAN URL PUBLIK APLIKASI 1 ANDA
        $this->apiUrl = config('services.ticketing.url');
        $this->apiToken = config('services.ticketing.token');
        $this->cacheDuration = 10; // Durasi cache dalam menit
    }

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

    public function index()
    {
        $assets = Asset::latest()->paginate(15);
        return view('asset.index', compact('assets'));
    }

    public function create()
    {
        $sites = $this->fetchSites();
        return view('asset.create', compact('sites'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:assets,serial_number',
            'category' => 'required|string',
            'status' => 'required|string',
            'site_location_code' => 'required|string',
        ]);
        Asset::create($validatedData);
        return redirect()->route('assets.index')->with('success', 'Aset berhasil dibuat! Sinkronisasi sedang berjalan.');
    }

    public function edit(Asset $asset)
    {
        $sites = $this->fetchSites();
        return view('asset.edit', compact('asset', 'sites'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:assets,serial_number,' . $asset->id,
            'category' => 'required|string',
            'status' => 'required|string',
            'site_location_code' => 'required|string',
        ]);
        $asset->update($validatedData);
        return redirect()->route('assets.index')->with('success', 'Aset berhasil diperbarui! Sinkronisasi sedang berjalan.');
    }

    public function updateFromApi(Request $request, $serial_number)
    {
        $asset = Asset::where('serial_number', $serial_number)->first();

        if ($asset) {
            // Validasi data yang masuk dari Aplikasi 1
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string',
                'status' => 'required|string',
                'site_location_code' => 'required|string',
            ]);

            $asset->update($validatedData);
            return response()->json(['message' => 'Asset updated successfully in App 2.']);
        }

        return response()->json(['message' => 'Asset not found in App 2.'], 404);
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus! Sinkronisasi sedang berjalan.');
    }

    public function destroyFromApi($serial_number)
    {
        $asset = Asset::where('serial_number', $serial_number)->first();

        if ($asset) {
            $asset->delete();
            return response()->json(['message' => 'Asset deleted successfully from App 2.']);
        }

        return response()->json(['message' => 'Asset not found in App 2.'], 404);
    }
}
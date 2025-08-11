<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiSenderController extends Controller
{

    private string $apiUrl;
    private string $apiToken;

    public function __construct()
    {
        // Ambil konfigurasi dari file config/services.php yang terhubung ke .env
        $this->apiUrl = config('services.ticketing.url');
        $this->apiToken = config('services.ticketing.token');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        // Kirim permintaan UPDATE ke API Aplikasi 1
        $response = Http::withToken($this->apiToken)
            ->put($this->apiUrl . '/api/v1/assets/' . $asset->serial_number, $request->all());

        if ($response->successful()) {
            return back()->with('success', 'Permintaan update berhasil dikirim dan sedang disinkronkan.');
        }
        // ... (handle error) ...
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        // Kirim permintaan DELETE ke API Aplikasi 1
        $response = Http::withToken($this->apiToken)
            ->delete($this->apiUrl . '/api/v1/assets/' . $asset->serial_number);

        if ($response->successful()) {
            return back()->with('success', 'Permintaan delete asset berhasil dikirim.');
        }

        // ... (handle response) ...
    }

}

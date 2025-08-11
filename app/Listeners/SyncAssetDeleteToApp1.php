<?php

namespace App\Listeners;

use App\Events\AssetDeleted;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncAssetDeleteToApp1
{

    private string $apiUrl;
    private string $apiToken;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        // Ambil konfigurasi dari file config/services.php yang terhubung ke .env
        $this->apiUrl = config('services.ticketing.url');
        $this->apiToken = config('services.ticketing.token');
    }

    /**
     * Handle the event.
     */
    public function handle(AssetDeleted $event): void
    {
        $asset = $event->asset;
        // Kirim HTTP DELETE ke API Aplikasi 1
        Http::withToken($this->apiToken)
            ->delete($this->apiUrl . '/api/webhook/assets/' . $asset->serial_number);
    }
}

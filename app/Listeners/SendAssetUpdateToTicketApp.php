<?php

namespace App\Listeners;

use App\Models\SyncLog;
use App\Events\AssetUpdated;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAssetUpdateToTicketApp
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
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
 public function handle(AssetUpdated $event): void
    {
        $asset = $event->asset;
        $dataToSync = [
            'name' => $asset->name,
            'category' => $asset->category,
            'status' => $asset->status,
            'site_location_code' => $asset->site_location_code,
        ];
        $apiUrl = config('services.ticketing.url') . '/api/v1/assets/' . $asset->serial_number;
        $apiToken = config('services.ticketing.token');

        try {
            $response = Http::withToken($apiToken)->acceptJson()->put($apiUrl, $dataToSync);
            SyncLog::create([
                'model_type' => 'Asset', 'model_id' => $asset->id, 'action' => 'updated',
                'status' => $response->successful() ? 'success' : 'failed',
                'response_code' => $response->status(),
                'response_body' => $response->successful() ? 'OK' : json_encode($response->json()),
            ]);
        } catch (\Exception $e) {
            SyncLog::create([
                'model_type' => 'Asset', 'model_id' => $asset->id, 'action' => 'updated',
                'status' => 'failed', 'response_code' => 500,
                'response_body' => $e->getMessage(),
            ]);
        }
    }
}

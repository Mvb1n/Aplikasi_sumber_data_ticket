<?php

namespace App\Listeners;

use App\Events\IncidentUpdated;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncIncidentUpdateToApp1 implements ShouldQueue
{
    use InteractsWithQueue;
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
    public function handle(IncidentUpdated $event): void
    {
        $incident = $event->incident;
        // Kirim HTTP PUT/PATCH ke API Aplikasi 1
        Http::withToken($this->apiToken)
            ->put($this->apiUrl . '/api/v1/incidents/' . $incident->uuid, $incident->toArray());
    }
}

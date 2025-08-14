<?php

namespace App\Listeners;

use App\Events\IncidentCancelled;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendIncidentCancellationToTicketApp
{

    private string $apiUrl;
    private string $apiToken;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->apiUrl = config('services.ticketing.url');
        $this->apiToken = config('services.ticketing.token');
    }

    /**
     * Handle the event.
     */
   public function handle(IncidentCancelled $event): void
    {
        $incident = $event->incident;
        $apiUrl = config('services.ticketing.url') . '/api/v1/incidents/' . $incident->uuid . '/cancel';
        $apiToken = config('services.ticketing.token');

        try {
            $response = Http::withToken($apiToken)->acceptJson()->post($apiUrl);
            if ($response->failed()) {
                Log::error('Pembatalan insiden dari Listener gagal:', ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::critical('Koneksi API untuk pembatalan insiden gagal:', ['error' => $e->getMessage()]);
        }
    }
}

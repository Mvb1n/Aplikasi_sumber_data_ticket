<?php

namespace App\Listeners;

use App\Models\Asset;
use App\Models\SyncLog;
use App\Events\IncidentCancelled;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendIncidentCancellationToTicketApp
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
     public function handle(IncidentCancelled $event): void
    {
        $incident = $event->incident;

        $apiUrl = config('services.ticketing.url') . "/api//v1/incidents/{incident}/cancel";
        $apiToken = config('services.ticketing.token');

        try {
            $response = Http::withToken($apiToken)->acceptJson()->post($apiUrl, [
                'external_incident_id' => $incident->id
            ]);

            SyncLog::create([
                'model_type' => 'Incident', 'model_id' => $incident->id, 'action' => 'cancelled',
                'status' => $response->successful() ? 'success' : 'failed',
                'response_code' => $response->status(),
                'response_body' => $response->successful() ? 'OK' : json_encode($response->json()),
            ]);

        } catch (\Exception $e) {
            SyncLog::create([
                'model_type' => 'Incident', 'model_id' => $incident->id, 'action' => 'cancelled',
                'status' => 'failed', 'response_code' => 500,
                'response_body' => $e->getMessage(),
            ]);
        }
    }
}

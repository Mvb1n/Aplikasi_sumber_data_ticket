<?php

namespace App\Events;

use App\Models\Incident;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event ini diaktifkan ketika sebuah laporan Insiden baru dibuat.
 * Tujuannya adalah untuk membawa data Insiden tersebut ke Listener
 * yang akan menanganinya secara asynchronous (misalnya, mengirimnya ke API lain).
 */
class IncidentReported
{
    // Gunakan trait yang diperlukan untuk event yang akan di-queue.
    // InteractsWithSockets dan ShouldBroadcast tidak diperlukan jika event ini
    // hanya untuk komunikasi server-side (bukan ke frontend via websockets).
    use Dispatchable, SerializesModels;

    /**
     * Membuat instance event baru.
     *
     * Menggunakan "Constructor Property Promotion" dari PHP 8.
     * 'public Incident $incident' secara otomatis membuat properti publik
     * dan mengisinya dengan objek Incident yang diterima.
     *
     * @param Incident $incident Model Incident yang baru saja dibuat.
     */
    public function __construct(public Incident $incident)
    {
        // Tidak ada kode yang diperlukan di sini berkat constructor property promotion.
    }
}

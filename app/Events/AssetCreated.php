<?php

namespace App\Events;

use App\Models\Asset;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event ini diaktifkan ketika sebuah Aset baru berhasil dibuat di database.
 * Tujuannya adalah untuk membawa data Aset tersebut ke Listener yang akan menanganinya,
 * seperti mengirimkannya ke API lain.
 */
class AssetCreated
{
    // Gunakan trait yang diperlukan untuk event yang akan di-queue.
    // InteractsWithSockets dan ShouldBroadcast tidak diperlukan jika event ini
    // hanya untuk komunikasi server-side (bukan ke frontend via websockets).
    use Dispatchable, SerializesModels;

    /**
     * Membuat instance event baru.
     *
     * Menggunakan "Constructor Property Promotion" dari PHP 8.
     * 'public Asset $asset' secara otomatis membuat properti publik bernama $asset
     * dan mengisinya dengan objek Asset yang diterima.
     *
     * @param Asset $asset Model Aset yang baru saja dibuat.
     */
    public function __construct(public Asset $asset)
    {
        // Baris '$this->asset = $asset;' tidak lagi diperlukan di sini
        // karena sudah ditangani oleh constructor property promotion.
    }
}

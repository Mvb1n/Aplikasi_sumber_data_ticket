<?php

namespace App\Listeners;

use App\Events\IncidentDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncIncidentDeleteToApp1
{
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
    public function handle(IncidentDeleted $event): void
    {
        //
    }
}

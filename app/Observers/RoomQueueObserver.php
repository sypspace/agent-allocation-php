<?php

namespace App\Observers;

use App\Jobs\AssignAgent;
use App\Models\RoomQueue;
use Illuminate\Support\Facades\Log;

class RoomQueueObserver
{
    /**
     * Handle the RoomQueue "created" event.
     */
    public function created(RoomQueue $roomQueue): void
    {
        Log::notice("New message, room: {$roomQueue->room_id} added to queue");
    }

    /**
     * Handle the RoomQueue "updated" event.
     */
    public function updated(RoomQueue $roomQueue): void
    {
        Log::notice("New notif, room {$roomQueue->room_id} has been resolved");
    }

    /**
     * Handle the RoomQueue "deleted" event.
     */
    public function deleted(RoomQueue $roomQueue): void
    {
        //
    }

    /**
     * Handle the RoomQueue "restored" event.
     */
    public function restored(RoomQueue $roomQueue): void
    {
        //
    }

    /**
     * Handle the RoomQueue "force deleted" event.
     */
    public function forceDeleted(RoomQueue $roomQueue): void
    {
        //
    }
}

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
        AssignAgent::dispatch($roomQueue->room_id);
        Log::notice("AssignAgent dispatched for room: {$roomQueue->room_id}");
    }

    /**
     * Handle the RoomQueue "updated" event.
     */
    public function updated(RoomQueue $roomQueue): void
    {
        $nextRoom = RoomQueue::where('status', 'queued')->orderBy('created_at', 'asc')->first();

        if ($nextRoom) {
            AssignAgent::dispatch($nextRoom->room_id);
            Log::notice("AssignAgent dispatched for next room: {$nextRoom->room_id}");
        } else {
            Log::notice("There are no rooms left to serve.");
        }
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

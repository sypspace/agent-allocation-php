<?php

namespace App\Observers;

use App\Jobs\AssignAgent;
use App\Jobs\FallbackRoomAssignment;
use App\Models\RoomQueue;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;

class RoomQueueObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the RoomQueue "created" event.
     */
    public function created(RoomQueue $roomQueue): void
    {
        Log::notice("New message, room: {$roomQueue->room_id} added to queue");

        // AssignAgent::dispatch($roomQueue->room_id)->withoutDelay()->afterCommit();
        FallbackRoomAssignment::dispatch()->withoutDelay()->afterCommit();
    }

    /**
     * Handle the RoomQueue "updated" event.
     */
    public function updated(RoomQueue $roomQueue): void
    {
        Log::notice("New notif, room {$roomQueue->room_id} has been resolved");

        FallbackRoomAssignment::dispatch()->withoutDelay()->afterCommit();

        // $nextRoom = $roomQueue->next;

        // if ($nextRoom) {
        //     AssignAgent::dispatch($nextRoom->room_id)->withoutDelay()->afterCommit();
        //     Log::notice("AssignAgent dispatched for next room: {$nextRoom->room_id}");
        // } else {
        //     Log::notice("There are no rooms left to serve.");
        // }
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

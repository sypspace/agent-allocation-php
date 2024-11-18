<?php

namespace App\Jobs;

use App\Models\RoomQueue;
use App\Services\QiscusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

class FallbackRoomAssignment implements ShouldQueue
{
    use Queueable;

    protected $id;
    /**
     * Ada kemungkinan server Qiscus gagal kirim data melalui webhook. 
     * Kita bikin job buat cek daftar room yg belum masuk antrian.
     */
    public function __construct()
    {
        Log::notice("Fallback RoomAssignment job started");
        $this->id = strtotime(date('Y-m-d H:') . ceil(date('i') / 5) * 5);
    }

    /**
     * Execute the job.
     */
    public function handle(QiscusService $qiscus): void
    {
        // Pantau daftar room yang belum dilayani
        $status = "unserved";
        $custRooms = $qiscus->getCustomerRooms($status);

        if (config('app.debug'))
            Log::debug("Customer Rooms:", $custRooms);

        // Queue Rule FIFO: 
        // Karena list customer rooms urutannya "descending" (tidak bisa diubah: filter tidak berfungsi). 
        // So, sort ascending
        $sourceRooms = $custRooms->sortByDesc('last_customer_timestamp')->pluck('room_id');

        // Cek room sudah masuk antrian atau belum
        $queueRooms = RoomQueue::where('status', $status)->orderBy('created_at', 'asc')->pluck('room_id');

        $unservedRooms = $sourceRooms->merge($queueRooms)->unique()->values();

        // Masukkan room ke daftar antrian dan jalankan job assignment
        $unservedRooms->each(function ($room) {
            RoomQueue::withoutEvents(function () use ($room) {
                $newRoom = RoomQueue::firstOrCreate(['room_id' => $room]);

                if ($newRoom) {
                    AssignAgent::dispatch($newRoom->room_id)->withoutDelay()->afterCommit();
                    Log::notice("AssignAgent dispatched for new room: {$newRoom->room_id}");
                } else {
                    Log::warning("Failed to add room: {$newRoom->room_id} to the queue.");
                }
            });
        });

        if (count($unservedRooms) > 0)
            Log::info("Fallback jobs adds " . count($unservedRooms) . " room(s) to the queue");


        if (config('app.debug'))
            Log::debug("Data:", compact('custRooms', 'sourceRooms', 'queueRooms', 'unservedRooms'));
    }


    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->id;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->id))->dontRelease()->expireAfter(300)];
    }
}

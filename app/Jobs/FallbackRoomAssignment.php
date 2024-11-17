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

    /**
     * Ada kemungkinan server Qiscus gagal kirim data melalui webhook. 
     * Kita bikin job buat cek daftar room yg belum masuk antrian.
     */
    public function __construct()
    {
        Log::notice("Fallback RoomAssignment job started");
    }

    /**
     * Execute the job.
     */
    public function handle(QiscusService $qiscus): void
    {
        // Pantau daftar room yang belum dilayani
        $status = "unserved";
        $custRooms = $qiscus->getCustomerRooms($status);

        // Queue Rule FIFO: 
        // Karena list customer rooms urutannya "descending" (tidak bisa diubah: filter tidak berfungsi). 
        // So, sort ascending
        $sourceRooms = $custRooms->sortByDesc('last_customer_timestamp')->pluck('room_id');

        // Cek room sudah masuk antrian atau belum
        $queueRooms = RoomQueue::whereIn('room_id', $sourceRooms)->pluck('room_id');

        $nonExistRooms = $sourceRooms->diff($queueRooms)->values();

        // Masukkan room ke daftar antrian dan jalankan job assignment
        $nonExistRooms->each(function ($room) {
            RoomQueue::create(['room_id' => $room]);
        });

        if (count($nonExistRooms) > 0)
            Log::info("Fallback jobs adds " . count($nonExistRooms) . " room(s) to the queue");
        else
            Log::debug("Data:", compact('custRooms', 'sourceRooms', 'queueRooms', 'nonExistRooms'));
    }
}

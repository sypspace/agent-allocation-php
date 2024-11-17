<?php

namespace App\Jobs;

use App\Models\RoomQueue;
use App\Services\QiscusService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

class AssignAgent implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $room_id = null
    ) {
        Log::info("Running AssignAgent job instance", ['room_id' => $this->room_id]);
    }

    /**
     * Execute the job.
     */
    public function handle(QiscusService $qiscus): void
    {
        try {
            // Ambil detail room dari API server
            $room = $qiscus->getRoomById($this->room_id);
            $room = $room['customer_room'];

            if ($room['is_waiting'] && !$room['is_resolved']) {
                // Cari agen yang free dari API server
                $agent = $qiscus->allocateAgent($room['source'], $room['channel_id']);

                if ($agent) {
                    // Pilih agent yang free & jumlah customer yg sedang dilayani < QISCUS_MAX_CUSTOMER
                    // Jika agent jumlah customer yg sedang dihandle kurang dari limit, assign room ke agent ini
                    if ($agent['count'] < env('QISCUS_MAX_CUSTOMER')) {

                        // Call API untuk assign agent ke room
                        $assignedAgent = $qiscus->assignAgent($room['room_id'], $agent['id']);

                        if ($assignedAgent && $assignedAgent['added_agent']) {
                            RoomQueue::where('room_id', $room['id'])->update(['status' => 'served']);
                            Log::info("Successfully assigned agent {$agent['name']} to room {$this->room_id}.");
                        } else {
                            Log::warning("Failed to assign agent to room {$this->room_id}. Retrying...");
                            $this->release();
                        }
                    } else {
                        Log::notice("No available agents found. Retrying...");
                        $this->release();
                    }
                } else {
                    Log::notice("There are no agents online. Retrying...");
                    $this->release();
                }
            } else {
                Log::info("Room {$this->room_id} is already served or resolved.");
                RoomQueue::where('room_id', $this->room_id)->update(['status' => 'served']);
            }
        } catch (\Exception $e) {
            Log::error("Error in AssignAgent Job: " . $e->getMessage());
            $this->fail("Error in AssignAgent Job: " . $e->getMessage());
        }
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->room_id))->dontRelease()->expireAfter(180)];
    }
}

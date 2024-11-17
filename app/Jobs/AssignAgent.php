<?php

namespace App\Jobs;

use App\Models\RoomQueue;
use App\Services\QiscusService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AssignAgent implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Karena tidak ada (belum menemukan) cara filter agent yang free, jadi kita ambil semua aja.
     */
    protected $agent_count = 100;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $room_id = null
    ) {
        // 
    }

    /**
     * Execute the job.
     */
    public function handle(QiscusService $qiscus): void
    {
        try {
            Log::info("Running new AssignAgent job instance", ['room_id' => $this->room_id]);

            // Ambil detail room dari API server
            $room = $qiscus->getRoomById($this->room_id);
            $room = $room['customer_room'];

            if ($room['is_waiting'] && !$room['is_resolved']) {
                // Ambil agen yang tersedia dari API server
                $availableAgents = $qiscus->getAvailableAgents($this->room_id, false, $this->agent_count);
                $availableAgents = $availableAgents['agents'];

                if (!empty($availableAgents)) {
                    // Pilih agent yang free & jumlah customer yg sedang dilayani < QISCUS_MAX_CUSTOMER
                    foreach ($availableAgents as $agent) {
                        // Jika agent jumlah customer yg sedang dihandle kurang dari limit, assign room ke agent ini
                        if ($agent['current_customer_count'] < env('QISCUS_MAX_CUSTOMER')) {

                            // Call API untuk assign agent ke room
                            $assignedAgent = $qiscus->assignAgent($room['room_id'], $agent['id']);

                            if ($assignedAgent) {
                                RoomQueue::where('room_id', $this->room_id)->update(['status' => 'served']);
                                Log::info("Successfully assigned agent {$agent['name']} to room {$this->room_id}.");
                            } else {
                                Log::warning("Failed to assign agent to room {$this->room_id}. Retrying...");
                                $this->release();
                            }
                        }
                    }
                } else {
                    Log::notice("No available agents found. Retrying...");
                    $this->release(5);
                }
            } else {
                Log::info("Room {$this->room_id} is already served or resolved.");
                RoomQueue::where('room_id', $this->room_id)->update(['status' => 'resolved']);
            }
        } catch (\Exception $e) {
            Log::error("Error in AssignAgent Job: " . $e->getMessage());
            $this->release(5);
        }
    }
}

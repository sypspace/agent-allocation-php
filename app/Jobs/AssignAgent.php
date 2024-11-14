<?php

namespace App\Jobs;

use App\Models\Room;
use App\Services\QiscusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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
     * Karena tidak ada (belum menemukan) cara filter agent yang free, jadi kita tentukan aja disini.
     */
    protected $agent_count = 100;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $qiscus = new QiscusService();

        // Ambil antrian pertama masuk
        $room = Room::UnResolved()->OrderByCreatedDate()->first();

        $qRoom = $qiscus->getRoomById($room->room_id);

        // Cek room sudah ada agent yang handle atau belum
        if ($qRoom['data']['customer_room']['is_waiting'] === true) {

            // Cari agent yang free
            $availAgents = $qiscus->getAvailableAgents($room->room_id, false, $this->agent_count);

            if ($availAgents->data) {

                $agents = $availAgents->data->agents;

                if (sizeof($agents) > 0) {
                    foreach ($agents as $agent) {
                        // Jika agent jumlah customer yg sedang dihandle kurang dari limit, assign room ke agent ini
                        if ($agent->current_customer_count < env('QISCUS_MAX_CUSTOMER')) {

                            // Call API untuk assign room ke agent
                            $assignedAgent = $qiscus->assignAgent($room->room_id, $agent->id);

                            Log::info("AssignAgent Job: " . $assignedAgent->added_agent->name . " assigned to room " . $room->room_id, ['params' => ['id' => $room->room_id]]);
                        }
                    }
                } else {
                    // Skip. Tidak ada agent yang Online/Ready untuk bisa handle customer
                    Log::warning("AssignAgent Job: Skiped! Unable to found free agent for room " . $room->room_id, ['params' => ['id' => $room->room_id]]);
                }
            }
        } else {
            // Skip. Room sudah di-handle seorang agent
            Log::warning("AssignAgent Job: Skiped! Room " . $room->room_id . " has been handled.", ['params' => ['id' => $room->room_id]]);
        }
    }
}

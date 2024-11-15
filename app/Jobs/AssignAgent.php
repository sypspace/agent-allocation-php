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

        // Queue Rule FIFO: 
        // Karena list customer rooms urutannya "descending" (tidak bisa diubah: filter tidak berfungsi). 
        // So, ambil room yg ada di akhir
        $rooms = $qiscus->getCustomerRooms();
        $room = $rooms->last();

        // Cek room sudah ada agent yang handle atau belum
        if ($room['is_waiting'] === true) {

            // Cari agent yang free
            $availAgents = $qiscus->getAvailableAgents($room['room_id'], false, $this->agent_count);
            $availAgents = $availAgents['data'];

            if ($availAgents) {

                $agents = $availAgents['agents'];

                if (sizeof($agents) > 0) {
                    foreach ($agents as $agent) {
                        // Jika agent jumlah customer yg sedang dihandle kurang dari limit, assign room ke agent ini
                        if ($agent->current_customer_count < env('QISCUS_MAX_CUSTOMER')) {

                            // Call API untuk assign room ke agent
                            $assignedAgent = $qiscus->assignAgent($room['room_id'], $agent['id']);

                            Log::info(
                                "AssignAgent Job: " . $assignedAgent['added_agent']['name'] . " assigned to room " . $room['room_id'],
                                [
                                    'params' => [
                                        'room_id' => $room['room_id'],
                                        'agent' => $agent
                                    ]
                                ]
                            );
                        }
                    }
                } else {
                    // Skip. Tidak ada agent yang Online/Ready untuk bisa melayani customer
                    Log::warning(
                        "AssignAgent Job: Skiped! Unable to found free agent.",
                        [
                            'params' => ['id' => $room->room_id]
                        ]
                    );
                }
            }
        } else {
            // Skip. Room sudah di-handle seorang agent
            Log::warning(
                "AssignAgent Job: Skiped! Room " . $room->room_id . " has been served.",
                [
                    'params' => ['room_id' => $room->room_id]
                ]
            );
        }
    }
}

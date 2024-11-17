<?php

namespace App\Http\Controllers;

use App\Jobs\AssignAgent;
use App\Models\ResolveNotif;
use App\Models\Room;
use App\Models\RoomQueue;
use App\Services\QiscusService;
use App\Services\ResponseHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    public function agentAllocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_id' => 'required',
            'room_id' => 'required',
            'source' => 'required',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed: ' . $validator->errors()->first());
            return ResponseHandler::error($validator->errors()->first());
        }

        if (!RoomQueue::where('room_id', $request->room_id)->exists()) {
            RoomQueue::create(['room_id' => $request->room_id]);

            Log::notice("New message, room: {$request->room_id} added to queue");

            AssignAgent::dispatch($request->room_id);

            return ResponseHandler::success("Room {$request->room_id} added to queue.");
        }

        return ResponseHandler::success("Room {$request->room_id} is already in queue.");
    }

    public function markAsResolved(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer' => 'required',
            'resolved_by' => 'required',
            'service' => 'required',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed: ' . $validator->errors()->first());
            return ResponseHandler::error($validator->errors()->first());
        }

        $room_id = $request->service['room_id'];
        $agent_id = $request->resolved_by['id'];

        $room = RoomQueue::where('room_id', $room_id)->update(['agent_id' => $agent_id, 'status' => 'resolved']);

        $nextRoom = RoomQueue::where('status', 'queued')->orderBy('created_at', 'asc')->first();

        if ($nextRoom) {
            AssignAgent::dispatch($room->room_id);
            Log::notice("AssignAgent dispatched for next room: {$nextRoom->room_id}");
        } else {
            Log::notice("There are no rooms left to serve.");
        }

        return ResponseHandler::success("Room {$room_id} marked as resolved");
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\AssignAgent;
use App\Models\ResolveNotif;
use App\Models\Room;
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
            Log::info('agentAllocation errors: ' . $validator->errors()->first());
            return ResponseHandler::error($validator->errors()->first());
        }

        $app_id         = $request->app_id;
        $name           = $request->name;
        $email          = $request->email;
        $avatar_url     = $request->avatar_url;
        $room_id        = $request->room_id;
        $source         = $request->source;
        $is_new_session = $request->is_new_session;
        $is_resolved    = $request->is_resolved;
        $latest_service = $request->latest_service;
        $extras         = $request->extras;
        $candidate_agent = $request->candidate_agent;

        // Jika room tidak ditemukan, create a new queue otherwise update existing
        $room = Room::upsert(
            [compact('app_id', 'name', 'email', 'avatar_url', 'room_id', 'source', 'is_new_session', 'is_resolved', 'latest_service', 'extras', 'candidate_agent')],
            ['room_id'],
            ['avatar_url', 'is_new_session', 'is_resolved', 'latest_service', 'extras', 'candidate_agent']
        );

        AssignAgent::dispatch();

        return ResponseHandler::success('Successfully received.');
    }

    public function markAsResolved(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer' => 'required',
            'resolved_by' => 'required',
            'service' => 'required',
        ]);

        if ($validator->fails()) {
            Log::info('agentAllocation errors: ' . $validator->errors()->first());
            return ResponseHandler::error($validator->errors()->first());
        }

        $notif = ResolveNotif::create(['customer' => $request->customer, 'resolved_by' => $request->resolved, 'service' => $request->service]);

        $room = Room::where('room_id', $request->service['room_id'])->first();
        $room->is_new_session = false;
        $room->is_resolved = true;
        $room->save();

        Log::info('MarkAsResolved API: Receive resolve notification.', ['params' => $request->all()]);

        AssignAgent::dispatch();

        return ResponseHandler::success('Room marked as resolved');
    }
}

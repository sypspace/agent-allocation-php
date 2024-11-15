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

        AssignAgent::dispatch();

        return ResponseHandler::success('Room marked as resolved');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\QiscusService;
use App\Services\ResponseHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function getMaxCustomers(Request $request)
    {
        $max_customers = Setting::where('key', 'max_customers')->first('value');

        $output = [
            'max_customers' => (int) $max_customers->value
        ];

        return ResponseHandler::success('Success', $output);
    }

    public function updateMaxCustomers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            Log::info('updateMaxCustomers errors: ' . $validator->errors()->first());
            return ResponseHandler::error($validator->errors()->first());
        }

        $setting = Setting::upsert([
            ['key' => 'max_customers', 'value' => $request->value]
        ], ['key'], ['value']);

        $output = [
            'max_customers' => $request->value
        ];

        return ResponseHandler::success('Sucessfully updated', $output);
    }

    public function setMarkAsResolved(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required',
            'enable' => 'nullable|boolean',
        ]);

        $qiscus = new QiscusService();
        $response = $qiscus->setMarkAsResolvedWebhook($request->endpoint, ($request->enable) ?? false);

        $output = [
            'mark_as_resolved_webhook_url' => $response['data']['mark_as_resolved_webhook_url'],
            'is_allocate_agent_webhook_enabled' => $response['data']['is_allocate_agent_webhook_enabled']
        ];

        return ResponseHandler::success('Successfully updated', $output);
    }
}

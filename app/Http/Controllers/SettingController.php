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
        return ResponseHandler::success($max_customers);
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

        Setting::upsert([
            ['key' => 'max_customers', 'value' => $request->value]
        ], ['key'], ['value']);

        return ResponseHandler::success('Sucessfully updated');
    }

    public function setMarkAsResolved(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required',
            'enable' => 'nullable'
        ]);

        $qiscus = new QiscusService();
        $qiscus->setMarkAsResolvedWebhook($request->endpoint, $request->enable);
    }
}

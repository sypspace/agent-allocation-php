<?php

use App\Http\Controllers\SettingController;
use App\Http\Controllers\WebhookController;
use App\Jobs\FallbackRoomAssignment;
use App\Services\ResponseHandler;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json('Hello Qiscus!');
});
Route::prefix('/v1')->group(function () {
    Route::post('webhook/agent_allocation', [WebhookController::class, 'agentAllocation'])->name('webhook.agentAllocation');
    Route::post('webhook/mark_as_resolved', [WebhookController::class, 'markAsResolved'])->name('webhook.markAsResolved');

    Route::get('settings/max_customers', [SettingController::class, 'getMaxCustomers'])->name('settings.maxCustomers.get');
    Route::post('settings/max_customers', [SettingController::class, 'updateMaxCustomers'])->name('settings.maxCustomers.update');
    Route::post('settings/set_mark_as_resolved', [SettingController::class, 'setMarkAsResolved'])->name('settings.setMarkAsResolved.update');

    Route::post('settings/reassgin', function () {
        FallbackRoomAssignment::dispatchAfterResponse();
        return ResponseHandler::success('Re-assigned');
    });
});

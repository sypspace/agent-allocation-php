<?php

namespace App\Providers;

use App\Jobs\AssignAgent;
use App\Jobs\FallbackRoomAssignment;
use App\Models\RoomQueue;
use App\Observers\RoomQueueObserver;
use App\Services\QiscusService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Http::macro('qiscus', function () {
            return Http::withHeaders([
                'Qiscus-App-Id' => env('QISCUS_APP_ID')
            ])->baseUrl(env('QISCUS_BASE_URL', 'https://multichannel.qiscus.com'));
        });

        $this->app->bindMethod([FallbackRoomAssignment::class, 'handle'], function (FallbackRoomAssignment $job, Application $app) {
            return $job->handle($app->make(QiscusService::class));
        });

        $this->app->bindMethod([AssignAgent::class, 'handle'], function (AssignAgent $job, Application $app) {
            return $job->handle($app->make(QiscusService::class));
        });

        RoomQueue::observe(RoomQueueObserver::class);
    }
}

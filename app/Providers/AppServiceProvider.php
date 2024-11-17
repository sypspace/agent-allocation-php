<?php

namespace App\Providers;

use App\Models\RoomQueue;
use App\Observers\RoomQueueObserver;
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

        RoomQueue::observe(RoomQueueObserver::class);
    }
}

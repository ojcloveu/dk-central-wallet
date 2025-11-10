<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Listeners\AccessTokenCreatedListener;
use Laravel\Passport\Events\AccessTokenCreated;

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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }

        // you might register a closure to rollback any transactions that were left open by a previously failed job
        Queue::looping(function () {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        });

        RateLimiter::for('heavy_client_api', function ($request) {
            return [Limit::perMinute(1000)->by('heavy_client_api:'.$request->ip())];
        });

        // Events
        Event::listen(AccessTokenCreated::class, AccessTokenCreatedListener::class); // revoke user old token
    }
}

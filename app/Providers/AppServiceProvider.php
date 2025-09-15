<?php

namespace App\Providers;

use App\Http\Services\Message\MessageService;
use App\Http\Services\Message\SMS\SmsService;
use App\Http\Services\Notification\SubscriptionUsageManagerService;
use App\Repositories\Contracts\Market\SubscriptionDefaultUsageRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->bind(SubscriptionUsageManagerService::class, function ($app) {
        //     $user = Auth::user(); // یا می‌تونی $app['auth']->user() هم بگیری
        //     return new SubscriptionUsageManagerService(
        //         $user,
        //         $app->make(SubscriptionDefaultUsageRepositoryInterface::class)
        //     );
        // });
        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService(); // یا با پیکربندی لازم
        });

        $this->app->singleton(MessageService::class, function ($app) {
            return new MessageService($app->make(SmsService::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

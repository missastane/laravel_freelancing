<?php

namespace App\Providers;

use App\Http\Services\Auth\LoginChallengeService;
use App\Http\Services\Message\MessageService;
use App\Http\Services\Message\SMS\SmsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower(trim((string) $request->input('email')));

            return [
                Limit::perMinute(20)
                    ->by('login:ip:' . $request->ip()),

                Limit::perMinute(5)
                    ->by('login:email:' . hash('sha256', $email)),
            ];
        });

        RateLimiter::for('two-factor-login', function (Request $request) {
            $challengeToken = (string) $request->input('challenge_token');

            $challengeService = app(LoginChallengeService::class);

            $userId = $challengeToken !== ''
                ? $challengeService->getUserIdByToken($challengeToken)
                : null;

            $userId ??= 'unknown';
            return [
                Limit::perMinute(30)
                    ->by('two-factor-login:ip:' . $request->ip()),

                Limit::perMinute(10)
                    ->by('two-factor-login:user:' . $userId),
            ];
        });

        RateLimiter::for('account-deletion-request', function (Request $request) {
            return Limit::perDay(3)
                ->by('account-deletion-request:user:' . $request->user()->id);
        });

        RateLimiter::for('account-deletion-cancel', function (Request $request) {
            return Limit::perDay(5)
                ->by('account-deletion-cancel:user:' . $request->user()->id);
        });
    }
}

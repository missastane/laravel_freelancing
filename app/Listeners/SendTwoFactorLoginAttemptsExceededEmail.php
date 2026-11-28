<?php

namespace App\Listeners;

use App\Events\TwoFactorLoginAttemptsExceeded;
use App\Http\Services\Message\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTwoFactorLoginAttemptsExceededEmail implements ShouldQueue
{
    public function handle(TwoFactorLoginAttemptsExceeded $event): void
    {
        $user = $event->user;
        $challenge = $event->challenge;

        $locale = $user->locale ?? config('app.locale');

        $emailService = app(EmailService::class);

        $emailService->setTo($user->email);
        $emailService->setLocale($locale);

        $emailService->setTranslatedSubject(
            'emails.security.two_factor_login_attempts_exceeded.subject',
            ['name' => $user->user_name]
        );

        $emailService->setView(
            'emails.security.two-factor-login-attempts-exceeded'
        );

        $emailService->setDetails([
            'name' => $user->user_name,
            'attempted_at' => now()->locale($locale)->translatedFormat('Y/m/d H:i'),
            'ip_address' => $challenge['ip_address'] ?? null,
            'device' => $challenge['user_agent'] ?? null,
            'client_type' => $challenge['client_type'] ?? 'web',
        ]);

        $emailService->fire();
    }
}
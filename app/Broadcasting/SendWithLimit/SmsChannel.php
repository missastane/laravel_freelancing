<?php

namespace App\Broadcasting\SendWithLimit;

use App\Http\Services\Message\MessageService;
use App\Http\Services\Message\SMS\SmsService;
use App\Http\Services\Notification\SubscriptionUsageManagerService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    public function __construct(
        protected SubscriptionUsageManagerService $service,
        protected MessageService $messageService,
        protected SmsService $smsService
    ) {
    }
    public function send($notifiable, Notification $notification)
    {
        if (!$this->service->canUse('sms')) {
            return null;
        }
        if (!method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);
        if (!$message) {
            return;
        }

        try {
            $this->smsService->setFrom(Config::get('sms.otp_from'));
            $this->smsService->setTo(['0' . $notifiable->mobile]);
            $this->smsService->setText($message);
            $this->smsService->setIsFlash(true);
            $this->messageService->send();
            $this->service->increamentUsage('sms');
        } catch (\Throwable $e) {
            Log::error('SmsChannel error: ' . $e->getMessage());
        }
    }
}

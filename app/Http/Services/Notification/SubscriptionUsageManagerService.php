<?php

namespace App\Http\Services\Notification;

use App\Models\Market\SubscriptionUsage;
use App\Models\Market\UserSubscription;
use App\Models\User\User;
use App\Repositories\Contracts\Market\SubscriptionDefaultUsageRepositoryInterface;

class SubscriptionUsageManagerService
{
    protected User $user;
    protected ?UserSubscription $activeSubscription = null;
    protected ?SubscriptionUsage $currentUsage = null;

    public function __construct(
        User $user,
        protected SubscriptionDefaultUsageRepositoryInterface $subscriptionDefaultUsageRepository
    ) {
        $this->user = $user;
    }

    protected function initSubscriptionAndUsage(): void
    {
        if ($this->activeSubscription && $this->currentUsage) {
            return;
        }

        $this->activeSubscription = $this->user->activeSubscription();

        if ($this->activeSubscription) {
            // پلن فعال دارد
            $subscriptionPeriodStart = $this->activeSubscription->start_date;
            $subscriptionPeriodEnd   = $this->activeSubscription->end_date;

            $this->currentUsage = $this->activeSubscription->defaultUsage()
                ->where('period_start', $subscriptionPeriodStart)
                ->where('period_end', $subscriptionPeriodEnd)
                ->first();

            if (!$this->currentUsage) {
                $this->currentUsage = $this->subscriptionDefaultUsageRepository->create([
                    'user_subscription_id' => $this->activeSubscription->id,
                    'user_id'              => $this->user->id,
                    'period_start'         => $subscriptionPeriodStart,
                    'period_end'           => $subscriptionPeriodEnd,
                ]);
            }
        } else {
            // بدون پلن
            $this->currentUsage = SubscriptionUsage::where('user_id', $this->user->id)
                ->where('period_start', '<=', now())
                ->where('period_end', '>=', now())
                ->first();

            if (!$this->currentUsage) {
                $this->currentUsage = $this->subscriptionDefaultUsageRepository->create([
                    'user_id'              => $this->user->id,
                    'user_subscription_id' => null,
                    'period_start'         => now(),
                    'period_end'           => now()->addDays(30),
                ]);
            }
        }
    }

    public function canUse(string $type): bool
    {
        $this->initSubscriptionAndUsage();

        if (!$this->currentUsage) {
            return false;
        }

        if (!$this->activeSubscription) {
            // حالت بدون پلن
            return match ($type) {
                // Notifications
                'sms'          => false,
                'email'        => false,
                'notification' => $this->currentUsage->send_notification_count < PHP_INT_MAX,

                // Projects / Proposals
                'target_create' => $this->currentUsage->target_create_count < 10,
                'view_details'  => $this->user->active_role === 'employer' ? true:false,

                default => false,
            };
        }

        // حالت با پلن
        return match ($type) {
            // Notifications
            'sms'          => $this->currentUsage->send_sms_count < $this->activeSubscription->getSmsLimit(),
            'email'        => $this->currentUsage->send_email_count < $this->activeSubscription->getEmailLimit(),
            'notification' => $this->currentUsage->send_notification_count < $this->activeSubscription->getNotificationLimit(),

            // Projects / Proposals
            'target_create' => $this->currentUsage->target_create_count < $this->activeSubscription->getTargetCreateLimit(),
            'view_details'  => $this->currentUsage->view_details_count < $this->activeSubscription->getViewDetailsLimit(),

            default => false,
        };
    }

    public function increamentUsage(string $type): void
    {
        if (!$this->currentUsage) {
            return;
        }

        match ($type) {
            'sms'            => $this->subscriptionDefaultUsageRepository->increamentUsage($this->currentUsage, 'send_sms_count'),
            'email'          => $this->subscriptionDefaultUsageRepository->increamentUsage($this->currentUsage, 'send_email_count'),
            'notification'   => $this->subscriptionDefaultUsageRepository->increamentUsage($this->currentUsage, 'send_notification_count'),
            'target_create'  => $this->subscriptionDefaultUsageRepository->increamentUsage($this->currentUsage, 'target_create_count'),
            'view_details'   => $this->subscriptionDefaultUsageRepository->increamentUsage($this->currentUsage, 'view_details_count'),
            default => null,
        };
    }
}
<?php

namespace App\Http\Services\Subscription;

use App\Models\User\User;

class PlanFeatureChecker
{
    public static function check(User $user, string $targetType, string $featureKey)
    {
        $subscription = $user->activeSubscription($targetType);
        if (!$subscription) return false;

        $limit = $subscription->getFeature($featureKey);
        if ($limit === null) return true; // we don't have a limit
        $used = match ($featureKey) {
            '' => '',
            default => 0,
        };

        return $used < $limit;
    }


}
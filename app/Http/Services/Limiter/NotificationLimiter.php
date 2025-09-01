<?php

namespace App\Http\Services\Limiter;
use App\Models\User\User;
use Illuminate\Support\Carbon;
class NotificationLimiter
{
    protected User $user;
    protected string $role;
    protected ?string $notificationType = null;
    protected string $featureKey;
    protected \Closure $callback;

    public static function for(User $user): self
    {
        $instance = new self();
        $instance->user = $user;
        return $instance;
    }

    public function role(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function type(string $notificationType): self
    {
        $this->notificationType = $notificationType;
        return $this;
    }

    public function check(string $featureKey): self
    {
        $this->featureKey = $featureKey;
        return $this;
    }

    public function thenSend(\Closure $callback): void
    {
        $this->callback = $callback;

        if ($this->allowed()) {
            call_user_func($this->callback);
        }
    }

    protected function allowed(): bool
    {
        $subscription = $this->user->activeSubscription($this->role);

        if (!$subscription)
            return false;

        $limit = $subscription->getFeature($this->featureKey);
        if ($limit === null)
            return true;

        $used = match ($this->featureKey) {
            'max_notifications_per_day' => $this->user->notifications()
                ->whereDate('created_at', Carbon::today())
                ->count(),

            'max_project_alerts_per_day' => $this->user->notifications()
                ->where('type', $this->notificationType)
                ->whereDate('created_at', Carbon::today())
                ->count(),

            // add new features here
            default => 0,
        };

        return $used < $limit;
    }

   

}
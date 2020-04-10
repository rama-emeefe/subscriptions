<?php

namespace Emeefe\Subscriptions\Events;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Emeefe\Subscriptions\Models\PlanSubscription;

class CancelSubscription {
    use Dispatchable, SerializesModels;

    public $subscription;
    public $reason;

    public function __construct(PlanSubscription $subscription, $reason)
    {
        $this->subscription = $subscription;
        $this->reason = $reason;
    }
}
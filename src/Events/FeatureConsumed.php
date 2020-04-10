<?php

namespace Emeefe\Subscriptions\Events;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Emeefe\Subscriptions\Models\PlanSubscription;

class FeatureConsumed {
    use Dispatchable, SerializesModels;

    public $subscription;
    public $model;
    public $units;

    public function __construct(PlanSubscription $subscription, $model, int $units)
    {
        $this->subscription = $subscription;
        $this->model = $model;
        $this->units = $units;
    }
}
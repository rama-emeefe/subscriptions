<?php

namespace Emeefe\Subscriptions\Events;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Emeefe\Subscriptions\Models\PlanSubscription;

class UpdatedSubscription {
    use Dispatchable, SerializesModels;

    public $model;
    public $oldSubscription;
    public $subscription;

    public function __construct($model, PlanSubscription $oldSubscription, PlanSubscription $subscription)
    {
        $this->model = $model;
        $this->oldSubscription = $oldSubscription;
        $this->subscription = $subscription;
    }
}
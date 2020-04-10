<?php

namespace Emeefe\Subscriptions\Events;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Emeefe\Subscriptions\Models\PlanSubscription;

class NewSubscription {
    use Dispatchable, SerializesModels;

    public $model;
    public $subscription;

    public function __construct($model, PlanSubscription $subscription)
    {
        $this->model = $model;
        $this->subscription = $subscription;
    }
}
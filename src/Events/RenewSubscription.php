<?php

namespace Emeefe\Subscriptions\Events;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Emeefe\Subscriptions\Models\PlanSubscription;

class RenewSubscription {
    use Dispatchable, SerializesModels;

    public $model;
    public $subscription;
    public $cycles;

    public function __construct($model, PlanSubscription $subscription, int $cycles)
    {
        $this->model = $model;
        $this->subscription = $subscription;
        $this->cycles = $cycles;
    }
}
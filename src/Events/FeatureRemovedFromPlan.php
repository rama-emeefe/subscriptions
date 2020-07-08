<?php

namespace Emeefe\Subscriptions\Events;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Emeefe\Subscriptions\Models\Plan;
use Emeefe\Subscriptions\Models\PlanFeature;

class FeatureRemovedFromPlan {

    use Dispatchable, SerializesModels;

    public $plan;
    public $feature;

    public function __construct(Plan $plan, PlanFeature $feature)
    {
        $this->plan = $plan;
        $this->feature = $feature;
    }
}
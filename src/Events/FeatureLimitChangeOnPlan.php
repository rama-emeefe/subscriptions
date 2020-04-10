<?php

namespace Emeefe\Subscriptions\Events;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Emeefe\Subscriptions\Models\Plan;
use Emeefe\Subscriptions\Models\PlanFeature;



class FeatureLimitChangeOnPlan {

    use Dispatchable, SerializesModels;

    public $plan;
    public $feature;
    public $limit;

    public function __construct(Plan $plan, PlanFeature $feature, int $limit)
    {
        $this->plan = $plan;
        $this->feature = $feature;
        $this->limit = $limit;
    }
}
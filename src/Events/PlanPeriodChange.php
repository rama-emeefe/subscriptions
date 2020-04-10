<?php

namespace Emeefe\Subscriptions\Events;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Emeefe\Subscriptions\Models\PlanPeriod;


class PlanPeriodChange {
    use Dispatchable, SerializesModels;

    public $oldPlanPeriod;
    public $newPlanPeriod;

    public function __construct(PlanPeriod $oldPlanPeriod, PlanPeriod $newPlanPeriod)
    {
        $this->oldPlanPeriod = $oldPlanPeriod;
        $this->newPlanPeriod = $newPlanPeriod;
    }
}
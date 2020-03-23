<?php

namespace Emeefe\Subscriptions\Observers;
use Emeefe\Subscriptions\Models\PlanPeriod;

class PlanPeriodObserver
{
    /**
     * Handle the planPeriod "saving" event.
     *
     * @param  PlanPeriod  $planPeriod
     * @return void
     */
    public function saving(PlanPeriod $planPeriod)
    {
        if($planPeriod->isDefault()) {
            $oldDefaultPlanPeriod = $planPeriod->plan->periods()->where('is_default', 1)->first();
            if($oldDefaultPlanPeriod){
                $oldDefaultPlanPeriod->is_default = false;
                $oldDefaultPlanPeriod->save();
            }
        }
    }

    /**
     * Handle the plan "created" event.
     *
     * @param  PlanPeriod  $planPeriod
     * @return void
     */
    public function created(PlanPeriod  $planPeriod)
    {
        //
    }

    /**
     * Handle the plan "updated" event.
     *
     * @param  PlanPeriod  $planPeriod
     * @return void
     */
    public function updated(PlanPeriod  $planPeriod)
    {
        //
    }

    /**
     * Handle the plan "deleted" event.
     *
     * @param  PlanPeriod  $planPeriod
     * @return void
     */
    public function deleted(PlanPeriod  $planPeriod)
    {
        //
    }

    /**
     * Handle the plan "restored" event.
     *
     * @param  PlanPeriod  $planPeriod
     * @return void
     */
    public function restored(PlanPeriod  $planPeriod)
    {
        //
    }

    /**
     * Handle the plan "force deleted" event.
     *
     * @param  PlanPeriod  $planPeriod
     * @return void
     */
    public function forceDeleted(PlanPeriod  $planPeriod)
    {
        //
    }
}
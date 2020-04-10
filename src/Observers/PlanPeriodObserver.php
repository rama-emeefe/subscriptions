<?php

namespace Emeefe\Subscriptions\Observers;
use Emeefe\Subscriptions\Models\PlanPeriod;
use Emeefe\Subscriptions\Events\PlanPeriodChange;


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
     * Handle the plan "updating" event.
     *
     * @param  PlanPeriod  $planPeriod
     * @return void
     */
    public function updating(PlanPeriod  $planPeriod)
    {
        $oldPlanPeriod = $planPeriod->plan->periods()->where('id', $planPeriod->id)->first();

        if($oldPlanPeriod) {
            $oldPlanPeriodString = $oldPlanPeriod->price
                                    .$oldPlanPeriod->currency
                                    .$oldPlanPeriod->trial_days
                                    .$oldPlanPeriod->period_unit
                                    .$oldPlanPeriod->period_count
                                    .$oldPlanPeriod->is_recurring
                                    .$oldPlanPeriod->is_visible
                                    .$oldPlanPeriod->tolerance_days;
            $newPlanPeriodString = $planPeriod->price
                                    .$planPeriod->currency
                                    .$planPeriod->trial_days
                                    .$planPeriod->period_unit
                                    .$planPeriod->period_count
                                    .$planPeriod->is_recurring
                                    .$planPeriod->is_visible
                                    .$planPeriod->tolerance_days;
            if ($oldPlanPeriodString != $newPlanPeriodString) {
                event(new PlanPeriodChange($oldPlanPeriod, $planPeriod));
            }
        }
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
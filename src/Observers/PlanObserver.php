<?php

namespace Emeefe\Subscriptions\Observers;
use Emeefe\Subscriptions\Models\Plan;

class PlanObserver
{
    /**
     * Handle the plan "saving" event.
     *
     * @param  Plan  $plan
     * @return void
     */
    public function saving(Plan $plan)
    {
        // dd('From PlanObserver', $plan);
        // \Log::info(print_r($plan, true));
        if($plan->isDefault()) {
            $oldDefaultPlan = $plan->type->plans()->where('is_default', 1)->first();
            if($oldDefaultPlan){
                $oldDefaultPlan->is_default = false;
                $oldDefaultPlan->save();
                // dd('old plan default', $oldPlan->isDefault());
            }
        }
    }

    /**
     * Handle the plan "created" event.
     *
     * @param  Plan  $plan
     * @return void
     */
    public function created(Plan $plan)
    {
        //
    }

    /**
     * Handle the plan "updated" event.
     *
     * @param  Plan  $plan
     * @return void
     */
    public function updated(Plan $plan)
    {
        //
    }

    /**
     * Handle the plan "deleted" event.
     *
     * @param  Plan  $plan
     * @return void
     */
    public function deleted(Plan $plan)
    {
        //
    }

    /**
     * Handle the plan "restored" event.
     *
     * @param  Plan  $plan
     * @return void
     */
    public function restored(Plan $plan)
    {
        //
    }

    /**
     * Handle the plan "force deleted" event.
     *
     * @param  Plan  $plan
     * @return void
     */
    public function forceDeleted(Plan $plan)
    {
        //
    }
}

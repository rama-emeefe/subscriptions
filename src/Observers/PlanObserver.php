<?php

namespace Emeefe\Subscriptions\Observers;
use Emeefe\Subscriptions\Models\Plan;
use Emeefe\Subscriptions\Exceptions\RepeatedCodeException;


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
        if($plan->type->plans()->where('code', $plan->code)->exists()){
            throw new RepeatedCodeException('Ya existe el codigo '.$plan->code);
        }
        
        $oldDefaultPlan = $plan->type->plans()->where('is_default', 1)->first();
        if($plan->isDefault()) {
            if($oldDefaultPlan){
                if($oldDefaultPlan->code != $plan->code) {
                    $plan->type->plans()->where('is_default', 1)->update(['is_default' => false]);
                }
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

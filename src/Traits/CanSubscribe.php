<?php

namespace Emeefe\Subscriptions\Traits;

use Emeefe\Subscriptions\Models\PlanSubscription;
use Emeefe\Subscriptions\Models\PlanPeriod;
use Carbon\Carbon;

trait CanSubscribe{

    /**
     * The subscriptions relationship
     */
    public function subscriptions(){
        return $this->morphMany(PlanSubscription::class, 'subscriber');
    }

    /**
     * Subscribe to period
     * 
     * @param PlanPeriod $period        The period instance
     * @param int        $periodCount   The number of periods
     * @return boolean 
     */
    public function subscribeTo(PlanPeriod $period, int $periodCount = 1){
        //TODO verificar si hay currentSubsciption y no esta cancelada devolver false
        if (!$this->currentSubscription($period->plan_id)) {
            $subscription = new PlanSubscription();
            $subscription->period_id = $period->id;
            $subscription->subscriber_id = $this->id;
            $subscription->subscriber_type = get_class($this);
            $subscription->trial_starts_at = Carbon::now()->toDateTimeString();
            $subscription->starts_at = Carbon::now()->addDays($period->trial_days)->toDateTimeString();
            $subscription->expires_at = ($period->isRecurring()) ? null : null; //duda
            $subscription->cancelled_at = null;
            $subscription->cancellation_reason = null;
            $subscription->plan_type_id = $period->plan->type->id;
            $subscription->price = $period->price;
            $subscription->tolerance_days = $period->tolerance_days;
            $subscription->currency = $period->currency;
            $subscription->period_unit = $period->period_unit;
            $subscription->period_count = $periodCount;
            $subscription->is_recurring = $period->is_recurring;
            $subscription->save();
            return true;
        } 

        return false;
    }

    /**
     * Check if there is a subscription linked to the model
     * 
     * @param string|PlanType $planTypeOrType   The plan type instance or type string
     * @return boolean
     */
    public function hasSubscription($planTypeOrType){
        //TODO
        if(is_string($planTypeOrType)) {
            $subscriptions = $this->subscriptions();
            $band = false;
            foreach($subscriptions as $sub) {
                // if($sub->plan_type->type == $planTypeOrType) {
                //     return true;
                // }
                if($sub->hasType($planTypeOrType)) {
                    $band = true;
                }
            }
            if($band) {
                return true;
            }            
        } else {
            if($this->subscriptions()->where('plan_type_id', $planTypeOrType->id)->first()){
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get the last subscription created on the model
     * 
     * @param string|PlanType $planTypeOrType
     * @return PlanSubscription
     */
    public function currentSubscription($planTypeOrType){
        //TODO la ultima por starts_at
        return $this->subscriptions()->where([
            ['starts_at', '<>', null],
            ['plan_type_id', $planTypeOrType],       
        ])->first();
    }
}    
<?php

namespace Emeefe\Subscriptions\Traits;

use Emeefe\Subscriptions\Models\PlanSubscription;
use Emeefe\Subscriptions\Models\PlanPeriod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Emeefe\Subscriptions\Events\NewSubscription;


trait CanSubscribe{

    /**
     * The subscriptions relationship
     */
    public function subscriptions(){
        return $this->morphMany(config('subscriptions.models.subscription'), 'subscriber');
    }

    /**
     * Subscribe to period
     * 
     * @param PlanPeriod $period        The period instance
     * @param int        $periodCount   The number of periods
     * @return boolean 
     */
    public function subscribeTo(PlanPeriod $period, int $periodCount = 1){
        $currentSubscription = $this->currentSubscription($period->plan->type);
        if($currentSubscription && !$currentSubscription->isCancelled()){
            return false;
        }
        $planSubscriptionModel = config('subscriptions.models.subscription');
        $subscription = new $planSubscriptionModel();
        $subscription->period_id = $period->id;
        $subscription->subscriber_id = $this->id;
        $subscription->subscriber_type = get_class($this);
        $subscription->trial_starts_at = Carbon::now()->toDateTimeString();
        $subscription->starts_at = Carbon::now()->addDays($period->trial_days)->toDateTimeString();
        if($period->is_recurring || $period->isLimitedNonRecurring()) {
            $dt = Carbon::parse($subscription->starts_at);
            $dt->settings([
                'monthOverflow' => false,
            ]);
            $count = $periodCount * $period->period_count;
            if($period->period_unit == 'day') {
                $subscription->expires_at = $dt->addDays($count)->toDateTimeString();
            }
            if($period->period_unit == 'month') {
                $subscription->expires_at = $dt->addMonths($count)->toDateTimeString();
            }
            if($period->period_unit == 'year') {
                $subscription->expires_at = $dt->addYears($count)->toDateTimeString();
            }
            if($period->period_unit == null) {
                $subscription->expires_at = null;
            }
        } else {
            $subscription->expires_at = null;
        }
        $subscription->cancelled_at = null;
        $subscription->cancellation_reason = null;
        $subscription->plan_type_id = $period->plan->type->id;
        $subscription->price = $period->price;
        $subscription->tolerance_days = $period->tolerance_days;
        $subscription->currency = $period->currency;
        $subscription->period_unit = $period->period_unit;
        $subscription->period_count = $period->period_count;
        $subscription->is_recurring = $period->is_recurring;
        $subscription->save();

        event(new NewSubscription($this, $subscription));

        $featuresPlan = $period->plan->features()->get();            
        foreach($featuresPlan as $featurePlan){
            if($featurePlan->type == 'limit') {
                $subscription->features()->attach($featurePlan->id, ['limit' => $featurePlan->pivot->limit, 'usage' => 0]);
            } else {
                $subscription->features()->attach($featurePlan->id);
            }
        }

        return true;
    }

    /**
     * Check if there is a subscription linked to the model
     * 
     * @param string|PlanType $planTypeOrType   The plan type instance or type string
     * @return boolean
     */
    public function hasSubscription($planTypeOrType){
        if(is_string($planTypeOrType)) {
            return $subscriptions = $this->subscriptions()->whereHas('plan_type', function(Builder $query) use ($planTypeOrType){
                $query->where('type', $planTypeOrType);
            })->exists();           
        } else {
            return $this->subscriptions()->where('plan_type_id', $planTypeOrType->id)->exists();
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
        if(is_int($planTypeOrType)) {
            return $this->subscriptions()->where([
                ['starts_at', '<>', null],
                ['plan_type_id', $planTypeOrType],       
            ])->first();
        }
        return $this->subscriptions()->where([
            ['starts_at', '<>', null],
            ['plan_type_id', $planTypeOrType->id],       
        ])->first();
    }
}    
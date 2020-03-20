<?php

namespace Emeefe\Subscriptions\Models;
use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanTypeInterface;

class PlanType extends Model implements PlanTypeInterface{
    public $timestamps = false;

    public function features(){
        return $this->belongsToMany(PlanFeature::class, 'plan_type_fetaure', 'type_id', 'feature_id');
    }
    public function plans(){
        return $this->hasMany(Plan::class, 'plan_type_id');
    }
    public function subscriptions(){
        return $this->hasMany(PlanSubscription::class, 'plan_type_id');
    }

    public function attachFeature(PlanFeature $planFeature){
        if(!$this->hasFeature($planFeature->code)) {
            $this->features()->attach($planFeature->id);
        }
        return $this;
    }

    public function hasFeature(string $featureCode){
        $feature = $this->features()->where('code', $featureCode)->exists();
        if ($feature) {
            return true;
        }
        return false;
    }

    public function getFeatureByCode(string $featureCode){
        return PlanFeature::where('code', $featureCode)->get()->first();
    }

    public function getDefaultPlan(){
        $default_plan = $this->plans()->where('is_default',1)->first();
        return $default_plan;
    }
}
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
        return $this->hasMany(Plan::class);
    }
    public function subscriptions(){

    }
    public function attachFeature(PlanFeature $planFeature){
        $this->features()->attach($planFeature->id);
        return $this;
    }
    public function hasFeature(string $featureCode){
        if ($this->features()->first()->code != $featureCode) {
            return false;
        }
        return true;
    }
    public function getFeatureByCode(string $featureCode){
        return PlanFeature::where('code', $featureCode)->get()->first();
    }
    public function getDefaultPlan(){
        $is_default = $this->plans()->first()->is_default;
        if ( $is_default ) {
            return $this->plans()->first();
        }
        return null;
    }
}
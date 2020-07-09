<?php

namespace Emeefe\Subscriptions\Models;
use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanTypeInterface;

class PlanType extends Model implements PlanTypeInterface{

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('emeefe.subscriptions.tables.plan_types'));
    }

    public function features(){
        return $this->belongsToMany(config('emeefe.subscriptions.models.feature'), config('emeefe.subscriptions.tables.plan_type_feature'), 'type_id', 'feature_id');
    }
    public function plans(){
        return $this->hasMany(config('emeefe.subscriptions.models.plan'), 'type_id');
    }
    public function subscriptions(){
        return $this->hasMany(config('emeefe.subscriptions.models.subscription'), 'plan_type_id');
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
        $planFeatureModel = config('emeefe.subscriptions.models.feature');
        return $planFeatureModel::where('code', $featureCode)->get()->first();
    }

    public function getDefaultPlan(){
        $default_plan = $this->plans()->where('is_default',1)->first();
        return $default_plan;
    }
}
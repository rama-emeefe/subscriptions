<?php

namespace Emeefe\Subscriptions\Models;
use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanTypeInterface;

class PlanType extends Model implements PlanTypeInterface{
    //!SI DEBE TENER TIMESTAMPS
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('subscriptions.tables.plan_types'));
    }

    public function features(){
        return $this->belongsToMany(config('subscriptions.models.feature'), config('subscriptions.tables.plan_type_feature'), 'type_id', 'feature_id');
    }
    public function plans(){
        return $this->hasMany(config('subscriptions.models.plan'), 'plan_type_id');
    }
    public function subscriptions(){
        return $this->hasMany(config('subscriptions.models.subscription'), 'plan_type_id');
    }

    //!FALTA VERIFICAR SI EL FEATURE NO EXISTE YA, EN CASO DE EXISTIR NO HACE NADA MAS QUE DEVOLVERSE ASI MISMO
    public function attachFeature(PlanFeature $planFeature){
        $this->features()->attach($planFeature->id);
        return $this;
    }

    //!!ESTO ES INCORRECTO YA QUE SOLO ESTÁS COMPARANDO CON EL PRIMER FEATURE
    public function hasFeature(string $featureCode){
        if ($this->features()->first()->code != $featureCode) {
            return false;
        }
        return true;
    }
    public function getFeatureByCode(string $featureCode){
        $planFeatureModel = config('subscriptions.models.feature');
        return $planFeatureModel::where('code', $featureCode)->get()->first();
    }
    public function getDefaultPlan(){
        $default_plan = $this->plans()->where('is_default',1)->first();
        return $default_plan;
    }
}
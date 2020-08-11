<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanInterface;
use Emeefe\Subscriptions\Events\FeatureLimitChangeOnPlan;
use Emeefe\Subscriptions\Events\FeatureRemovedFromPlan;
use Emeefe\Subscriptions\Events\NewFeatureOnPlan;


class Plan extends Model implements PlanInterface{

    protected $fillable = ['is_default'];

    protected $casts = [
        'metadata' => 'array',
        'is_default' => 'boolean'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('emeefe.subscriptions.tables.plans'));
    }

    public function type(){
        return $this->belongsTo(config('emeefe.subscriptions.models.type'), 'type_id');
    }

    public function features(){
        return $this->belongsToMany(config('emeefe.subscriptions.models.feature'), config('emeefe.subscriptions.tables.plan_feature_values'), 'plan_id', 'plan_feature_id')->withPivot('limit');
    }

    public function periods() {
        return $this->hasMany(config('emeefe.subscriptions.models.period'), 'plan_id');
    }

    public function scopeByType($query, string $type){
        $planType = PlanType::where('type', $type)->first();
        if(!$planType){
            return $query;
        }

        return $query->where('type_id', $planType->id);
    }

    public function scopeVisible($query){
        return $query->where('is_visible',1);
    }

    public function scopeHidden($query){
        return $query->where('is_visible',0);
    }

    public function scopeDefault($query){
        return $query->where('is_default',1);
    }

    public function assignFeatureLimitByCode(string $featureCode, int $limit = 0){
        $feature = $this->type->features()->limitType()->where('code', $featureCode)->first();
        if($feature) {
            if($limit >= 1) {
                $existentFeature = $this->features()->limitType()->where('code', $featureCode)->first();
                if($existentFeature){
                    $existentFeature->pivot->limit = $limit;
                    $existentFeature->pivot->save();
                    event(new FeatureLimitChangeOnPlan($this, $existentFeature, $limit));
                    return true;
                } else {
                    $this->features()->attach($feature->id, ['limit' => $limit]);
                    event(new NewFeatureOnPlan($this, $feature, $limit));
                    return true;
                }
            }
        }
        return false;
    }

    public function removeFeature(string $featureCode){
        $feature = $this->type->features()->where('code', $featureCode)->first();

        if($feature){
            $this->features()->detach($feature->id);
            event(new FeatureRemovedFromPlan($this, $feature));
            return true;
        }

        return false;
    }

    public function assignUnlimitFeatureByCode(string $featureCode) {
        $feature = $this->type->features()->featureType()->where('code', $featureCode)->first();
        if($feature) {
            $this->features()->attach($feature->id);
            event(new NewFeatureOnPlan($this, $feature, null));
            return true;
        }
        return false;
    }

    public function getFeatureLimitByCode($featureCode) {
        $limit = $this->features()->limitType()->where('code', $featureCode)->first();

        //The limit has been defined
        if($limit) {
            $limitNumber = $limit->pivot->limit;
            if($limitNumber !== null) {
                return $limitNumber;
            }
        }

        //Feature exists on plan type but limit not assigned
        if( $this->type->features()->limitType()->where('code', $featureCode)->exists() ) { 
            return 0;
        }

        //Feature not exists on plan type or exists but is not limit type
        return -1;
    }

    public function hasFeature(string $featureCode){
        return $this->features()->where('code', $featureCode)->exists();
    }

    public function isVisible() {
        return $this->is_visible;
    }

    public function isHidden() {
        return !$this->is_visible;
    }

    public function isDefault() {
        return $this->is_default;
    }

    public function setAsVisible() {
        $this->is_visible = true;
        $this->save();
    }

    public function setAsHidden() {
        $this->is_visible = false;
        $this->save();
    }

    public function setAsDefault() {
        $this->is_default = true;
        $this->save();
    }

}
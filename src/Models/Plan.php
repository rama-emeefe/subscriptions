<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanInterface;

class Plan extends Model implements PlanInterface{

    protected $casts = [
        'metadata' => 'array',
        'is_default' => 'boolean'
    ];

    public function type(){
        return $this->belongsTo(PlanType::class, 'plan_type_id');
    }

    public function features(){
        return $this->belongsToMany(PlanFeature::class, 'plan_feature_values', 'plan_id', 'plan_feature_id')->withPivot('limit');
    }

    public function scopeByType($query, string $type){
        return $query->where('plan_type_id', $type);
    }

    public function scopeVisible($query){
        return $query->where('is_visible',1);
    }

    public function scopeHidden($query){
        return $query->where('is_visible',0);
    }

    public function assignFeatureLimitByCode(string $featureCode, int $limit = 0){
        $feature = $this->type->features()->limitType()->where('code', $featureCode)->first();
        if($feature) {
            if($limit >= 1) {
                if($this->hasFeature($featureCode)){
                    $existenFeature = $this->features()->limitType()->where('code', $featureCode)->first()->pivot;
                    $existenFeature->limit = $limit;
                    $existenFeature->save();
                    return true;
                } else {
                    $this->features()->attach($feature->id, ['limit' => $limit]);
                    return true;
                }
            }
        }
        return false;
    }

    public function getFeatureLimitByCode($featureCode) {
        if($this->hasFeature($featureCode)) {
            $limit = $this->features()->limitType()->where('code', $featureCode)->first();
            if($limit) {
                return $limit->pivot->limit;
            } else {
                return -1;
            }
        }
        //TODO verificar que el plan tenga el feature del tipo limit pero a traves de su type
        return -1;
    }

    public function hasFeature(string $featureCode){
        $feature = $this->features()->where('code', $featureCode)->first();
        if($feature) {
            return true;
        }
        return false;
    }

    public function isVisible() {
        if($this->is_visible) {
            return true;
        }
        return false;
    }

    public function isHidden() {
        if($this->is_visible) {
            return false;
        }
        return true;
    }

    public function isDefault() {
        if($this->is_default) {
            return true;
        }
        return false;
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
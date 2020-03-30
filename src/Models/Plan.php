<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanInterface;

class Plan extends Model implements PlanInterface{

    protected $fillable = ['is_default'];

    protected $casts = [
        'metadata' => 'array',
        'is_default' => 'boolean'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('subscriptions.tables.plans'));
    }

    public function type(){
        return $this->belongsTo(config('subscriptions.models.type'), 'plan_type_id');
    }

    public function features(){
        return $this->belongsToMany(config('subscriptions.models.feature'), config('subscriptions.tables.plan_feature_values'), 'plan_id', 'plan_feature_id')->withPivot('limit');
    }

    public function periods() {
        return $this->hasMany(config('subscriptions.models.period'), 'plan_id');
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
                $existentFeature = $this->features()->limitType()->where('code', $featureCode)->first();
                if($existentFeature){
                    $existentFeature->pivot->limit = $limit;
                    $existentFeature->pivot->save();
                    return true;
                } else {
                    $this->features()->attach($feature->id, ['limit' => $limit]);
                    return true;
                }
            }
        }
        return false;
    }

    public function assignUnlimitFeatureByCode(string $featureCode) {
        $feature = $this->type->features()->featureType()->where('code', $featureCode)->first();
        if($feature) {
            $this->features()->attach($feature->id);
            return true;
        }
        return false;
    }

    public function getFeatureLimitByCode($featureCode) {
        $limit = $this->features()->limitType()->where('code', $featureCode)->first();
        if($limit) {
            $limitNumber = $limit->pivot->limit;
            if($limitNumber) {
                return $limitNumber;
            }
            return 0;
        } 
        if( $this->hasFeature($featureCode)) { 
            return -1;
        }
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
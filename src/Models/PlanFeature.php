<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanFeatureInterface;
use Emeefe\Subscriptions\PlanType;

class PlanFeature extends Model implements PlanFeatureInterface{
    public const TYPE_LIMIT = 'limit';
    public const TYPE_FEATURE = 'feature';

    protected $casts = [
        'metadata' => 'array'
    ];

    public function types() {
        return $this->belongsToMany(PlanType::class, 'plan_type_fetaure', 'type_id', 'feature_id');
    }

    public function plans() {
        return $this->belongsToMany(Plan::class, 'plan_feature_values', 'plan_id', 'plan_feature_id')->withPivot('limit');
    }

    public function subscriptions() {
        
    }

    public function scopeLimitType($query) {
        return $query->where('type', 'limit');
    }

    public function scopeFeatureType($query){
        return $query->where('type', 'feature');
    }
}
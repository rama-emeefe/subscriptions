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

    public function scopeLimitType($query) {

    }

    public function scopeFeatureType($query){

    }
}
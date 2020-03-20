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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('subscriptions.tables.plan_features'));
    }

    public function types() {
        return $this->belongsToMany(config('subscriptions.models.type'), config('subscriptions.tables.plan_type_fetaure'), 'type_id', 'feature_id');
    }

    public function plans() {
        return $this->belongsToMany(config('subscriptions.models.plan'), config('subscriptions.tables.plan_feature_values'), 'plan_id', 'plan_feature_id')->withPivot('limit');
    }

    public function subscriptions() {
        return $this->belongsToMany(config('subscriptions.models.subscription'), config('subscriptions.tables.plan_subscription_usage'), 'feature_id', 'subscription_id')->withPivot(['limit', 'usage']);
    }

    public function scopeLimitType($query) {
        return $query->where('type', 'limit');
    }

    public function scopeFeatureType($query){
        return $query->where('type', 'feature');
    }
}
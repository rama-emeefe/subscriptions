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
        $this->setTable(config('emeefe.subscriptions.tables.plan_features'));
    }

    public function types() {
        return $this->belongsToMany(config('emeefe.subscriptions.models.type'), config('emeefe.subscriptions.tables.plan_type_feature'), 'type_id', 'feature_id');
    }

    public function plans() {
        return $this->belongsToMany(config('emeefe.subscriptions.models.plan'), config('emeefe.subscriptions.tables.plan_feature_values'), 'plan_id', 'plan_feature_id')->withPivot('limit');
    }

    public function subscriptions() {
        return $this->belongsToMany(config('emeefe.subscriptions.models.subscription'), config('emeefe.subscriptions.tables.plan_subscription_usage'), 'feature_id', 'subscription_id')->withPivot(['limit', 'usage']);
    }

    public function scopeLimitType($query) {
        return $query->where('type', 'limit');
    }

    public function scopeFeatureType($query){
        return $query->where('type', 'feature');
    }
}
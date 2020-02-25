<?php

namespace Emeefe\Subscriptions\Contracts;

interface PlanFeatureInterface{

    public function types();

    public function plans();
    /**
     * Scope by limit type
     */
    public function scopeLimitType($query);

    /**
     * Scope by feature type
     */
    public function scopeFeatureType($query);
}
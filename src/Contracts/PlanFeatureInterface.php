<?php

namespace Emeefe\Subscriptions\Contracts;

interface PlanFeatureInterface{

     /**
     * The types relationship
     */
    public function types();

    /**
     * The plans relationship
     */
    public function plans();

     /**
     * The subscriptions relationship
     */
    public function subscriptions();
    /**
     * Scope by limit type
     */
    public function scopeLimitType($query);

    /**
     * Scope by feature type
     */
    public function scopeFeatureType($query);
}
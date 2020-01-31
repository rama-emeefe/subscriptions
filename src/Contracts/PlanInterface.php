<?php

namespace Emeefe\Subscriptions\Contracts;

use Emeefe\Subscriptions\PlanFeature;
use Emeefe\Subscriptions\PlanType;

interface PlanInterface{

    /**
     * The plan type relationship
     */
    public function type();

    /**
     * The features relationship
     */
    public function features();

    /**
     * Scope plans by type
     */
    public function scopeByType($query, string $type);

    /**
     * Scope visible plans
     */
    public function scopeVisible($query);

    /**
     * Scope hidden plans
     */
    public function scopeHidden($query);

    /**
     * Assign the feature limit through the feature code 
     * validating that the feature is of the type limit, 
     * exists and a positive number greater than zero is 
     * provided, then returns true, otherwise returns false
     * 
     * @param int $limit
     * @param string $featureCode
     * @return boolean
     */
    public function assignFeatureLimitByCode(int $limit, string $featureCode);

    /**
     * Get the limit of a feature with limit, if the feature 
     * has no limit or does not exist within the type of 
     * the plan returns -1.
     * 
     * @return int
     */
    public function getFeatureLimitByCode();

    /**
     * TODO La funcionalidad es un alias de $this->type->hasFeature
     * Check if the PlanType of the Plan instance has a feature
     * with the $featureCode code
     * 
     * @param string $featureCode
     * @return boolean
     */
    public function hasFeature(string $featureCode);
}
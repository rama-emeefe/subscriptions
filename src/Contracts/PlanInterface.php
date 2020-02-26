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
     * The features relationship throught
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
    public function assignFeatureLimitByCode(string $featureCode, int $limit);

    /**
     * Get the limit of a feature with limit, if the feature 
     * has no limit or does not exist within the type of 
     * the plan returns -1.
     * 
     * @return int
     */
    public function getFeatureLimitByCode(string $featureCode);

    /**
     * TODO La funcionalidad es un alias de $this->type->hasFeature
     * Check if the PlanType of the Plan instance has a feature
     * with the $featureCode code
     * 
     * @param string $featureCode
     * @return boolean
     */
    public function hasFeature(string $featureCode);

    /**
     * Check if the plan is visible
     * 
     * @return bool
     */
    public function isVisible();

    /**
     * Check if the plan is hidden
     * 
     * @return bool
     */
    public function isHidden();

    /**
     * Check if the plan is default in plan type
     * 
     * @return bool
     */
    public function isDefault();

    /**
     * Define the plan as visible
     * 
     * @return bool
     */
    public function setAsVisible();

    /**
     * Define the plan as hidden
     * 
     * @return bool
     */
    public function setAsHidden();

    /**
     * Define the plan as default
     * 
     * @return bool
     */
    public function setAsDefault();
}
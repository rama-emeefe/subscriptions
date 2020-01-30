<?php

namespace Emeefe\Subscriptions\Contracts;

use Emeefe\Subscriptions\PlanFeature;
use Emeefe\Subscriptions\PlanType;

interface PlanTypeInterface{

    /**
     * The features relationship
     */
    public function features();

    /**
     * Attach a PlanFeature model and return
     * the PlanType instance
     * 
     * @param PlanFeature $planFeature
     * @return PlanType
     */
    public function attachFeature(PlanFeature $planFeature);

    /**
     * Check if the PlanType instance has a feature
     * with the $featureCode code
     * 
     * @param string $featureCode
     * @return boolean
     */
    public function hasFeature(string $featureCode);

    /**
     * Get the plan feature by code attached to the
     * PlanType instance, if it does not exist return null
     * 
     * @param string $featureCode
     * @return PlanFeature
     */
    public function getFeatureByCode(string $featureCode);
}
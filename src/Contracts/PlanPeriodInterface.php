<?php

namespace Emeefe\Subscriptions\Contracts;

use Emeefe\Subscriptions\PlanFeature;
use Emeefe\Subscriptions\PlanType;

interface PlanTypeInterface{

    /**
     * Check if the plan period is recurring
     * 
     * @return bool
     */
    public function isRecurring();

    /**
     * Check if the plan period is limited and non recurring
     * 
     * @return bool
     */
    public function isLimitedNonRecurring();

    /**
     * Check if the plan period is inlimited and non recurring
     * 
     * @return bool
     */
    public function isUnlimitedNonRecurring();

    /**
     * Check if the plan period is visible
     * 
     * @return bool
     */
    public function isVisible();

    /**
     * Check if the plan period is hidden
     * 
     * @return bool
     */
    public function isHidden();

    /**
     * Check if the plan period is default
     * 
     * @return bool
     */
    public function isDefault();

    /**
     * Check if the plan period is free, the
     * price is zero
     * 
     * @return bool
     */
    public function isFree();

    /**
     * Check if the plan period has a trial
     * 
     * @return bool
     */
    public function hasTrial();

    /**
     * Define the plan period as visible
     * 
     * @return bool
     */
    public function setAsVisible();

    /**
     * Define the plan period as hidden
     * 
     * @return bool
     */
    public function setAsHidden();

    /**
     * Define the plan period as default
     * 
     * @return bool
     */
    public function setAsDefault();
}
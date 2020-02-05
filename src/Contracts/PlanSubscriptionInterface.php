<?php
namespace Emeefe\Subscriptions\Contracts;

use Emeefe\Subscriptions\Models\PlanType;

interface PlanSubscriptionInterface{

    /**
     * The period relationship
     */
    public function period();

    /**
     * The subscriber relationship
     */
    public function subscriber();

    /**
     * The plan type relationship
     */
    public function plan_type();


    /**
     * Scope subscriptions by plan type
     */
    public function scopeByType($query, PlanType $planType);

    /**
     * Scope the canceled subscriptions
     */
    public function scopeCanceled($query);

    /**
     * Scope the free subscriptions
     */
    public function scopeFree($query);

    /**
     * Scope the recurring subscriptions
     */
    public function recurring($query);


    /**
     * Check if the subscription is on trial period
     * 
     * @return bool
     */
    public function isOnTrial();

    /**
     * Check if the subscription is active
     * 
     * @return bool
     */
    public function isActive();

    /**
     * Check if the subscriptions is valid
     * 
     * @return bool
     */
    public function isValid();

    /**
     * Check if the subscription is expired with tolerance days
     * 
     * @return bool
     */
    public function isExpiredWithTolerance();

    /**
     * Check if the subscription is expired without tolerance days
     * 
     * @return bool
     */
    public function isFullExpired();

    /**
     * Check remaining trial days, if there is not
     * trial period then returns zero
     * 
     * @return int
     */
    public function remainingTrialDays();

    /**
     * Renew the subscription as long as it is not canceled
     * 
     * @param int $periods
     * @return boolean
     */
    public function renew(int $periods = 1);

    /**
     * Cancel the subscription
     * 
     * @param string $reason Reason for cancellation
     * @return boolean  true if it could be canceled, false if it was already canceled
     */
    public function cancel(string $reason = null);

    
}
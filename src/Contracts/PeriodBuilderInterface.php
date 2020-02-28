<?php

namespace Emeefe\Subscriptions\Contracts;

interface PeriodBuilderInterface{

    /**
     * Set the price of plan period
     * 
     * @param float $price
     * @return PeriodBuilder
     */
    public function setPrice(float $price);

    /**
     * Set the currency of plan period with
     * ISO 4217
     * 
     * @param string $currency
     * @return PeriodBuilder
     */
    public function setCurrency(string $currency);

    /**
     * Set the trial days of plan period
     * 
     * @param int $trialDays
     * @return PeriodBuilder
     */
    public function setTrialDays(int $trialDays);

    /**
     * Set the plan period as recurrent
     * 
     * @param int $count
     * @param string $unit
     * @return PeriodBuilder
     */
    public function setRecurringPeriod(int $count, string $unit);

    /**
     * Set the plan period as non recurring with a cicle
     * 
     * @param int $count
     * @param string $unit
     * @return PeriodBuilder
     */
    public function setLimitedNonRecurringPeriod(int $count, string $unit);

    /**
     * Set the plan period as hidden
     * 
     * @return PeriodBuilder
     */
    public function setHidden();

    /**
     * Set the tolerance days of the plan period
     * 
     * @param int $toleranceDays
     * @return PeriodBuilder
     */
    public function setToleranceDays(int $toleranceDays);

    /**
     * Set the plan period as default
     * 
     * @return PeriodBuilder
     */
    public function setDefault();

    /**
     * Build the plan period and return
     * the PeriodBuilder instance
     * 
     * @return PlanPeriod
     */
    public function create();
}
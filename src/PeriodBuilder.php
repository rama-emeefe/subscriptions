<?php

namespace Emeefe\Subscriptions;
use Emeefe\Subscriptions\Models\PlanPeriod;
use Emeefe\Subscriptions\Contracts\PeriodBuilderInterface;

class PeriodBuilder implements PeriodBuilderInterface{

    private $period;

    public function __construct($displayName, $code, $plan){
        $this->period = new PlanPeriod();
        $this->period->display_name = $displayName;
        $this->period->code = $code;
        $this->period->price = 0;
        $this->period->currency = 'MXN';
        $this->period->plan_id = $plan->id;
        $this->period->trial_days = 0;
        $this->period->period_unit = null;
        $this->period->period_count = null;
        $this->period->is_recurring = false;
        $this->period->is_visible = true;
        $this->period->tolerance_days = 0;
        $this->period->is_default = false;
    }

    public function setPrice(float $price) {
        if($price >= 0) {
            $this->period->price = $price;
        } else {
            $this->period->price = 0;
        }
        return $this;
    }
    public function setCurrency(string $currency) {
        $this->period->currency = $currency;
        return $this;
    }

    public function setTrialDays(int $trialDays) {
        if($trialDays >= 0) {
            $this->period->trial_days = $trialDays;
        } else {
            $this->period->trial_days = 0;
        }
        return $this;
    }

    public function setRecurringPeriod(int $count, string $unit) {
        $this->period->period_unit = $unit;
        $this->period->period_count = $count;
        $this->period->is_recurring = true;
        return $this;
    }

    public function setLimitedNonRecurringPeriod(int $count, string $unit) {
        $this->period->period_unit = $unit;
        $this->period->period_count = $count;
        $this->period->is_recurring = false;
        return $this;
    }
    
    public function setHidden() {
        $this->period->is_visible = false;
        return $this;
    }

    public function setToleranceDays(int $toleranceDays) {
        if($toleranceDays >= 0) {
            $this->period->tolerance_days = $toleranceDays;
        } else {
            $this->period->tolerance_days = 0;
        }
        return $this;
    }

    public function setDefault() {
        $this->period->is_default = true;
        return $this;
    }

    public function create() {
        $this->period->save();
        return $this->period;
    }
}
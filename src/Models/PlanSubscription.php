<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanSubscriptionInterface;

class PlanSubscription extends Model implements PlanSubscriptionInterface{

    public function period(){
        return $this->belongsTo(PlanPeriod::class, 'period_id');
    }

    public function subscriber() {
        return $this->morphTo();
    }

    public function plan_type() {
        return $this->belongsTo(PlanType::class, 'plan_type_id');
    }

    public function features() {
        
    }

    public function scopeByType($query, PlanType $planType) {

    }

    public function scopeCanceled($query) {

    }

    public function scopeFree($query) {

    }

    public function recurring($query) {

    }

    public function isOnTrial() {

    }

    public function isActive() {

    }

    public function isValid() {

    }

    public function isExpiredWithTolerance() {

    }

    public function isFullExpired() {

    }

    public function remainingTrialDays() {

    }

    public function renew(int $periods = 1) {

    }

    public function cancel(string $reason = null) {

    }

    public function hasFeature(string $featureCode) {

    }

    public function consumeFeature(string $featureCode, int $units = 1) {

    }

    public function unconsumeFeature(string $featureCode, int $units = 1) {

    }

    public function getUsageOf(string $featureCode) {

    }

    public function getRemainingOf(string $featureCode) {

    }
}
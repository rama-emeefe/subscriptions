<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanSubscriptionInterface;

class PlanSubscription extends Model implements PlanSubscriptionInterface{

    //cast de fechas
    protected $casts = [
        'trial_starts_at' => 'datetime',
        'starts_at' => 'datetime'
    ];

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
        return $this->belongsToMany(PlanFeature::class, 'plan_subscription_usage', 'feature_id', 'subscription_id')->withPivot(['limit', 'usage']);
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
        $currentDay = Carbon\Carbon::now();
        if($currentDay > $this->trial_starts_at && $currentDay < $this->starts_at) {
            return true;
        }
        return false;
    }

    public function isActive() {
        $currentDay = Carbon\Carbon::now();
        if($currentDay > $this->starts_at && $currentDay < $this->expires_at || $this->expires_at == null) {
            return true;
        }
        return false;
    }

    public function isValid() {
        if($this->isOnTrial() || $this->isActive() || $this->isExpiredWithTolerance()) {
            return true;
        }
        return false;
    }

    public function isExpiredWithTolerance() {
        $currentDay = Carbon\Carbon::now();
        $expireDateWithTolerance = Carbon\Carbon::parse($this->expires_at)->addDays($this->tolerance_days);
        if($currentDay > $this->expires_at && $currentDay < $expireDateWithTolerance) {
            return true;
        }
        return false;
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
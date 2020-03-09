<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanSubscriptionInterface;

class PlanSubscription extends Model implements PlanSubscriptionInterface{

    //cast de fechas
    protected $casts = [
        'trial_starts_at' => 'datetime',
        'starts_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at' => 'datetime'
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

    public function hasType(string $type) {
        if ($this->plan_type->type == $type) {
            return true;
        }
        return false;
    }

    public function scopeByType($query, PlanType $planType) {
        return $query->where('plan_type_id', $planType->id);
    }

    public function scopeCanceled($query) {
        return $query->where('cancelled_at', '<>', null);
    }

    public function scopeFree($query) {
        return $query->where('price', 0);
    }

    public function recurring($query) {
        return $query->where('is_recurring', 1);
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
        $currentDay = Carbon\Carbon::now();
        $expireDateWithTolerance = Carbon\Carbon::parse($this->expires_at)->addDays($this->tolerance_days);
        if($currentDay > $this->expires_at && $currentDay < $expireDateWithTolerance) {
            return false;
        }
        return true;
    }

    public function remainingTrialDays() {
        $currentDay = Carbon\Carbon::now();
        if($currentDay < $this->starts_at) {
            $remainingDays = $currentDay->floatDiffInDays($this->starts_at);
            return (int) $r;
        }
        return 0;
    }

    public function renew(int $periods = 1) {
        if($this->is_recurring) {
            $this->period_count = $periods;
            $this->save();
            return true;
        }
        return false;
    }

    public function cancel(string $reason = null) {
        if($this->period_count == null || $this->cancelled_at == null) {
            $this->cancelled_at = Carbon\Carbon::now()->toDateTimeString();
            $this->cancellation_reason = $reason;
            $this->save();
            return true;
        }
        return false;
    }

    public function hasFeature(string $featureCode) {
        $feature = $this->features()->where('code', $feature)->first();
        if($feature) {
            return true;
        }
        return false;
    }

    public function consumeFeature(string $featureCode, int $units = 1) {
        $feature = $this->features()->limitType()->where('code', $feature)->first();
        if($feature) {
            $limit = $feature->pivot->limit;
            $feature->pivot->limit = $limit - $units;
            return true;
        }
        return false;
    }

    public function unconsumeFeature(string $featureCode, int $units = 1) {
        $feature = $this->features()->limitType()->where('code', $feature)->first();
        if($feature) {
            $limit = $feature->pivot->limit;
            $feature->pivot->limit = $limit + $units;
            return true;
        }
        return false;
    }

    public function getUsageOf(string $featureCode) {

    }

    public function getRemainingOf(string $featureCode) {

    }
}
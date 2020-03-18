<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanSubscriptionInterface;
use Carbon\Carbon;

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
        $currentDay = Carbon::now();
        if($currentDay >= Carbon::parse($this->trial_starts_at) && $currentDay < Carbon::parse($this->starts_at)) {
            return true;
        }
        return false;
    }

    public function isActive() {
        // var_dump($this->expires_at);
        if(!$this->isCanceled()) {
            $currentDay = Carbon::now();
            if($currentDay >= $this->starts_at && $currentDay < $this->expires_at || $this->expires_at == null) {
                return true;
            }
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
        if(!$this->isCanceled()) {
            $currentDay = Carbon::now();
            $expireDateWithTolerance = Carbon::parse($this->expires_at)->addDays($this->tolerance_days);
            if($currentDay >= $this->expires_at && $currentDay < $expireDateWithTolerance) {
                return true;
            }
        }
        return false;
    }

    public function isFullExpired() {
        if( $this->isExpiredWithTolerance() || $this->isActive() || $this->isOnTrial()) {
            return false;
        }
        return true;
    }

    public function remainingTrialDays() {
        $currentDay = Carbon::now();
        if($currentDay < $this->starts_at) {
            $remainingDays = $currentDay->floatDiffInDays($this->starts_at) - 1.0;
            return (int) $remainingDays;
        }
        return 0;
    }

    public function renew(int $periods = 1) {
        if(!$this->isCanceled()) {
            if($this->is_recurring) {
                $this->period_count = $periods;
                $this->starts_at = Carbon::now();
                if($this->period_unit == 'day') {
                    $days = $this->period_count;
                    $this->expires_at = Carbon::parse($this->starts_at)->addDays($days)->toDateTimeString();
                }
                if($this->period_unit == 'month') {
                    $days = $this->period_count * 30;
                    $this->expires_at = Carbon::parse($this->starts_at)->addDays($days)->toDateTimeString();
                }
                if($this->period_unit == 'year') {
                    $days = $this->period_count * 365;
                    $this->expires_at = Carbon::parse($this->starts_at)->addDays($days)->toDateTimeString();
                }
                if($this->period_unit == null) {
                    $this->expires_at = null;
                }
                $this->save();
                return true;
            }
        }
        return false;
    }

    public function cancel(string $reason = null) {
        if($this->period_count == null || $this->cancelled_at == null) {
            $this->cancelled_at = Carbon::now()->toDateTimeString();
            $this->cancellation_reason = $reason;
            $this->save();
            return true;
        }
        return false;
    }

    public function isCanceled() {
        if($this->cancelled_at != null) {
            return true;
        }
        return false;
    }

    public function hasFeature(string $featureCode) {
        $feature = $this->plan_type->features()->where('code', $featureCode)->exists();
        if($feature) {
            return true;
        }
        return false;
    }

    public function consumeFeature(string $featureCode, int $units = 1) {
        $feature = $this->features()->limitType()->where('code', $featureCode)->first();
        var_dump($feature);
        if($feature) {
            $limit = $feature->pivot->limit;
            $feature->pivot->limit = $limit - $units;
            return true;
        }
        return false;
    }

    public function unconsumeFeature(string $featureCode, int $units = 1) {
        $feature = $this->features()->limitType()->where('code', $featureCode)->first();
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
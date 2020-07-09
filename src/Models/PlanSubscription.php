<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanSubscriptionInterface;
use Carbon\Carbon;
use Emeefe\Subscriptions\Events\RenewSubscription;
use Emeefe\Subscriptions\Events\CancelSubscription;
use Emeefe\Subscriptions\Events\FeatureConsumed;
use Emeefe\Subscriptions\Events\FeatureUnconsumed;

class PlanSubscription extends Model implements PlanSubscriptionInterface{

    protected $casts = [
        'trial_starts_at' => 'datetime',
        'starts_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public const CANCEL_REASON_UPDATE_SUBSCRIPTION = 'update_subscription';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('emeefe.subscriptions.tables.plan_subscriptions'));
    }

    public function period(){
        return $this->belongsTo(config('emeefe.subscriptions.models.period'), 'period_id');
    }

    public function subscriber() {
        return $this->morphTo();
    }

    public function plan_type() {
        return $this->belongsTo(config('emeefe.subscriptions.models.type'), 'plan_type_id');
    }

    public function features() {
        return $this->belongsToMany(config('emeefe.subscriptions.models.feature'), config('emeefe.subscriptions.tables.plan_subscription_usage'), 'subscription_id', 'feature_id')->withPivot(['limit', 'usage']);
    }

    public function hasType(string $type) {
        return $this->plan_type->type == $type;
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

    public function isUnlimited(){
        return !$this->expires_at;
    }

    public function isLimited(){
        return !$this->isUnlimited();
    }

    /**
     * Check if subscription is on trial
     * period
     * 
     * @return bool
     */
    public function isOnTrial() {
        $currentDay = Carbon::now();
        return $currentDay->greaterThanOrEqualTo($this->trial_starts_at) && $currentDay->lessThan($this->starts_at);
    }

    /**
     * Check if subscription is active
     * 
     * @return bool
     */
    public function isActive() {
        $currentDay = Carbon::now();
        if($currentDay->greaterThanOrEqualTo($this->starts_at) && $currentDay->lessThan($this->expires_at) || ($this->isUnlimited() && !$this->isCancelled())) {
            return true;
        }

        return false;
    }

    /**
     * Check if subscription is expired but
     * is in tolerance period
     * 
     * @return bool
     */
    public function isExpiredWithTolerance() {
        if($this->isUnlimited()){
            return false;
        }

        if(!$this->isCancelled()) {
            $currentDay = Carbon::now();
            $expireDateWithTolerance = $this->expires_at->addDays($this->tolerance_days);

            if($currentDay->greaterThanOrEqualTo($this->expires_at) && $currentDay->lessThan($expireDateWithTolerance)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if subscription is full expired
     * 
     * @return bool
     */
    public function isFullExpired() {
        return ! ($this->isExpiredWithTolerance() || $this->isActive() || $this->isOnTrial());
    }

    /**
     * Check if subscription is valid
     * 
     * @return bool
     */
    public function isValid() {
        $nonCancelled = !$this->isCancelled() && ($this->isOnTrial() || $this->isActive() || $this->isExpiredWithTolerance());
        $cancelled = $this->isCancelled() && ($this->isOnTrial() || $this->isActive());
        return $nonCancelled || $cancelled;
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
        if(!$this->isCancelled()) {
            if($this->is_recurring) {
                $dt = Carbon::parse($this->expires_at);
                $count = $periods * $this->period_count;
                $dt->settings([
                    'monthOverflow' => false,
                ]);
                if($this->period_unit == 'day') {
                    $this->expires_at = $dt->addDays($count)->toDateTimeString();
                }
                if($this->period_unit == 'month') {
                    $dts = Carbon::parse($this->starts_at);
                    $dt = $dt->addMonths($count);
                    if($dts->day == 30 || $dts->day == 31) {
                        if($dt->day == 29) {
                            $dt = $dt->addDay();
                        } else if($dt->day == 30) {
                            if($dt->addDay()->day == 31) {
                                $dt = $dt;
                            }
                        }
                    }
                    $this->expires_at = $dt->toDateTimeString();
                }
                if($this->period_unit == 'year') {
                    $this->expires_at = $dt->addYears($count)->toDateTimeString();
                }
                if($this->period_unit == null) {
                    $this->expires_at = null;
                }
                $this->save();
                event(new RenewSubscription($this->subscriber, $this, $periods));
                return true;
            }
        }
        return false;
    }

    public function cancel(string $reason = null) {
        if(!$this->isCancelled()){
            if($this->period_count == null || $this->cancelled_at == null) {
                $this->cancelled_at = Carbon::now()->toDateTimeString();
                $this->cancellation_reason = $reason;
                $this->save();

                if($reason != self::CANCEL_REASON_UPDATE_SUBSCRIPTION){
                    event(new CancelSubscription($this, $reason));
                }
                return true;
            }
        }
        return false;
    }

    public function isCancelled() {
        if($this->cancelled_at != null) {
            return true;
        }
        return false;
    }

    public function hasFeature(string $featureCode) {
        return $this->features()->where('code', $featureCode)->exists();
    }

    public function consumeFeature(string $featureCode, int $units = 1) {
        if($this->isCancelled())
            return false;

        $feature = $this->features()->limitType()->where('code', $featureCode)->first();
        if($feature) {
            $usage = $feature->pivot->usage;
            $limit = $feature->pivot->limit;
            if (($usage + $units) <= $limit) {
                $feature->pivot->usage = $usage + $units;
                $feature->pivot->save();
                event(new FeatureConsumed($this, $this->subscriber, $units));
                return true;
            }
        }
        return false;
    }

    public function unconsumeFeature(string $featureCode, int $units = 1) {
        if($this->isCancelled())
            return false;

        $feature = $this->features()->limitType()->where('code', $featureCode)->first();
        if($feature) {
            $usage = $feature->pivot->usage;
            if ($usage != 0 && ($usage - $units) >= 0) {
                $feature->pivot->usage = $usage - $units;
                $feature->pivot->save();
                event(new FeatureUnconsumed($this, $this->subscriber, $units));
                return true;
            }
        }
        return false;
    }

    public function getUsageOf(string $featureCode) {
        $feature = $this->features()->limitType()->where('code', $featureCode)->first();
        if ($feature) {
            $usage = $feature->pivot->usage;
            return $usage;
        }
        return null;
    }

    public function getRemainingOf(string $featureCode) {
        $feature = $this->features()->limitType()->where('code', $featureCode)->first();
        if ($feature) {
            $usage = $feature->pivot->usage;
            $limit = $feature->pivot->limit;
            $remaining = $limit - $usage;
            return $remaining;
        }
        return null;
    }
}
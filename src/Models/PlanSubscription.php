<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanSubscriptionInterface;
use Carbon\Carbon;

class PlanSubscription extends Model implements PlanSubscriptionInterface{

    protected $casts = [
        'trial_starts_at' => 'datetime',
        'starts_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('subscriptions.tables.plan_subscriptions'));
    }

    public function period(){
        return $this->belongsTo(config('subscriptions.models.period'), 'period_id');
    }

    public function subscriber() {
        return $this->morphTo();
    }

    public function plan_type() {
        return $this->belongsTo(config('subscriptions.models.type'), 'plan_type_id');
    }

    public function features() {
        return $this->belongsToMany(config('subscriptions.models.feature'), config('subscriptions.tables.plan_subscription_usage'), 'feature_id', 'subscription_id')->withPivot(['limit', 'usage']);
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

    public function isOnTrial() {
        $currentDay = Carbon::now();
        return $currentDay >= Carbon::parse($this->trial_starts_at) && $currentDay < Carbon::parse($this->starts_at);
    }

    public function isActive() {
        if(!$this->isCanceled()) {
            $currentDay = Carbon::now();
            if($currentDay >= $this->starts_at && $currentDay < $this->expires_at || $this->expires_at == null) {
                return true;
            }
        }
        return false;
    }

    public function isValid() {
        return $this->isOnTrial() || $this->isActive() || $this->isExpiredWithTolerance();
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
        return ! ($this->isExpiredWithTolerance() || $this->isActive() || $this->isOnTrial());
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
        // fecha inicio -> 31-01-2020 13:40:00
        // fecha expiracion -> 29-02-2020 13:40:00

        // $periods = 1

        // no se actualiza fecha de inicio ya que guarda dia de pago
        // actualizar fecha expiracion -> 31-03-2020 

        // $periods = 1

        // actualizar fecha expiracion -> 30-04-2020

        // $periods =  1

        // actualizar fecha expiracion -> 31-05-2020 

        if(!$this->isCanceled()) {
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
                    $this->expires_at = $dt->addMonths($count)->toDateTimeString();
                }
                if($this->period_unit == 'year') {
                    $this->expires_at = $dt->addYears($count)->toDateTimeString();
                }
                if($this->period_unit == null) {
                    $this->expires_at = null;
                }

                //actualizar dia de expiracion
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
        return $this->features()->where('code', $featureCode)->exists();
    }

    public function consumeFeature(string $featureCode, int $units = 1) {
        $feature = $this->features()->limitType()->where('code', $featureCode)->first();
        if($feature) {
            $usage = $feature->pivot->usage;
            $limit = $feature->pivot->limit;
            if (($usage + $units) <= $limit) {
                $feature->pivot->usage = $usage + $units;
                $feature->pivot->save();
                return true;
            }
        }
        return false;
    }

    public function unconsumeFeature(string $featureCode, int $units = 1) {
        $feature = $this->features()->limitType()->where('code', $featureCode)->first();
        if($feature) {
            $usage = $feature->pivot->usage;
            if ($usage != 0 && ($usage - $units) >= 0) {
                $feature->pivot->usage = $usage - $units;
                $feature->pivot->save();
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
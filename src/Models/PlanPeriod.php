<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanPeriodInterface;

class PlanPeriod extends Model implements PlanPeriodInterface{
    public const UNIT_DAY = 'day';
    public const UNIT_MONTH = 'month';
    public const UNIT_YEAR = 'year';
    
    public function plan() {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function subscriptions() {
        return $this->hasMany(PlanSubscription::class, 'period_id');
    }

    public function scopeVisible($query) {
        return $query->where('is_visible', 1);
    }

    public function scopeHidden($query) {
        return $query->where('is_visible', 0);
    }

    public function isRecurring() {
        if($this->is_recurring) {
            return true;
        }
        return false;
    }

    public function isLimitedNonRecurring() {
        if(!$this->is_recurring && $this->period_count != null) {
            return true;
        }
        return false;
    }

    public function isUnlimitedNonRecurring() {
        if(!$this->is_recurring && $this->period_count == null) {
            return true;
        }
        return false;
    }

    public function isVisible() {
        if($this->is_visible) {
            return true;
        }
        return false;
    }

    public function isHidden() {
        if(!$this->is_visible) {
            return true;
        }
        return false;
    }

    public function isDefault() {
        if($this->is_default) {
            return true;
        }
        return false;
    }

    public function isFree() {
        if($this->price == 0) {
            return true;
        }
        return false;
    }

    public function hasTrial() {
        if($this->trial_days != 0) {
            return true;
        }
        return false;
    }

    public function setAsVisible() {
        $this->is_visible = true;
        $this->save();
    }

    public function setAsHidden() {
        $this->is_visible = false;
        $this->save();
    }

    public function setAsDefault() {
        $this->is_default = true;
        $this->save();
    }
}
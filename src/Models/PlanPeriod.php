<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanPeriodInterface;
use Illuminate\Database\Eloquent\Builder;

class PlanPeriod extends Model implements PlanPeriodInterface{
    public const UNIT_DAY = 'day';
    public const UNIT_MONTH = 'month';
    public const UNIT_YEAR = 'year';
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('emeefe.subscriptions.tables.plan_periods'));
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot(){
        parent::boot();

        //Only visible periods
        static::addGlobalScope('withoutHidden', function(Builder $builder){
            $builder->where('is_visible', '<>', 0);
        });
    }

    public function plan() {
        return $this->belongsTo(config('emeefe.subscriptions.models.plan'), 'plan_id');
    }

    public function subscriptions() {
        return $this->hasMany(config('emeefe.subscriptions.models.subscription'), 'period_id');
    }

    public function scopeVisible($query) {
        return $query->where('is_visible', 1);
    }
    
    public function scopeWithHiddens($query){
        return $query->withoutGlobalScope('withoutHidden');
    }
    
    public function scopeHidden($query) {
        return $query->where('is_visible', 0);
    }

    public function scopeDefault($query) {
        return $query->where('is_default', 1);
    }

    public function isRecurring() {
        return $this->is_recurring;
    }

    public function isLimitedNonRecurring() {
        return !$this->is_recurring && $this->period_count != null;
    }

    public function isUnlimitedNonRecurring() {
        return !$this->is_recurring && $this->period_count == null;
    }

    public function isVisible() {
        return $this->is_visible;
    }

    public function isHidden() {
        return !$this->is_visible;
    }

    public function isDefault() {
        return !!$this->is_default;
    }

    public function isFree() {
        return $this->price == 0;
    }

    public function hasTrial() {
        return $this->trial_days != 0;
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
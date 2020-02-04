<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanPeriodInterface;

class PlanPeriod extends Model implements PlanPeriodInterface{
    public const UNIT_DAY = 'day';
    public const UNIT_MONTH = 'month';
    public const UNIT_YEAR = 'year';
}
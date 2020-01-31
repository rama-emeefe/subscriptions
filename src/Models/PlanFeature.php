<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanFeatureInterface;

class PlanFeature extends Model implements PlanFeatureInterface{
    public const TYPE_LIMIT = 'limit';
    public const TYPE_FEATURE = 'feature';
}
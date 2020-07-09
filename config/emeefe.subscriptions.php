<?php

return [
    'tables' => [
        'plans' => 'plans',
        'plan_types' => 'plan_types',
        'plan_features' => 'plan_features',
        'plan_type_feature' => 'plan_type_feature',
        'plan_feature_values' => 'plan_feature_values',
        'plan_periods' => 'plan_periods',
        'plan_subscriptions' => 'plan_subscriptions',
        'plan_subscription_usage' => 'plan_subscription_usage',
    ],

    'models' => [
        'plan' => \Emeefe\Subscriptions\Models\Plan::class,
        'feature' => \Emeefe\Subscriptions\Models\PlanFeature::class,
        'period' => \Emeefe\Subscriptions\Models\PlanPeriod::class,
        'subscription' => \Emeefe\Subscriptions\Models\PlanSubscription::class,
        'type' => \Emeefe\Subscriptions\Models\PlanType::class,
    ]
];
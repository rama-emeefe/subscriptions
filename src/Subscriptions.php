<?php

namespace Emeefe\Subscriptions;
use Emeefe\Subscriptions\Models\Plan;
use Emeefe\Subscriptions\PeriodBuilder;

class Subscriptions{

    /**
     * Laravel application.
     *
     * @var \Illuminate\Foundation\Application
     */
    public $app;

    /**
     * Create a new confide instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a period builder to build a plan period
     * 
     * @param string $displayName
     * @param string $code
     * @param Plan   $plan
     * @return PeriodBuilder
     */
    public function period(string $displayName, string $code, Plan $plan){
        return new PeriodBuilder($displayName, $code, $plan);
    }
}
<?php

namespace Emeefe\Subscriptions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Emeefe\Subscriptions\Subscriptions;
use Emeefe\Subscriptions\SubscriptionsFacade;
use Emeefe\Subscriptions\Observers\PlanObserver;
use Emeefe\Subscriptions\Models\Plan;

class SubscriptionsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerSubscriptionsPackage();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Plan::observe(PlanObserver::class);
    }

    private function registerSubscriptionsPackage(){
        $this->app->bind('subscriptions', function ($app) {
            return new Subscriptions($app);
        });

        AliasLoader::getInstance()->alias('Subscriptions', SubscriptionsFacade::class);
    }
}

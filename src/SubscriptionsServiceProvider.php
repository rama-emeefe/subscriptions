<?php

namespace Emeefe\Subscriptions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Emeefe\Subscriptions\Subscriptions;
use Emeefe\Subscriptions\SubscriptionsFacade;
use Emeefe\Subscriptions\Observers\PlanObserver;
use Emeefe\Subscriptions\Observers\PlanPeriodObserver;
use Emeefe\Subscriptions\Models\Plan;
use Emeefe\Subscriptions\Models\PlanPeriod;

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
        PlanPeriod::observe(PlanPeriodObserver::class);
        $this->publishMigrations();
        $this->publishConfig();
    }

    private function registerSubscriptionsPackage(){
        $this->app->bind('subscriptions', function ($app) {
            return new Subscriptions($app);
        });

        AliasLoader::getInstance()->alias('Subscriptions', SubscriptionsFacade::class);
    }

    private function publishMigrations(){
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

    private function publishConfig(){
        $this->publishes([
            __DIR__.'/../config/emeefe.subscriptions.php' => config_path('emeefe.subscriptions.php'),
        ]);
    }
}

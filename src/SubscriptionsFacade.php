<?php

namespace Emeefe\Subscriptions;
use Illuminate\Support\Facades\Facade;

class SubscriptionsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'subscriptions';
    }
}

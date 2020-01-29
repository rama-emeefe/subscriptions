<?php

namespace Emeefe\Subscriptions\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class SubscriptionsTest extends \Emeefe\Subscriptions\Tests\TestCase
{
    public function test_create_type(){
        
    }

    /**
     * Create test user
     */
    private function createUser(): User
    {
        return factory(User::class)->create();
    }
}

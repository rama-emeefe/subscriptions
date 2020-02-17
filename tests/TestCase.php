<?php

namespace Emeefe\Subscriptions\Tests;

use Emeefe\Subscriptions\Tests\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Emeefe\Subscriptions\SubscriptionsServiceProvider;
use Illuminate\Foundation\Testing\WithFaker;
use \Illuminate\Database\SQLiteConnection;  
use \Illuminate\Database\Schema\SQLiteBuilder;
use \Illuminate\Support\Fluent; 

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithFaker;

    public function __construct(){
        parent::__construct();
        $this->hotfixSqlite();
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    private function setUpDatabase(): void
    {
        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/resources/database/migrations'),
        ]);

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/../database/migrations'),
        ]);
    }

    /**
     * Documented in https://github.com/orchestral/testbench/tree/v3.8.5#custom-service-provider
     */
    protected function getPackageProviders($app)
    {
        return [
            SubscriptionsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function createUser(): User
    {
        return User::create([
            'name' => $this->faker->name,
        ]);
    }

    public function hotfixSqlite()
    {
        \Illuminate\Database\Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            return new class($connection, $database, $prefix, $config) extends SQLiteConnection {
                public function getSchemaBuilder()
                {
                    if ($this->schemaGrammar === null) {
                        $this->useDefaultSchemaGrammar();
                    }
                    return new class($this) extends SQLiteBuilder {
                        protected function createBlueprint($table, \Closure $callback = null)
                        {
                            return new class($table, $callback) extends Blueprint {
                                public function dropForeign($index)
                                {
                                    return new Fluent();
                                }
                            };
                        }
                    };
                }
            };
        });
    }

}
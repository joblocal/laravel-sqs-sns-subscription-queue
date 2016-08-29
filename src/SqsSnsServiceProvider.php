<?php

namespace Joblocal\LaravelSqsSnsSubscriptionQueue;

use Illuminate\Support\ServiceProvider;

use Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\Connectors\SqsSnsConnector;

class SqsSnsServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // nothing to register
    }

    /**
     * Bootstraps the 'queue' with a new connector 'sqs-sns'
     *
     * @return void
     */
    public function boot()
    {
        $this->app['queue']->extend('sqs-sns', function () {
            return new SqsSnsConnector;
        });
    }
}

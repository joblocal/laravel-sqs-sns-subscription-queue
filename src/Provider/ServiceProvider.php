<?php

namespace Joblocal\LaravelSqsSnsSubscriptionQueue\Provider;

use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Queue\QueueManager;

use Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\Connectors\SqsSnsConnector;

class ServiceProvider extends QueueServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /**
     * Register the queue manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('queue', function ($app) {
            $manager = new QueueManager($app);
            $this->registerConnectors($manager);
            return $manager;
        });

        $this->app->singleton('queue.connection', function ($app) {
            return $app['queue']->connection();
        });
    }

    /**
     * Register the connectors on the queue manager.
     *
     * @param \Illuminate\Queue\QueueManager $manager
     * @return void
     */
    public function registerConnectors($manager)
    {
        foreach (['Null', 'Sync', 'Database', 'Beanstalkd', 'Redis', 'Sqs', 'SqsSns'] as $connector) {
            $this->{"register{$connector}Connector"}($manager);
        }
    }

    /**
     * Register the SqsSns queue connector.
     *
     * @param \Illuminate\Queue\QueueManager $manager
     * @return void
     */
    protected function registerSqsSnsConnector($manager)
    {
        $manager->addConnector('sqs-sns', function () {
            return new SqsSnsConnector;
        });
    }
}

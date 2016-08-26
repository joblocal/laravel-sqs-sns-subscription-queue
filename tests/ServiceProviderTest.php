<?php

namespace Joblocal\LaravelSqsSnsSubscriptionQueue\Tests;

use Orchestra\Testbench\TestCase;

use Joblocal\LaravelSqsSnsSubscriptionQueue\Provider\ServiceProvider;
use Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\Connectors\SqsSnsConnector;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            'Joblocal\LaravelSqsSnsSubscriptionQueue\Provider\ServiceProvider',
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('queue.connections.sqs-sns', [
            'driver' => 'sqs-sns',
            'key'    => env('AWS_ACCESS_KEY', 'your-public-key'),
            'secret' => env('AWS_SECRET_ACCESS_KEY', 'your-secret-key'),
            'queue'  => env('QUEUE_URL', 'your-queue-url'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'routes' => [],
        ]);
        $app['config']->set('queue.default', 'sqs-sns');
    }

    public function testWillCallRegisterManager()
    {
        $provider = $this->getMockBuilder(ServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->setMethods(['registerManager'])
            ->getMock();

        $provider->expects($this->once())
            ->method('registerManager');

        $provider->register();
    }

    public function testWillRegisterSqsSnsQueueConnector()
    {
        $reflectionQueueManager = new \ReflectionClass($this->app['queue']);
        $reflectionQueueManagerGetConnectorMethod = $reflectionQueueManager->getMethod('getConnector');
        $reflectionQueueManagerGetConnectorMethod->setAccessible(true);

        $connector = $reflectionQueueManagerGetConnectorMethod->invoke(
            $this->app['queue'],
            'sqs-sns'
        );
        
        $this->assertInstanceOf(SqsSnsConnector::class, $connector);
    }
}

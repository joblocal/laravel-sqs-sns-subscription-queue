<?php

namespace Joblocal\LaravelSqsSnsSubscriptionQueue\Tests;

use PHPUnit\Framework\TestCase;
use Aws\Sqs\SqsClient;
use Illuminate\Container\Container;

use Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\SqsSnsQueue;
use Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\Jobs\SqsSnsJob;

class SqsSnsQueueTest extends TestCase
{
    private $sqsClient;

    protected function setUp()
    {
        $this->sqsClient = $this->getMockBuilder(SqsClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['receiveMessage'])
            ->getMock();
    }

    public function testCanInstantiateQueue()
    {
        $queue = new SqsSnsQueue($this->sqsClient, 'default_queue');

        $this->assertInstanceOf(SqsSnsQueue::class, $queue);
    }

    public function testWillSetRoutes()
    {
        $queue = new SqsSnsQueue($this->sqsClient, 'default_queue', '', [
            "Subject#action" => '\\Job',
        ]);

        $queueReflection = new \ReflectionClass($queue);
        $routeReflectionProperty = $queueReflection->getProperty('routes');
        $routeReflectionProperty->setAccessible(true);

        $this->assertEquals([
            "Subject#action" => '\\Job',
        ], $routeReflectionProperty->getValue($queue));
    }

    public function testWillCallReceiveMessage()
    {
        $this->sqsClient->expects($this->once())
            ->method('receiveMessage');

        $queue = new SqsSnsQueue($this->sqsClient, 'default_queue');
        $queue->setContainer($this->createMock(Container::class));

        $queue->pop();
    }

    public function testWillPopMessageOffQueue()
    {
        $message = [
            'Body' => 'The Body',
        ];

        $this->sqsClient->method('receiveMessage')->willReturn([
            'Messages' => [
                $message,
            ],
        ]);

        $queue = new SqsSnsQueue($this->sqsClient, 'default_queue');
        $queue->setContainer($this->createMock(\Illuminate\Container\Container::class));

        $job = $queue->pop();

        $this->assertInstanceOf(SqsSnsJob::class, $job);
        $this->assertEquals($message['Body'], $job->getRawBody());
    }
}

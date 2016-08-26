<?php

namespace Joblocal\LaravelSqsSnsSubscriptionQueue\Tests;

use PHPUnit\Framework\TestCase;
use Aws\Sqs\SqsClient;
use Illuminate\Container\Container;

use Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\Jobs\SqsSnsJob;

class SqsSnsJobTest extends TestCase
{
    private $sqsClient;
    private $container;
    private $sqsSnsJob;

    protected function setUp()
    {
        $this->sqsClient = $this->getMockBuilder(SqsClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container = $this->createMock(Container::class);

        $body = [
            'Subject' => 'Subject#action',
            'Message' => 'The Message',
        ];
        $payload = [
            'Body' => json_encode($body),
        ];

        $routes = [
            'Subject#action' => '\\stdClass',
        ];

        $this->sqsSnsJob = new SqsSnsJob(
            $this->container,
            $this->sqsClient,
            'default_queue',
            $payload,
            $routes
        );
    }

    public function testWillResolveSqsSubscriptionJob()
    {
        $jobPayload = $this->sqsSnsJob->payload();

        $this->assertEquals('Illuminate\\Queue\\CallQueuedHandler@call', $jobPayload['job']);
    }

    public function testWillResolveSqsSubscriptionCommandName()
    {
        $jobPayload = $this->sqsSnsJob->payload();

        $this->assertEquals('\\stdClass', $jobPayload['data']['commandName']);
    }

    public function testWillResolveSqsSubscriptionCommand()
    {
        $jobPayload = $this->sqsSnsJob->payload();
        $expectedCommand = serialize(new \stdClass);

        $this->assertEquals($expectedCommand, $jobPayload['data']['command']);
    }

    public function testWillLeaveDefaultSqsJobUntouched()
    {
        $body = [
            'Message' => 'The Message',
        ];

        $defaultSqsJob = new SqsSnsJob(
            $this->container,
            $this->sqsClient,
            'default_queue',
            [
                'Body' => json_encode($body),
            ],
            []
        );

        $jobPayload = $defaultSqsJob->payload();

        $this->assertEquals($body, $jobPayload);
    }
}

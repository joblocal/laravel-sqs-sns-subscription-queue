<?php

namespace Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\Jobs;

use Aws\Sqs\SqsClient;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Container\Container;
use Illuminate\Queue\CallQueuedHandler;

class SqsSnsJob extends SqsJob
{
    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Container\Container $container
     * @param \Aws\Sqs\SqsClient $sqs
     * @param string $queue
     * @param array $job
     * @param string $connectionName
     * @param array $routes
     */
    public function __construct(Container $container, SqsClient $sqs, array $job, $connectionName, $queue, array $routes)
    {
        parent::__construct($container, $sqs, $job, $connectionName, $queue);

        $this->job = $this->resolveSnsSubscription($this->job, $routes);
    }

    /**
     * Resolves SNS queue messages
     *
     * @param array $job
     * @param array $routes
     * @return array
     */
    private function resolveSnsSubscription(array $job, array $routes)
    {
        $body = json_decode($job['Body'], true);

        if (isset($body['TopicArn']) && array_key_exists($body['TopicArn'], $routes)) {
            // Find name of command in queue routes
            $commandName = $routes[$body['TopicArn']];

            // restructure job body
            $job['Body'] = json_encode([
                'job' => CallQueuedHandler::class . '@call',
                'data' => [
                    'commandName' => $commandName,
                    'command' => serialize(new $commandName(
                        $body['TopicArn'],
                        json_decode($body['Message'], true)
                    ))
                ],
            ]);
        }

        return $job;
    }

    /**
     * Get the underlying raw SQS job.
     *
     * @return array
     */
    public function getSqsSnsJob()
    {
        return $this->job;
    }
}

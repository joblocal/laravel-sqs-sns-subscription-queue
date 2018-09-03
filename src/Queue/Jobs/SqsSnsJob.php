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

        $commandName = null;

        // available parameters to route your jobs by
        $possibleRouteParams = ['TopicArn', 'Subject'];

        foreach ($possibleRouteParams as $param) {
            if (isset($body[$param]) && array_key_exists($body[$param], $routes)) {
                // Find name of command in queue routes using the param field
                $commandName = $routes[$body[$param]];
                break;
            }
        }

        if ($commandName !== null) {
            // restructure job body
            $job['Body'] = json_encode([
                'job' => CallQueuedHandler::class . '@call',
                'data' => [
                    'commandName' => $commandName,
                    'command' => serialize($this->container->make($commandName, [
                        'subject' => $body['Subject'],
                        'payload' => json_decode($body['Message'], true)
                    ]))
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

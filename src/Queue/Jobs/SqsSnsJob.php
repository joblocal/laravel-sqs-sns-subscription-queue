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
     * @return void
     */
    public function __construct(
        Container $container,
        SqsClient $sqs,
        array $job,
        $connectionName,
        $queue,
        array $routes
    ) {
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
    protected function resolveSnsSubscription(array $job, array $routes)
    {
        $body = json_decode($job['Body'], true);

        $commandName = null;

        // available parameters to route your jobs by
        $possibleRouteParams = ['Subject', 'TopicArn'];

        foreach ($possibleRouteParams as $param) {
            if (isset($body[$param]) && array_key_exists($body[$param], $routes)) {
                // Find name of command in queue routes using the param field
                $commandName = $routes[$body[$param]];
                break;
            }
        }

        if ($commandName !== null) {
            // If there is a command available, we will resolve the job instance for it from
            // the service container, passing in the subject and the payload of the
            // notification.

            $command = $this->makeCommand($commandName, $body);

            // The instance for the job will then be serialized and the body of
            // the job is reconstructed.

            $job['Body'] = json_encode([
                'uuid' => $body['MessageId'],
                'displayName' => $commandName,
                'job' => CallQueuedHandler::class . '@call',
                'data' => compact('commandName', 'command'),
            ]);
        }

        return $job;
    }

    /**
     * Make the serialized command.
     *
     * @param string $commandName
     * @param array  $body
     * @return string
     */
    protected function makeCommand($commandName, $body)
    {
        $payload = json_decode($body['Message'], true);

        $data = [
            'subject' => (isset($body['Subject'])) ? $body['Subject'] : '',
            'payload' => $payload
        ];

        $instance = $this->container->make($commandName, $data);

        return serialize($instance);
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

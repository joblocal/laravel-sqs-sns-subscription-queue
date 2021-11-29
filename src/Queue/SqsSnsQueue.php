<?php

namespace Joblocal\LaravelSqsSnsSubscriptionQueue\Queue;

use Aws\Sqs\SqsClient;
use Illuminate\Queue\SqsQueue;

use Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\Jobs\SqsSnsJob;

class SqsSnsQueue extends SqsQueue
{
    /**
     * The Job command routes by Subject
     *
     * @var array
     */
    protected $routes;

    /**
     * Create a new Amazon SQS SNS subscription queue instance
     *
     * @param \Aws\Sqs\SqsClient $sqs
     * @param string $default
     * @param string $prefix
     * @param array $routes
     */
    public function __construct(SqsClient $sqs, $default, $prefix = '', $routes = [])
    {
        parent::__construct($sqs, $default, $prefix);

        $this->routes = $routes;
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string $queue
     * @return \Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\Jobs\SqsSnsJob
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        $response = $this->sqs->receiveMessage([
            'QueueUrl' => $queue,
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        if (is_array($response['Messages']) && count($response['Messages']) > 0) {
            if ($this->routeExists($response['Messages'][0]) || $this->classExists($response['Messages'][0])) {
                return new SqsSnsJob(
                    $this->container,
                    $this->sqs,
                    $response['Messages'][0],
                    $this->connectionName,
                    $queue,
                    $this->routes
                );
            } else {
                // remove unwanted messages from topics with multiple messages
                $this->sqs->deleteMessage([
                    'QueueUrl' => $queue, // REQUIRED
                    'ReceiptHandle' => $response['Messages'][0]['ReceiptHandle'] // REQUIRED
                ]);
            }
        }
    }

    /**
     * Check if subject exist within the routes.
     * This skips creating a job for messages from
     * topics that publish multiple different messages.
     *
     * @param array $message
     * @return bool
     */
    protected function routeExists(array $message)
    {
        $body = json_decode($message['Body'], true);

        $possibleRouteParams = ['Subject', 'TopicArn'];

        foreach ($possibleRouteParams as $param) {
            if (isset($body[$param]) && array_key_exists($body[$param], $this->routes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the job class
     * you're trying to trigger exists.
     *
     * @param array $message
     * @return bool
     */
    protected function classExists(array $message)
    {
        $body = json_decode($message['Body'], true);

        return isset($body['data']['commandName']) && class_exists($body['data']['commandName']);
    }
}

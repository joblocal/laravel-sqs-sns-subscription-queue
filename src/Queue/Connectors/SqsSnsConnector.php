<?php

namespace Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\Connectors;

use Aws\Sqs\SqsClient;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Support\Arr;

use Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\SqsSnsQueue;

class SqsSnsConnector extends SqsConnector
{
    /**
     * Establish a queue connection.
     *
     * @param array $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret']);
        }

        return new SqsSnsQueue(
            new SqsClient($config),
            $config['queue'],
            Arr::get($config, 'prefix', ''),
            Arr::get($config, 'routes', [])
        );
    }
}

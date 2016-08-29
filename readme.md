# AWS SQS SNS Subscription Queue

This simple extension to the [Laravel](https://laravel.com) queue system
adds a new queue connector which allows you to work with [SQS](https://aws.amazon.com/sqs/)
[SNS](https://aws.amazon.com/sns/) subscription queues.

## Requirements

-   Laravel (tested with version 5.3)
-   or Lumen (tested with version 5.3)

## Usage

Add the LaravelSqsSnsSubscriptionQueue Provider
to your bootstrap, in Lumen you would add:

```php
$app->register(Joblocal\LaravelSqsSnsSubscriptionQueue\Provider\ServiceProvider::class);
```

You'll need to configure the queue connection in your config/queue.php

```php
'connections' => [
  'sqs-sns' => [
    'driver' => 'sqs-sns',
    'key'    => env('AWS_ACCESS_KEY', 'your-public-key'),
    'secret' => env('AWS_SECRET_ACCESS_KEY', 'your-secret-key'),
    'queue'  => env('QUEUE_URL', 'your-queue-url'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'routes' => [
        // specify routes to your queue jobs eg.
        'Subject' => 'App\\Jobs\\YourJob',
    ],
  ],
],
```

Once the sqs-sns queue connector is configured you can start
using it by setting your queue driver to 'sqs-sns'.

## Installation

The best way to install laravel-sqs-sns-subscription is by using [Composer](http://getcomposer.org/).

To install the most recent version, you can run the following command:

```sh
php composer.phar require joblocal/laravel-sqs-sns-subscription
```

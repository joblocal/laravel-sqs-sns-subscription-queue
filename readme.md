# AWS SQS SNS Subscription Queue

[![Downloads](https://img.shields.io/packagist/dt/joblocal/laravel-sqs-sns-subscription-queue.svg)](https://packagist.org/packages/joblocal/laravel-sqs-sns-subscription-queue)
[![Version](https://img.shields.io/packagist/v/joblocal/laravel-sqs-sns-subscription-queue.svg)](https://packagist.org/packages/joblocal/laravel-sqs-sns-subscription-queue)

A simple extension to the [Illuminate/Queue](https://github.com/illuminate/queue) queue system used in [Laravel](https://laravel.com) and [Lumen](https://lumen.laravel.com/).

Using this connector allows [SQS](https://aws.amazon.com/sqs/) messages originating from a [SNS](https://aws.amazon.com/sns/) subscription to be worked on with Illuminate\Queue\Jobs\SqsJob.

This is especially useful in a miroservice architecture where multiple services subscribe to a common topic with their queues.

Understand that this package will not handle publishing to SNS, please use the [AWS SDK](https://aws.amazon.com/sdk-for-php/) to publish an event to SNS.


## Requirements

-   Laravel (tested with version 5.8)
-   or Lumen (tested with version 5.8)


## Usage

Add the LaravelSqsSnsSubscriptionQueue ServiceProvider to your application.


### Laravel
[Registering Service Providers in Laravel](https://laravel.com/docs/5.6/providers#registering-providers)
```php
'providers' => [
    // ...
    Joblocal\LaravelSqsSnsSubscriptionQueue\SqsSnsServiceProvider::class,
],
```

### Lumen
[Registering Service Providers in Lumen](https://lumen.laravel.com/docs/5.6/providers#registering-providers)
```php
$app->register(Joblocal\LaravelSqsSnsSubscriptionQueue\SqsSnsServiceProvider::class);
```


### Configuration

You'll need to configure the queue connection in your config/queue.php

```php
'connections' => [
  'sqs-sns' => [
    'driver' => 'sqs-sns',
    'key'    => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'queue'  => env('SQS_QUEUE', 'your-queue-url'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'routes' => [
        // you can use the "Subject" field
        'Subject' => 'App\\Jobs\\YourJob',
        // or the "TopicArn" of your SQS message
        'TopicArn:123' => 'App\\Jobs\\YourJob',
        // to specify which job class should handle the job
    ],
  ],
],
```

Once the sqs-sns queue connector is configured you can start
using it by setting queue driver to 'sqs-sns' in your .env file.


### Job class example

```php
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Example Job class
 */
class Job implements ShouldQueue
{
  use InteractsWithQueue, Queueable, SerializesModels;

  /**
   * @param string  $subject   SNS Subject
   * @param array   $payload   JSON decoded 'Message'
   */
  public function __construct(string $subject, array $payload)
  {
  }
}
```

## Message transformation

When SNS publishes to SQS queues the received message signature is as follows:

```json
{
  "Type" : "Notification",
  "MessageId" : "63a3f6b6-d533-4a47-aef9-fcf5cf758c76",
  "TopicArn" : "arn:aws:sns:us-west-2:123456789012:MyTopic",
  "Subject" : "Testing publish to subscribed queues",
  "Message" : "Hello world!",
  "Timestamp" : "2017-03-29T05:12:16.901Z",
  "SignatureVersion" : "1",
  "Signature" : "...",
  "SigningCertURL" : "...",
  "UnsubscribeURL" : "..."
} 
```

Illuminate\Queue\Jobs\SqsJob requires the following signature:

```json
{
  "job": "Illuminate\\Queue\\CallQueuedHandler@call",
  "data": {
    "commandName": "App\\Jobs\\YourJob",
    "command": "...",
  }
}
```


## Installation

The best way to install laravel-sqs-sns-subscription is by using [Composer](http://getcomposer.org/).

To install the most recent version:
```sh
php composer.phar require joblocal/laravel-sqs-sns-subscription-queue
```

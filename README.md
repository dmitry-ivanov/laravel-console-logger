# Laravel Console Logger

[<img src="https://user-images.githubusercontent.com/1286821/43083932-4915853a-8ea0-11e8-8983-db9e0f04e772.png" alt="Become a Patron" width="160" />](https://patreon.com/dmitryivanov)

[![StyleCI](https://github.styleci.io/repos/61117768/shield?branch=6.x&style=flat)](https://github.styleci.io/repos/61117768?branch=6.x)
[![Build Status](https://img.shields.io/github/workflow/status/dmitry-ivanov/laravel-console-logger/tests/6.x)](https://github.com/dmitry-ivanov/laravel-console-logger/actions?query=workflow%3Atests+branch%3A6.x)
[![Coverage Status](https://img.shields.io/codecov/c/github/dmitry-ivanov/laravel-console-logger/6.x)](https://app.codecov.io/gh/dmitry-ivanov/laravel-console-logger/branch/6.x)

[![Latest Stable Version](https://poser.pugx.org/illuminated/console-logger/v/stable)](https://packagist.org/packages/illuminated/console-logger)
[![Latest Unstable Version](https://poser.pugx.org/illuminated/console-logger/v/unstable)](https://packagist.org/packages/illuminated/console-logger)
[![Total Downloads](https://poser.pugx.org/illuminated/console-logger/downloads)](https://packagist.org/packages/illuminated/console-logger)
[![License](https://poser.pugx.org/illuminated/console-logger/license)](https://packagist.org/packages/illuminated/console-logger)

Logging and notifications for Laravel console commands.

| Laravel | Console Logger                                                            |
| ------- | :-----------------------------------------------------------------------: |
| 6.x     | [6.x](https://github.com/dmitry-ivanov/laravel-console-logger/tree/6.x)   |
| 5.8.*   | [5.8.*](https://github.com/dmitry-ivanov/laravel-console-logger/tree/5.8) |
| 5.7.*   | [5.7.*](https://github.com/dmitry-ivanov/laravel-console-logger/tree/5.7) |
| 5.6.*   | [5.6.*](https://github.com/dmitry-ivanov/laravel-console-logger/tree/5.6) |
| 5.5.*   | [5.5.*](https://github.com/dmitry-ivanov/laravel-console-logger/tree/5.5) |
| 5.4.*   | [5.4.*](https://github.com/dmitry-ivanov/laravel-console-logger/tree/5.4) |
| 5.3.*   | [5.3.*](https://github.com/dmitry-ivanov/laravel-console-logger/tree/5.3) |
| 5.2.*   | [5.2.*](https://github.com/dmitry-ivanov/laravel-console-logger/tree/5.2) |
| 5.1.*   | [5.1.*](https://github.com/dmitry-ivanov/laravel-console-logger/tree/5.1) |

## Table of contents

- [Usage](#usage)
- [Methods](#methods)
- [Channels](#channels)
  - [File channel](#file-channel)
  - [Notification channels](#notification-channels)
    - [Email channel](#email-channel)
    - [Database channel](#database-channel)
- [Error handling](#error-handling)
  - [Custom exceptions](#custom-exceptions)
- [Guzzle 6+ integration](#guzzle-6-integration)
- [Powered by Monolog](#powered-by-monolog)
- [Troubleshooting](#troubleshooting)
  - [Trait included, but nothing happens?](#trait-included-but-nothing-happens)
  - [Several traits conflict?](#several-traits-conflict)
- [License](#license)

## Usage

1. Install the package via Composer:

    ```shell
    composer require "illuminated/console-logger:^6.0"
    ```

2. Use `Illuminated\Console\Loggable` trait:

    ```php
    use Illuminated\Console\Loggable;

    class ExampleCommand extends Command
    {
        use Loggable;

        public function handle()
        {
            $this->logInfo('Hello World!');
        }

        // ...
    }
    ```

3. Now your command is loggable!

    ```
    [2016-05-11 17:19:21]: [INFO]: Command `App\Console\Commands\ExampleCommand` initialized.
    [2016-05-11 17:19:21]: [INFO]: Host: `MyHost.local` (`10.0.1.1`).
    [2016-05-11 17:19:21]: [INFO]: Database host: `MyHost.local`, port: `3306`, ip: ``.
    [2016-05-11 17:19:21]: [INFO]: Database date: `2016-05-11 17:19:21`.
    [2016-05-11 17:19:21]: [INFO]: Hello World!
    [2016-05-11 17:19:21]: [INFO]: Execution time: 0.009 sec.
    [2016-05-11 17:19:21]: [INFO]: Memory peak usage: 8 MB.
    ```

## Methods

As soon as you're using `Loggable` trait, these [PSR-3](http://www.php-fig.org/psr/psr-3/) methods are available for you:

- `logDebug`
- `logInfo`
- `logNotice`
- `logWarning`
- `logError`
- `logCritical`
- `logAlert`
- `logEmergency`

Each of them expects the message and optional context for additional data.

## Channels

Channels are very simple. It's just the different ways to handle log messages.

### File channel

File channel is nothing more than writing log messages into the file. It is the main channel, and it is always enabled.

Log file can be found at `storage/logs/[command-name]/[date].log`. Namespaced commands exploded into subfolders.

| Command                   | Logs                                  |
| ------------------------- | ------------------------------------- |
| `php artisan send-report` | `storage/logs/send-report/[date].log` |
| `php artisan send:report` | `storage/logs/send/report/[date].log` |

As you can see, each command has a separate folder for its logs. Also, you get configured log files rotation out of the box.
By default, only latest thirty log files are stored. However, you can override this behavior as you wish:

```php
class ExampleCommand extends Command
{
    use Loggable;

    protected function getLogPath()
    {
        return storage_path('logs/custom-folder/date.log');
    }

    protected function getLogMaxFiles()
    {
        return 45;
    }

    // ...
}
```

## Notification channels

Want to be notified if some error occurred? Meet notifications!

Notification channels are optional and disabled by default. Each of them can be enabled and configured as needed.
By default, you'll get notifications of each level which is higher than NOTICE (see [PSR-3 log levels](https://www.php-fig.org/psr/psr-3/#5-psrlogloglevel)).
It means that you'll get notifications about each NOTICE, WARNING, ERROR, CRITICAL, ALERT and EMERGENCY, occurred while execution.

Of course, you can change this and other channel-specific aspects as you wish.

### Email channel

Email channel provides notifications via email.

The only thing you have to do is specify recipients. Set recipients and email notifications are ready to go!

```php
class ExampleCommand extends Command
{
    use Loggable;

    protected function getEmailNotificationsRecipients()
    {
        return [
            ['address' => 'john.doe@example.com', 'name' => 'John Doe'],
            ['address' => 'jane.smith@example.com', 'name' => 'Jane Smith'],
        ];
    }

    // ...
}
```

There is a bunch of methods specific to email channel. If you want to change email notifications level, or change the
subject, or change the from address, or something else - override proper method as you wish. And you're done!

Another cool feature of email notifications is deduplication. Sometimes the same error can be produced many times.
For example, you're using some external web service which is down. Or imagine that database server goes down.
You'll get a lot of similar emails in those cases. Email notifications deduplication is the solution for these scenarios.

Disabled by default, it can be enabled and also adjusted the time in seconds, for which deduplication works.

```php
class ExampleCommand extends Command
{
    use Loggable;

    protected function useEmailNotificationsDeduplication()
    {
        return true;
    }

    protected function getEmailNotificationsDeduplicationTime()
    {
        return 90;
    }

    // ...
}
```

### Database channel

Database channel provides saving of notifications into the database.

Disabled by default, it can be easily enabled by the proper method.

```php
class ExampleCommand extends Command
{
    use Loggable;

    protected function useDatabaseNotifications()
    {
        return true;
    }

    // ...
}
```

By default, you will get `iclogger_notifications` table, which would be created automatically, if it doesn't exist yet.
Of course, you can change the table name or even the logic of notification saving by overriding proper methods. It can be
useful if you want to add some custom fields to the notifications table. Here is the basic example of what it may look like:

```php
class ExampleCommand extends Command
{
    use Loggable;

    protected function useDatabaseNotifications()
    {
        return true;
    }

    protected function getDatabaseNotificationsTable()
    {
        return 'custom_notifications';
    }

    protected function getDatabaseNotificationsCallback()
    {
        return function (array $record) {
            CustomNotification::create([
                'level' => $record['level'],
                'level_name' => $record['level_name'],
                'message' => $record['message'],
                'context' => get_dump($record['context']),
                'some_custom_field' => 'Lorem!',
            ]);
        };
    }

    // ...
}
```

## Error handling

One of the coolest features is error handling. Each exception, each error and even PHP notices and warnings are handled.
It would be automatically logged as an error, and you will get proper notifications according to your command's setup.
You'll know immediately if something went wrong while execution. Very useful, especially for scheduled commands.

### Custom exceptions

You can throw an exception of any type from your code, and it would be properly handled by the exception handler.
However, if you want to pass an additional context, use `Illuminated\Console\Exceptions\RuntimeException` class:

```php
use Illuminated\Console\Exceptions\RuntimeException;

class ExampleCommand extends Command
{
    use Loggable;

    public function handle()
    {
        throw new RuntimeException('Oooups! Houston, we have a problem!', [
            'some' => 123,
            'extra' => true,
            'context' => null,
        ]);
    }

    // ...
}
```

```
[2016-05-11 17:19:21]: [ERROR]: Oooups! Houston, we have a problem!
array:5 [
    "code" => 0
    "message" => "Oooups! Houston, we have a problem!"
    "file" => "/Applications/MAMP/htdocs/icl-test/app/Console/Commands/ExampleCommand.php"
    "line" => 22
    "context" => array:3 [
        "some" => 123
        "extra" => true
        "context" => null
    ]
]
```

## Guzzle 6+ integration

If you're using [Guzzle](https://github.com/guzzle/guzzle), well, maybe you'll want to have logs of your HTTP interactions.

There is a helper function `iclogger_guzzle_middleware`, which makes it very easy:

```php
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

$handler = HandlerStack::create();
$middleware = iclogger_guzzle_middleware($log);
$handler->push($middleware);

$client = new Client([
    'handler' => $handler,
    'base_uri' => 'http://example.com',
]);
```

Now, your guzzle interactions are fully loggable. Each request, response and even errors would be logged for you.
You can also set type, as a second argument. Set it to `json` to get auto JSON decoding for request params and response body.

And even more advanced options are the third and the fourth optional arguments, which are callbacks, by which you can customize your logging logic if needed.
Both of them should return a bool. `shouldLogRequest` determines if request bodies should be logged or not, and `shouldLogResponse` determines the same for the response bodies.
You can set any of your custom logic here. For example, maybe you want to skip logging for just specific URLs, or maybe you want to check the content length of the response, etc.

```php
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

$middleware = iclogger_guzzle_middleware($log, 'json',
    function (RequestInterface $request) {
        if (ends_with($request->getUri(), '/foo')) {
            return false; // skips logging for /foo request bodies
        }

        return true;
    },
    function (RequestInterface $request, ResponseInterface $response) {
        $contentLength = $response->getHeaderLine('Content-Length');
        if ($contentLength > (100 * 1024)) {
            return false; // skips logging for responses greater than 100 KB
        }

        return true;
    }
);
```

## Powered by Monolog

This package is using the [Monolog logging library](https://github.com/Seldaek/monolog) with all of its power and benefits.

If needed, you may access the underlying Monolog instance in a two ways:

- Using `icLogger` command's method:

    ```php
    class ExampleCommand extends Command
    {
        use Loggable;

        public function handle()
        {
            $log = $this->icLogger();
        }

        // ...
    }
    ```

- Through Laravel service container:

    ```php
    $log = $app('log.iclogger');
    ```

## Troubleshooting

### Trait included, but nothing happens?

Note, that `Loggable` trait is overriding `initialize` method:

```php
trait Loggable
{
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeLogging();
    }

    // ...
}
```

If your command is overriding `initialize` method too, then you should call `initializeLogging` method by yourself:

```php
class ExampleCommand extends Command
{
    use Loggable;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeLogging();

        $this->foo = $this->argument('foo');
        $this->bar = $this->argument('bar');
        $this->baz = $this->argument('baz');
    }

    // ...
}
```

### Several traits conflict?

If you're using another `illuminated/console-%` package, then you can find yourself getting into the "traits conflict".

For example, if you're trying to build loggable command, which is [protected against overlapping](https://github.com/dmitry-ivanov/laravel-console-mutex):

```php
class ExampleCommand extends Command
{
    use Loggable;
    use WithoutOverlapping;

    // ...
}
```

You'll get the fatal error - the traits conflict, because of both of these traits are overriding `initialize` method:
> If two traits insert a method with the same name, a fatal error is produced, if the conflict is not explicitly resolved.

Override `initialize` method by yourself, and initialize traits in required order:

```php
class ExampleCommand extends Command
{
    use Loggable;
    use WithoutOverlapping;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeMutex();
        $this->initializeLogging();
    }

    // ...
}
```

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

[<img src="https://user-images.githubusercontent.com/1286821/43086829-ff7c006e-8ea6-11e8-8b03-ecf97ca95b2e.png" alt="Support on Patreon" width="125" />](https://patreon.com/dmitryivanov)

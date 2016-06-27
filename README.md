# Laravel console logger

[![StyleCI](https://styleci.io/repos/61117768/shield)](https://styleci.io/repos/61117768)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b6404099-b40b-4c59-8e71-5140a390f018/mini.png)](https://insight.sensiolabs.com/projects/b6404099-b40b-4c59-8e71-5140a390f018)

Provides logging and email notifications for Laravel console commands.

## Dependencies
- `PHP >=5.5.9`
- `Laravel >=5.2`

## Usage

1. Install package through `composer`:
    ```shell
    composer require illuminated/console-logger
    ```

2. Use `Illuminated\Console\Loggable` trait in your console command class:
    ```php
    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminated\Console\Loggable;

    class Foo extends Command
    {
        use Loggable;

        // ...
    }
    ```

3. Now your command is loggable!
    You have auto-rotation, email notifications for errors, auto saving to DB, set of useful info added to each iteration and even more cool features right out of the box!  

    These PSR-3 methods are available for you:
    - `logDebug`
    - `logInfo`
    - `logNotice`
    - `logWarning`
    - `logError`
    - `logCritical`
    - `logAlert`
    - `logEmergency`

    By default your log file would be something like this:
    ```
    [2016-05-11 17:19:21]: [INFO]: Command `Foo\Bar\Console\Commands\BazCommand` initialized.
    [2016-05-11 17:19:21]: [INFO]: Host: `MyHost.local` (`10.0.1.1`).
    [2016-05-11 17:19:21]: [INFO]: Database host: `MyHost.local`, port: `3306`, ip: ``.
    [2016-05-11 17:19:21]: [INFO]: Database date: `2016-05-11 17:19:21`.
    [2016-05-11 17:19:21]: [INFO]: Hello World!
    [2016-05-11 17:19:21]: [INFO]: Execution time: 0.009 sec.
    [2016-05-11 17:19:21]: [INFO]: Memory peak usage: 8 MB.
    ```

## Location

Each command has a separate folder for it's logs. Path is generated according to the command's name.
For example, command `foo` would have it's logs on `./storage/logs/foo/` folder, and command `foo:bar` on `./storage/logs/foo/bar/`.
Log file names are corresponding to dates, and only latest thirty files are stored.

## Advanced

- Message context
- Formatter reserved keywords
- Exceptions handler
- Monolog handlers (including DB Storage)

## Troubleshooting

#### Trait included, but nothing happens?

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
class Foo extends Command
{
    use Loggable;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeLogging();

        $this->bar = $this->argument('bar');
        $this->baz = $this->argument('baz');
    }

    // ...
}
```

#### Several traits conflict?

If you're using some other cool `illuminated/console-%` packages, well, then you can find yourself getting "traits conflict".
For example, if you're trying to build loggable command, which is [protected against overlapping](https://packagist.org/packages/illuminated/console-mutex):
```php
class Foo extends Command
{
    use Loggable;
    use WithoutOverlapping;

    // ...
}
```

You'll get fatal error, the "traits conflict", because both of these traits are overriding `initialize` method:
>If two traits insert a method with the same name, a fatal error is produced, if the conflict is not explicitly resolved.

But don't worry, solution is very simple. Just override `initialize` method by yourself, and initialize traits in required order:
```php
class Foo extends Command
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

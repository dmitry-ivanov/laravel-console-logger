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

3. Now your command is... To be continued... 

## Troubleshooting

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

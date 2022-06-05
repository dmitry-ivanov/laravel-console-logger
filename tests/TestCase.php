<?php

namespace Illuminated\Console\Tests;

use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Support\Facades\File;
use Illuminated\Console\Tests\App\Console\Kernel;
use Illuminated\Testing\TestingTools;
use Mockery;
use Symfony\Component\Finder\Finder;

Mockery::globalHelpers();

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use TestingTools;

    /**
     * Indicates if the console output should be mocked.
     */
    public $mockConsoleOutput = false;

    /**
     * The date used in tests.
     */
    protected string $date;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDate();
        $this->setUpDatabase();
        $this->setUpSendmail();
        $this->setUpStorage();
    }

    /**
     * Set up the date used in tests.
     */
    private function setUpDate(): void
    {
        $this->date = date('Y-m-d');
    }

    /**
     * Set up database.
     */
    protected function setUpDatabase(): void
    {
        config(['database.default' => 'testing']);
    }

    /**
     * Set up "sendmail".
     */
    protected function setUpSendmail(): void
    {
        config(['mail.sendmail' => '/usr/sbin/sendmail -bs']);
    }

    /**
     * Set up the storage.
     */
    private function setUpStorage(): void
    {
        $this->app->useStoragePath(__DIR__ . '/fixture/storage');
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function resolveApplicationConsoleKernel($app): void
    {
        $app->singleton(KernelContract::class, Kernel::class);

        app(KernelContract::class);
    }

    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void
    {
        $this->cleanLogsDirectory();

        parent::tearDown();
    }

    /**
     * Clean up the logs directory.
     */
    private function cleanLogsDirectory(): void
    {
        $objects = (new Finder)->in(storage_path('logs'))->depth(0);
        foreach ($objects as $object) {
            if (File::isDirectory($object)) {
                File::deleteDirectory($object);
            } else {
                File::delete($object);
            }
        }
    }
}

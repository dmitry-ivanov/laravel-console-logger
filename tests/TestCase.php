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
     *
     * @var bool
     */
    public $mockConsoleOutput = false;

    /**
     * The date used in tests.
     *
     * @var string
     */
    protected $date;

    /**
     * Setup the test environment.
     *
     * @return void
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
     *
     * @return void
     */
    private function setUpDate()
    {
        $this->date = date('Y-m-d');
    }

    /**
     * Set up database.
     *
     * @return void
     */
    protected function setUpDatabase()
    {
        config(['database.default' => 'testing']);
    }

    /**
     * Set up "sendmail".
     *
     * @return void
     */
    protected function setUpSendmail()
    {
        config(['mail.sendmail' => '/usr/sbin/sendmail -bs']);
    }

    /**
     * Set up the storage.
     *
     * @return void
     */
    private function setUpStorage()
    {
        $this->app->useStoragePath(__DIR__ . '/fixture/storage');
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(KernelContract::class, Kernel::class);

        app(KernelContract::class);
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->cleanLogsDirectory();

        parent::tearDown();
    }

    /**
     * Clean up the logs directory.
     *
     * @return void
     */
    private function cleanLogsDirectory()
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

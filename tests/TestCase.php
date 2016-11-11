<?php

use Illuminate\Contracts\Console\Kernel as KernelContract;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->setUpStorage();
    }

    private function setUpDatabase()
    {
        config(['database.default' => 'testing']);
    }

    private function setUpStorage()
    {
        $this->app->useStoragePath(__DIR__ . '/fixture/storage');
    }

    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(KernelContract::class, Kernel::class);
    }
}

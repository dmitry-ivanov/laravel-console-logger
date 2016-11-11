<?php

use Illuminate\Contracts\Console\Kernel as KernelContract;
use Symfony\Component\Finder\Finder;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $date;

    protected function setUp()
    {
        parent::setUp();

        $this->setUpDate();
        $this->setUpDatabase();
        $this->setUpStorage();
    }

    private function setUpDate()
    {
        $this->date = date('Y-m-d');
    }

    protected function setUpDatabase()
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

    protected function tearDown()
    {
        $this->cleanLogsDirectory();

        parent::tearDown();
    }

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

    public function assertLogFileExists($path)
    {
        $this->assertFileExists(storage_path("logs/{$path}"));
    }

    public function assertLogFileContains($path, $expected)
    {
        $expected = !is_array($expected) ? [$expected] : $expected;
        $content = File::get(storage_path("logs/{$path}"));

        foreach ($expected as $item) {
            $pattern = $this->normalizeExpectedFileContent($item);
            $this->assertRegExp($pattern, $content, "Failed asserting that file contains `{$item}`.");
        }
    }

    public function assertLogFileNotContains($path, $expected)
    {
        $expected = !is_array($expected) ? [$expected] : $expected;
        $content = File::get(storage_path("logs/{$path}"));

        foreach ($expected as $item) {
            $pattern = $this->normalizeExpectedFileContent($item);
            $this->assertNotRegExp($pattern, $content, "Failed asserting that file not contains `{$item}`.");
        }
    }

    private function normalizeExpectedFileContent($content)
    {
        $content = '/' . preg_quote($content) . '/';
        $content = str_replace('%datetime%', '\d{4}-\d{2}-\d{2} \d{2}\:\d{2}\:\d{2}', $content);

        return $content;
    }
}

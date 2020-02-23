<?php

namespace Illuminated\Console\Tests\Loggable\FileChannel;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminated\Console\Tests\App\Console\Commands\GenericCommand;
use Illuminated\Console\Tests\TestCase;

class FileChannelTest extends TestCase
{
    /** @test */
    public function it_creates_log_file_according_to_the_command_name_and_current_date()
    {
        $this->artisan('generic');

        $this->seeLogFile("generic/{$this->date}.log");
    }

    /** @test */
    public function it_creates_log_file_in_subfolder_if_command_is_namespaced()
    {
        $this->artisan('namespaced:command');

        $this->seeLogFile("namespaced/command/{$this->date}.log");
    }

    /** @test */
    public function it_provides_automatic_file_rotation_and_only_30_latest_files_are_stored()
    {
        $path = storage_path('logs/generic');
        $this->createLogFiles($path, 45);
        $this->assertFilesCount($path, 45);

        $this->runArtisan(new GenericCommand)->emulateFileHandlerClose();

        $this->assertFilesCount($path, 30);
    }

    /** @test */
    public function it_supports_separator_in_psr3_methods_which_is_transformed_to_11_blank_lines()
    {
        $this->artisan('separator-logging-command');

        $this->seeInLogFile("separator-logging-command/{$this->date}.log", [
            'Testing separator!',
            str_repeat("\n", 11),
        ]);
    }

    /**
     * Create log files in the given path.
     *
     * @param string $path
     * @param int $count
     * @return void
     */
    private function createLogFiles(string $path, int $count)
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path);
        }

        $date = Carbon::parse('2016-01-01');
        for ($i = 0; $i < $count; $i++) {
            File::put("{$path}/{$date->toDateString()}.log", 'foo');
            $date->addDay();
        }
    }
}

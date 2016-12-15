<?php

use Carbon\Carbon;

class FileChannelTest extends TestCase
{
    /** @test */
    public function it_creates_log_file_according_to_the_command_name_and_current_date()
    {
        $this->artisan('generic');

        $this->assertLogFileExists("generic/{$this->date}.log");
    }

    /** @test */
    public function it_creates_log_file_in_subfolder_if_command_is_namespaced()
    {
        $this->artisan('namespaced:command');

        $this->assertLogFileExists("namespaced/command/{$this->date}.log");
    }

    /** @test */
    public function it_provides_automatic_file_rotation_and_only_30_latest_files_are_stored()
    {
        $path = storage_path('logs/generic');
        $this->createBunchOfOldLogsInCount45($path);
        $this->assertCount(45, File::files($path));

        $this->runConsoleCommand(GenericCommand::class)->emulateFileHandlerClose();

        $this->assertCount(30, File::files($path));
    }

    /** @test */
    public function it_supports_separator_in_psr3_methods_which_is_transformed_to_11_blank_lines()
    {
        $this->artisan('separator-logging-command');

        $this->assertLogFileContains("separator-logging-command/{$this->date}.log", [
            'Testing separator!',
            str_repeat("\n", 11),
        ]);
    }

    private function createBunchOfOldLogsInCount45($path)
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path);
        }

        $date = Carbon::parse('2016-01-01');
        for ($i = 0; $i < 45; $i++) {
            File::put("{$path}/{$date->toDateString()}.log", 'foo');
            $date->addDay();
        }
    }
}

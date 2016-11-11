<?php

class FileHandlerTest extends TestCase
{
    private $date;

    protected function setUp()
    {
        parent::setUp();

        $this->date = date('Y-m-d');
    }

    /** @test */
    public function it_creates_log_file_according_to_the_command_name_and_current_date()
    {
        Artisan::call('generic');

        $this->assertLogFileExists("generic/{$this->date}.log");
    }

    /** @test */
    public function namespaced_command_names_are_translated_into_a_separate_subfolders()
    {
        Artisan::call('foo:barbaz');

        $this->assertLogFileExists("foo/barbaz/{$this->date}.log");
    }
}

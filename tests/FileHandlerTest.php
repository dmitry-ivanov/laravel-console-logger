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
}

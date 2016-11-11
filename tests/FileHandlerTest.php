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

    /** @test */
    public function it_writes_to_log_file_information_header_each_iteration()
    {
        $class = GenericCommand::class;
        $host = gethostname();
        $ip = gethostbyname($host);

        Artisan::call('generic');

        $this->assertLogFileContains("generic/{$this->date}.log", [
            "[%datetime%]: [INFO]: Command `{$class}` initialized.",
            "[%datetime%]: [INFO]: Host: `{$host}` (`{$ip}`).",
        ]);
    }

    /** @test */
    public function it_does_not_write_additional_mysql_information_headers_for_non_mysql_connections()
    {
        Artisan::call('generic');

        $this->assertLogFileNotContains("generic/{$this->date}.log", [
            'Database host:',
            'Database date:',
        ]);
    }
}

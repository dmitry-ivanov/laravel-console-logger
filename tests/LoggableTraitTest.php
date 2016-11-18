<?php

class LoggableTraitTest extends TestCase
{
    /** @test */
    public function it_supports_psr3_methods_for_logging()
    {
        Artisan::call('generic');

        $this->assertLogFileContains("generic/{$this->date}.log", [
            '[%datetime%]: [DEBUG]: Debug!',
            '[%datetime%]: [INFO]: Info!',
            '[%datetime%]: [NOTICE]: Notice!',
            '[%datetime%]: [WARNING]: Warning!',
            '[%datetime%]: [ERROR]: Error!',
            '[%datetime%]: [CRITICAL]: Critical!',
            '[%datetime%]: [ALERT]: Alert!',
            '[%datetime%]: [EMERGENCY]: Emergency!',
        ]);
    }

    /** @test */
    public function psr3_methods_are_supporting_context_and_it_is_logged_as_readable_dump()
    {
        Artisan::call('context-logging-command');

        $this->assertLogFileContains("context-logging-command/{$this->date}.log", [
            'Testing context!',
            'Some log with data.',
            get_dump([
                'foo' => 'bar',
                'baz' => 111,
                'faz' => true,
                3 => null,
            ]),
        ]);
    }
}
